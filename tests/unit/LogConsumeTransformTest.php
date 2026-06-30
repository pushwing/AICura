<?php

use App\Commands\LogConsumeQueue;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * 로그 컨슈머 가공(transform) 단위 테스트 (이슈 #115)
 *
 * 커버리지:
 *   [T1] 표준 필드(level/event/message) 분리 + 나머지 context JSON 보존
 *   [T2] 누락 필드 기본값 (level=info, event/message/context=null)
 *   [T3] user_id·received_at 매핑
 *
 * @internal
 */
final class LogConsumeTransformTest extends CIUnitTestCase
{
    private LogConsumeQueue $command;

    protected function setUp(): void
    {
        parent::setUp();
        // BaseCommand 의존성을 우회하기 위해 리플렉션으로 인스턴스만 생성한다.
        $this->command = (new ReflectionClass(LogConsumeQueue::class))->newInstanceWithoutConstructor();
    }

    /** [T1] 표준 필드 분리 + context 보존 */
    public function testTransformSplitsStandardFieldsAndKeepsContext(): void
    {
        $row = $this->command->transform([
            'received_at' => '2026-06-30 12:00:00',
            'user_id'     => 7,
            'payload'     => [
                'level'    => 'warn',
                'event'    => 'screen_view',
                'message'  => '이벤트 상세 진입',
                'screen'   => 'event_detail',
                'event_id' => 42,
            ],
        ]);

        $this->assertSame(7, $row['user_id']);
        $this->assertSame('warn', $row['level']);
        $this->assertSame('screen_view', $row['event']);
        $this->assertSame('이벤트 상세 진입', $row['message']);
        $this->assertSame('2026-06-30 12:00:00', $row['client_received_at']);

        $context = json_decode((string) $row['context'], true);
        $this->assertSame('event_detail', $context['screen']);
        $this->assertSame(42, $context['event_id']);
        // 표준 필드는 context 에서 제외
        $this->assertArrayNotHasKey('level', $context);
        $this->assertArrayNotHasKey('message', $context);
    }

    /** [T2] 누락 필드 기본값 */
    public function testTransformAppliesDefaults(): void
    {
        $row = $this->command->transform([
            'received_at' => '2026-06-30 12:00:00',
            'user_id'     => null,
            'payload'     => [],
        ]);

        $this->assertNull($row['user_id']);
        $this->assertSame('info', $row['level']);
        $this->assertNull($row['event']);
        $this->assertNull($row['message']);
        $this->assertNull($row['context']); // 부가 컨텍스트 없음
    }

    /** [T3] payload 가 배열이 아니어도 안전하게 기본값 처리 */
    public function testTransformHandlesMissingPayload(): void
    {
        $row = $this->command->transform(['received_at' => '2026-06-30 12:00:00']);

        $this->assertNull($row['user_id']);
        $this->assertSame('info', $row['level']);
        $this->assertNull($row['context']);
    }
}
