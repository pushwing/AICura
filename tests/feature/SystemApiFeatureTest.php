<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 외부(소비자) 앱 공통·운영 API 피처 테스트 (이슈 #103, SQLite3 인메모리 DB)
 *
 * 커버리지:
 *   [S1] settings — 공개 키만, 미설정 기본값
 *   [S2] codes — 사용중 코드만, type 필터
 *   [S3] logs — 202 + 원시 파일 append
 *   [S4] logs — 빈 본문 422
 *   [S5] health — 200 ok
 *   [S6] 인증 없이 접근 가능 (공개)
 *
 * @internal
 */
final class SystemApiFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();
        // Throttler 를 갓 비운 캐시에 재바인딩 — 다른 테스트의 Rate Limit 버킷 누수 방지
        \CodeIgniter\Config\Services::injectMock('throttler', new \CodeIgniter\Throttle\Throttler(cache()));

        $db = db_connect();
        $db->table('settings')->insert(['setting_key' => 'site_name', 'setting_value' => 'AI Cura', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        // 내부 키 — 공개되면 안 됨
        $db->table('settings')->insert(['setting_key' => 'admin_email', 'setting_value' => 'secret@aicura.io', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);

        $db->table('codes')->insert(['code' => 'M', 'name' => '남', 'type' => 'sex', 'is_use' => 1, 'sort' => 1]);
        $db->table('codes')->insert(['code' => 'F', 'name' => '여', 'type' => 'sex', 'is_use' => 1, 'sort' => 2]);
        $db->table('codes')->insert(['code' => 'X', 'name' => '미사용', 'type' => 'sex', 'is_use' => 0, 'sort' => 3]);
        $db->table('codes')->insert(['code' => 'A', 'name' => '기타', 'type' => 'etc', 'is_use' => 1, 'sort' => 1]);
    }

    /** [S1][S6] settings — 공개 키만 */
    public function testSettings(): void
    {
        $res = $this->get('api/v1/settings');
        $res->assertStatus(200);
        $data = json_decode($res->getJSON(), true)['data'];

        $this->assertSame('AI Cura', $data['site_name']);
        $this->assertSame('', $data['terms_url']); // 미설정 기본값
        $this->assertArrayHasKey('app_min_version_ios', $data);
        $this->assertArrayNotHasKey('admin_email', $data); // 내부 키 미노출
    }

    /** [S2] codes — 사용중 + type 필터 */
    public function testCodes(): void
    {
        $all = json_decode($this->get('api/v1/codes')->getJSON(), true)['data'];
        $this->assertCount(3, $all); // is_use=0 제외

        $sex = json_decode($this->get('api/v1/codes?type=sex')->getJSON(), true)['data'];
        $this->assertCount(2, $sex);
        $this->assertSame('M', $sex[0]['code']);
    }

    /** [S3] logs — 202 + 파일 append (Redis 미연결 폴백 경로) */
    public function testLogsAccepted(): void
    {
        // 로컬에 Redis 가 떠 있어도 결과가 흔들리지 않도록 미연결 큐를 주입한다.
        \CodeIgniter\Config\Services::injectMock('redisQueue', new \Tests\Support\Libraries\FakeRedisQueue(false));

        $res = $this->withBodyFormat('json')->post('api/v1/logs', ['level' => 'info', 'event' => 'screen_view', 'message' => '진입']);
        $res->assertStatus(202);

        $file = rtrim(WRITEPATH, '/\\') . '/logs/raw/' . date('Y-m-d') . '.log';
        $this->assertFileExists($file);
        $this->assertStringContainsString('screen_view', (string) file_get_contents($file));
        @unlink($file);
    }

    /** [S4] logs — 빈 본문 422 */
    public function testLogsRejectsEmpty(): void
    {
        $this->withBodyFormat('json')->post('api/v1/logs', [])->assertStatus(422);
    }

    /** [S4a] logs — 본문 크기 초과 413 (이슈 #187 디스크·큐 남용 방지) */
    public function testLogsRejectsOversizedBody(): void
    {
        $res = $this->withBodyFormat('json')->post('api/v1/logs', [
            'level'   => 'info',
            'event'   => 'flood',
            'message' => str_repeat('A', 9000), // MAX_LOG_BYTES(8192) 초과
        ]);

        $res->assertStatus(413);
        $this->assertSame('PAYLOAD_TOO_LARGE', json_decode($res->getJSON(), true)['code']);
    }

    /** [S4b] logs — 허용되지 않은 level 422 */
    public function testLogsRejectsInvalidLevel(): void
    {
        $res = $this->withBodyFormat('json')->post('api/v1/logs', ['level' => 'critical', 'event' => 'x']);

        $res->assertStatus(422);
        $this->assertSame('VALIDATION_ERROR', json_decode($res->getJSON(), true)['code']);
    }

    /** [S4c] logs — 지나치게 긴 message 422 (크기 상한 이내지만 필드 길이 초과) */
    public function testLogsRejectsTooLongMessage(): void
    {
        $res = $this->withBodyFormat('json')->post('api/v1/logs', [
            'level'   => 'info',
            'message' => str_repeat('가', 2100), // max_length[2000] 초과, UTF-8 이라 8192 bytes 이내
        ]);

        $res->assertStatus(422);
        $this->assertSame('VALIDATION_ERROR', json_decode($res->getJSON(), true)['code']);
    }

    /** [S5] health */
    public function testHealth(): void
    {
        $res = $this->get('api/v1/health');
        $res->assertStatus(200);
        $data = json_decode($res->getJSON(), true);
        $this->assertSame('ok', $data['status']);
        $this->assertSame('up', $data['db']);
        $this->assertSame('up', $data['cache']);
    }
}
