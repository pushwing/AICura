<?php

use App\Models\CampaignReviewRequestModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class CampaignReviewRequestModelDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private int $hospitalId = 0;
    private int $campaignId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('hospitals')->insert([
            'name'       => '__test_hospital_crr__',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $db->table('campaigns')->insert([
            'hospital_id'   => $this->hospitalId,
            'hospital_type' => 1,
            'status'        => 'pending',
            'review_status' => 'pending',
            'is_deleted'    => 0,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        $this->campaignId = (int) $db->insertID();
    }

    protected function tearDown(): void
    {
        $db = db_connect();
        $db->table('campaign_review_requests')->where('campaign_id', $this->campaignId)->delete();
        $db->table('campaigns')->where('id', $this->campaignId)->delete();
        $db->table('hospitals')->where('id', $this->hospitalId)->delete();

        parent::tearDown();
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleContent(): array
    {
        return [
            'ad_title'      => '__test_title__',
            'ad_type'       => 1,
            'ad_start_date' => '2026-01-01',
            'ad_end_date'   => '2026-12-31',
            'cost_type'     => 1,
            'channel'       => 1,
        ];
    }

    public function testRecordCreatesPendingRequest(): void
    {
        $model = model(CampaignReviewRequestModel::class);
        $id    = $model->record($this->campaignId, $this->sampleContent(), 1, 'create');

        $this->assertGreaterThan(0, $id);

        /** @var array<string, mixed> $row */
        $row = $model->find($id);
        $this->assertSame('pending', $row['review_status']);
        $this->assertSame('create', $row['request_type']);
        $this->assertSame('__test_title__', $row['ad_title']);
    }

    public function testApproveReturnsContentFieldsAndMarksApproved(): void
    {
        $model = model(CampaignReviewRequestModel::class);
        $id    = $model->record($this->campaignId, $this->sampleContent(), 1, 'create');

        $content = $model->approve($id, 99, '승인 메모');

        // 콘텐츠 필드만 반환 — campaigns 복사용
        $this->assertArrayHasKey('ad_title', $content);
        $this->assertSame('__test_title__', $content['ad_title']);
        $this->assertArrayNotHasKey('review_status', $content);

        /** @var array<string, mixed> $row */
        $row = $model->find($id);
        $this->assertSame('approved', $row['review_status']);
        $this->assertSame(99, (int) $row['reviewed_by']);
        $this->assertSame('승인 메모', $row['review_memo']);
    }

    public function testRejectMarksRejected(): void
    {
        $model = model(CampaignReviewRequestModel::class);
        $id    = $model->record($this->campaignId, $this->sampleContent(), 1, 'update');

        $model->reject($id, 99, '반려 사유');

        /** @var array<string, mixed> $row */
        $row = $model->find($id);
        $this->assertSame('rejected', $row['review_status']);
        $this->assertSame('반려 사유', $row['review_memo']);
    }

    public function testApproveAlreadyProcessedThrows(): void
    {
        $model = model(CampaignReviewRequestModel::class);
        $id    = $model->record($this->campaignId, $this->sampleContent(), 1, 'create');
        $model->approve($id, 99, null);

        // approved → approved 는 허용되지 않는 전이
        $this->expectException(\RuntimeException::class);
        $model->approve($id, 99, null);
    }

    public function testApproveMissingRequestThrows(): void
    {
        $model = model(CampaignReviewRequestModel::class);

        $this->expectException(\RuntimeException::class);
        $model->approve(999999, 99, null);
    }

    public function testHasPendingReflectsState(): void
    {
        $model = model(CampaignReviewRequestModel::class);

        $this->assertFalse($model->hasPending($this->campaignId));

        $id = $model->record($this->campaignId, $this->sampleContent(), 1, 'create');
        $this->assertTrue($model->hasPending($this->campaignId));

        $model->approve($id, 99, null);
        $this->assertFalse($model->hasPending($this->campaignId));
    }

    public function testHasPendingWithMultipleRequests(): void
    {
        $model = model(CampaignReviewRequestModel::class);

        $first  = $model->record($this->campaignId, $this->sampleContent(), 1, 'create');
        $model->record($this->campaignId, $this->sampleContent(), 1, 'update');

        // 한 건만 처리해도 다른 pending 이 남아있으면 true
        $model->approve($first, 99, null);
        $this->assertTrue($model->hasPending($this->campaignId));
    }

    public function testGetLatestPendingReturnsNewest(): void
    {
        $model = model(CampaignReviewRequestModel::class);

        $model->record($this->campaignId, $this->sampleContent(), 1, 'create');
        $second = $model->record(
            $this->campaignId,
            array_merge($this->sampleContent(), ['ad_title' => '__newer__']),
            1,
            'update'
        );

        $latest = $model->getLatestPending($this->campaignId);
        $this->assertNotNull($latest);
        $this->assertSame($second, (int) $latest['id']);
        $this->assertSame('__newer__', $latest['ad_title']);
    }
}
