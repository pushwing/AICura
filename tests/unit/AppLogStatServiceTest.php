<?php

namespace Tests\Unit;

use App\Services\AppLogStatService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * AppLogStatService 단위 테스트 (이슈 #120)
 *
 * 로그 행 → (event, campaign_id) 버킷 집계의 정확성을 DB 없이 검증한다.
 *   - 발생 수 카운트
 *   - distinct user_id (uniq_users)
 *   - context JSON 에서 campaign_id 추출 / 없으면 0
 *   - event 누락 시 'unknown'
 *
 * @internal
 */
final class AppLogStatServiceTest extends CIUnitTestCase
{
    private function service(): AppLogStatService
    {
        return new AppLogStatService();
    }

    /**
     * @param list<array<string, mixed>> $buckets
     */
    private function find(array $buckets, string $event, int $campaignId): ?array
    {
        foreach ($buckets as $b) {
            if ($b['event'] === $event && $b['campaign_id'] === $campaignId) {
                return $b;
            }
        }

        return null;
    }

    public function testCountsAndUniqueUsersPerBucket(): void
    {
        $rows = [
            ['event' => 'event_detail_view', 'user_id' => 1, 'context' => '{"campaign_id":10}'],
            ['event' => 'event_detail_view', 'user_id' => 1, 'context' => '{"campaign_id":10}'],
            ['event' => 'event_detail_view', 'user_id' => 2, 'context' => '{"campaign_id":10}'],
        ];

        $bucket = $this->find($this->service()->summarize($rows), 'event_detail_view', 10);

        $this->assertNotNull($bucket);
        $this->assertSame(3, $bucket['count']);       // 발생 3건
        $this->assertSame(2, $bucket['uniq_users']);  // distinct user 2명
    }

    public function testSplitsByCampaignId(): void
    {
        $rows = [
            ['event' => 'apply_submit', 'user_id' => 1, 'context' => '{"campaign_id":10}'],
            ['event' => 'apply_submit', 'user_id' => 2, 'context' => '{"campaign_id":20}'],
        ];

        $buckets = $this->service()->summarize($rows);

        $this->assertSame(1, $this->find($buckets, 'apply_submit', 10)['count']);
        $this->assertSame(1, $this->find($buckets, 'apply_submit', 20)['count']);
    }

    public function testMissingCampaignIdFallsBackToZero(): void
    {
        $rows = [
            ['event' => 'app_open', 'user_id' => null, 'context' => null],
            ['event' => 'app_open', 'user_id' => null, 'context' => '{"platform":"android"}'],
        ];

        $bucket = $this->find($this->service()->summarize($rows), 'app_open', 0);

        $this->assertNotNull($bucket);
        $this->assertSame(2, $bucket['count']);
        $this->assertSame(0, $bucket['uniq_users']); // 익명(user_id 없음)
    }

    public function testMissingEventBecomesUnknown(): void
    {
        $rows = [
            ['event' => '', 'user_id' => 5, 'context' => null],
            ['user_id' => 6, 'context' => null],
        ];

        $bucket = $this->find($this->service()->summarize($rows), 'unknown', 0);

        $this->assertNotNull($bucket);
        $this->assertSame(2, $bucket['count']);
        $this->assertSame(2, $bucket['uniq_users']);
    }

    public function testEmptyInputProducesNoBuckets(): void
    {
        $this->assertSame([], $this->service()->summarize([]));
    }
}
