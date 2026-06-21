<?php

use App\Models\CampaignModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class CampaignModelDatabaseTest extends CIUnitTestCase
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
            'name'       => '__test_hospital__',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $db->table('campaigns')->insert([
            'ad_title'      => '__test_campaign__',
            'hospital_id'   => $this->hospitalId,
            'hospital_type' => 1,
            'ad_type'       => 1,
            'ad_start_date' => '2026-01-01',
            'ad_end_date'   => '2026-12-31',
            'cost_type'     => 1,
            'status'        => 'pending',
            'channel'       => 1,
            'is_deleted'    => 0,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        $this->campaignId = (int) $db->insertID();
    }

    protected function tearDown(): void
    {
        $db = db_connect();
        $db->table('campaign_histories')->where('campaign_id', $this->campaignId)->delete();
        $db->table('campaigns')->where('id', $this->campaignId)->delete();
        $db->table('hospitals')->where('id', $this->hospitalId)->delete();

        parent::tearDown();
    }

    public function testGetCampaignListReturnsCorrectStructure(): void
    {
        $model  = model(CampaignModel::class);
        $result = $model->getCampaignList(['page' => 1, 'limit' => 20]);

        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertIsArray($result['list']);
        $this->assertIsInt($result['total']);
    }

    public function testGetCampaignListFindsInsertedRecord(): void
    {
        $model  = model(CampaignModel::class);
        $result = $model->getCampaignList(['keyword' => '__test_campaign__', 'page' => 1, 'limit' => 20]);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $ids = array_map('intval', array_column($result['list'], 'id'));
        $this->assertContains($this->campaignId, $ids);
    }

    public function testGetCampaignListFiltersByStatus(): void
    {
        $model  = model(CampaignModel::class);
        $result = $model->getCampaignList(['status' => 'pending', 'page' => 1, 'limit' => 100]);

        foreach ($result['list'] as $row) {
            $this->assertSame('pending', $row['status']);
        }

        $ids = array_map('intval', array_column($result['list'], 'id'));
        $this->assertContains($this->campaignId, $ids);
    }

    public function testUpdateStatusApprovePendingToActive(): void
    {
        $model     = model(CampaignModel::class);
        $newStatus = $model->updateStatus($this->campaignId, 'approve');

        $this->assertSame('active', $newStatus);

        /** @var array<string, mixed> $row */
        $row = $model->find($this->campaignId);
        $this->assertSame('active', $row['status']);
    }

    public function testUpdateStatusRejectPendingToRejected(): void
    {
        $model     = model(CampaignModel::class);
        $newStatus = $model->updateStatus($this->campaignId, 'reject');

        $this->assertSame('rejected', $newStatus);
    }

    public function testUpdateStatusInvalidTransitionThrows(): void
    {
        // pending → ended 는 허용되지 않는 전이
        $model = model(CampaignModel::class);

        $this->expectException(\RuntimeException::class);
        $model->updateStatus($this->campaignId, 'end');
    }

    public function testUpdateStatusUnknownActionThrows(): void
    {
        $model = model(CampaignModel::class);

        $this->expectException(\RuntimeException::class);
        $model->updateStatus($this->campaignId, 'unknown_action');
    }

    public function testGetCreativeListReturnsCorrectStructure(): void
    {
        $model  = model(CampaignModel::class);
        $result = $model->getCreativeList(['page' => 1, 'limit' => 20]);

        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertIsArray($result['list']);
        $this->assertIsInt($result['total']);
    }

    public function testGetCreativeListFindsInsertedRecordByKeyword(): void
    {
        $model  = model(CampaignModel::class);
        $result = $model->getCreativeList(['keyword' => '__test_campaign__', 'page' => 1, 'limit' => 20]);

        $ids = array_map('intval', array_column($result['list'], 'id'));
        $this->assertContains($this->campaignId, $ids);
    }

    public function testGetHistoryListReturnsCorrectStructure(): void
    {
        $model  = model(CampaignModel::class);
        $result = $model->getHistoryList($this->campaignId);

        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertIsArray($result['list']);
        $this->assertIsInt($result['total']);
    }
}
