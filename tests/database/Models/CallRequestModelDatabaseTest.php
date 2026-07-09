<?php

use App\Models\CallRequestModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class CallRequestModelDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace;
    private int $hospitalId    = 0;
    private int $campaignId    = 0;
    private int $callRequestId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('hospitals')->insert([
            'name'       => '__call_test_hospital__',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        // 계약 연결된 캠페인 (CPA 과금 경로 검증용)
        $db->table('campaigns')->insert([
            'ad_title'          => '__call_test_campaign__',
            'hospital_id'       => $this->hospitalId,
            'hospital_type'     => 1,
            'ad_type'           => 1,
            'ad_start_date'     => '2026-01-01',
            'ad_end_date'       => '2026-12-31',
            'cost_type'         => 1,
            'status'            => 'active',
            'channel'           => 1,
            'contract_id'       => 1,
            'contract_order_id' => 1,
            'is_deleted'        => 0,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        $this->campaignId = (int) $db->insertID();

        $db->table('call_requests')->insert([
            'hospital_id' => $this->hospitalId,
            'campaign_id' => $this->campaignId,
            'status'      => 1,
            'is_charged'  => 0,
            'name'        => '__call_test_user__',
            'phone'       => '01012345678',
            'event_cost'  => 30000,
            'is_delete'   => 0,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
        $this->callRequestId = (int) $db->insertID();
    }

    protected function tearDown(): void
    {
        $db = db_connect();
        $db->table('deposits')->where('contract_id', 1)->where('contract_order_id', 1)->delete();
        $db->table('call_memos')->where('call_request_id', $this->callRequestId)->delete();
        $db->table('call_requests')->where('id', $this->callRequestId)->delete();
        $db->table('campaigns')->where('id', $this->campaignId)->delete();
        $db->table('hospitals')->where('id', $this->hospitalId)->delete();

        parent::tearDown();
    }

    public function testGetListReturnsStructureAndFiltersByStatus(): void
    {
        $model = model(CallRequestModel::class);

        $result = $model->getList(['page' => 1, 'limit' => 20]);
        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertGreaterThanOrEqual(1, $result['total']);

        // 존재하지 않는 상태(7=내원완료)로 필터 → 현재 건은 제외
        $filtered = $model->getList(['status' => 7, 'page' => 1, 'limit' => 20]);
        $ids      = array_column($filtered['list'], 'id');
        $this->assertNotContains($this->callRequestId, $ids);
    }

    public function testChangeStatusSetsConfirmDateOnFirstConfirm(): void
    {
        $model = model(CallRequestModel::class);

        $model->changeStatus($this->callRequestId, 5, '2026-07-01 14:30'); // 예약 (예약 일시 필수)

        $row = $model->find($this->callRequestId);
        $this->assertSame(5, (int) $row['status']);
        $this->assertNotEmpty($row['confirm_date']);
        $this->assertSame('2026-07-01 14:30:00', $row['reserved_at']);
    }

    public function testChangeStatusToReservedRequiresReservedAt(): void
    {
        $this->expectException(RuntimeException::class);
        model(CallRequestModel::class)->changeStatus($this->callRequestId, 5); // 예약 일시 누락
    }

    public function testChangeStatusRejectsInvalidStatus(): void
    {
        $this->expectException(RuntimeException::class);
        model(CallRequestModel::class)->changeStatus($this->callRequestId, 99);
    }

    public function testChargeCpaIsIdempotent(): void
    {
        $model = model(CallRequestModel::class);
        $db    = db_connect();

        // 1차 과금 → 성공, deposit 1건, is_charged=1
        $first = $model->chargeCpa($this->callRequestId, 1);
        $this->assertTrue($first);

        $charged = $model->find($this->callRequestId);
        $this->assertSame(1, (int) $charged['is_charged']);

        $depositCount = $db->table('deposits')
            ->where('note', 'CPA 소진 (call_request:' . $this->callRequestId . ')')
            ->countAllResults();
        $this->assertSame(1, $depositCount);

        // 2차 과금 → 멱등(이미 과금됨) → false, deposit 추가 없음
        $second = $model->chargeCpa($this->callRequestId, 1);
        $this->assertFalse($second);

        $depositCountAfter = $db->table('deposits')
            ->where('note', 'CPA 소진 (call_request:' . $this->callRequestId . ')')
            ->countAllResults();
        $this->assertSame(1, $depositCountAfter);
    }

    public function testChargeCpaSkipsWhenEventCostZero(): void
    {
        $db = db_connect();
        $db->table('call_requests')->where('id', $this->callRequestId)->update(['event_cost' => 0]);

        $result = model(CallRequestModel::class)->chargeCpa($this->callRequestId, 1);
        $this->assertFalse($result);

        $row = model(CallRequestModel::class)->find($this->callRequestId);
        $this->assertSame(0, (int) $row['is_charged']);
    }
}
