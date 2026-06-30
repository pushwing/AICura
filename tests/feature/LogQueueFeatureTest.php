<?php

use App\Models\AppLogModel;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Libraries\FakeRedisQueue;

/**
 * 로그 수집 큐 + 컨슈머 + Rate Limit 피처 테스트 (이슈 #115, SQLite3 인메모리 DB)
 *
 * 커버리지:
 *   [L1] Redis 가용 → POST /logs 큐 적재 → logs:consume → app_logs 적재 + 원시 파일 보존
 *   [L2] Redis 미연결 → POST /logs 원시 파일 폴백 (큐 비어 컨슈머 무작업)
 *   [L3] Rate Limit — IP 당 분당 60회 초과 시 429
 *
 * @internal
 */
final class LogQueueFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private string $rawFile;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();
        // Throttler 를 갓 비운 캐시에 재바인딩 — 테스트 간 버킷 상태 누수 방지.
        // 추가로 setTestTime() 으로 시계를 고정한다. CI4 Throttler 는 정수 초 단위
        // 타임스탬프로 토큰을 재충전(rate = capacity/seconds)하므로, 60회 요청이
        // 벽시계 1초 경계를 넘으면 토큰이 1개 보충돼 61회째가 통과(202)해 버린다.
        // 느린 환경(PHP 8.4 + 전체 스위트)에서만 재현되던 간헐 실패의 원인 →
        // 시계를 고정해 재충전을 차단, 실행 속도와 무관하게 결정적으로 동작시킨다.
        $throttler = new \CodeIgniter\Throttle\Throttler(cache());
        $throttler->setTestTime(\CodeIgniter\I18n\Time::now()->getTimestamp());
        Services::injectMock('throttler', $throttler);

        // 날짜별 원시/dead-letter 파일을 깨끗한 상태로 시작
        $this->rawFile = rtrim(WRITEPATH, '/\\') . '/logs/raw/' . date('Y-m-d') . '.log';
        if (is_file($this->rawFile)) {
            unlink($this->rawFile);
        }
    }

    /** [L1] 큐 적재 → 컨슈머 → DB 적재 + 원시 파일 보존 */
    public function testQueueToConsumerPersistsLog(): void
    {
        $queue = new FakeRedisQueue(true);
        Services::injectMock('redisQueue', $queue);

        $res = $this->withBodyFormat('json')->post('api/v1/logs', [
            'level'   => 'info',
            'event'   => 'screen_view',
            'message' => '이벤트 상세 진입',
            'screen'  => 'event_detail',
        ]);
        $res->assertStatus(202);

        // 큐에 적재되었고 아직 DB·원시 파일에는 없다
        $this->assertSame(1, $queue->length('log_queue'));
        $this->assertSame(0, model(AppLogModel::class)->countAllResults());

        // 컨슈머 실행 (같은 인메모리 큐 공유)
        command('logs:consume');

        $rows = model(AppLogModel::class)->findAll();
        $this->assertCount(1, $rows);
        $this->assertSame('screen_view', $rows[0]['event']);
        $this->assertSame('이벤트 상세 진입', $rows[0]['message']);
        $context = json_decode((string) $rows[0]['context'], true);
        $this->assertSame('event_detail', $context['screen']);

        // 원시 파일도 보존됨
        $this->assertFileExists($this->rawFile);
        $this->assertStringContainsString('이벤트 상세 진입', (string) file_get_contents($this->rawFile));
    }

    /** [L2] Redis 미연결 → 원시 파일 폴백 */
    public function testFallbackWritesRawFileWhenRedisUnavailable(): void
    {
        Services::injectMock('redisQueue', new FakeRedisQueue(false));

        $res = $this->withBodyFormat('json')->post('api/v1/logs', ['level' => 'info', 'event' => 'app_open']);
        $res->assertStatus(202);

        $this->assertFileExists($this->rawFile);
        $this->assertStringContainsString('app_open', (string) file_get_contents($this->rawFile));
        // 큐가 비어 있어 컨슈머는 무작업
        $this->assertSame(0, model(AppLogModel::class)->countAllResults());
    }

    /** [L3] Rate Limit — 분당 60회 초과 시 429 */
    public function testRateLimitBlocksAfterThreshold(): void
    {
        Services::injectMock('redisQueue', new FakeRedisQueue(false));

        // 처음 60회는 통과
        for ($i = 0; $i < 60; $i++) {
            $this->withBodyFormat('json')->post('api/v1/logs', ['level' => 'info', 'event' => 'ping']);
        }

        // 61회째는 429
        $res = $this->withBodyFormat('json')->post('api/v1/logs', ['level' => 'info', 'event' => 'ping']);
        $res->assertStatus(429);
        $this->assertSame('RATE_LIMITED', json_decode($res->getJSON(), true)['code']);
    }
}
