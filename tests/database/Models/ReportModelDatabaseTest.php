<?php

use App\Models\ReportModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * ReportModel DB 통합 테스트
 *
 * @internal
 */
final class ReportModelDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private int $hospitalId      = 0;
    private int $contractId      = 0;
    private int $contractOrderId = 0;
    private int $campaignId      = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('hospitals')->insert([
            'name'       => '__report_hospital__',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $db->table('contracts')->insert([
            'hospital_id'   => $this->hospitalId,
            'hospital_name' => '__report_hospital__',
            'title'         => '__report_contract__',
            'pay_type'      => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        $this->contractId = (int) $db->insertID();

        $db->table('contract_orders')->insert([
            'hospital_id'     => $this->hospitalId,
            'hospital_name'   => '__report_hospital__',
            'contract_type'   => 1,
            'ad_type'         => 1,
            'ad_type2'        => 1,
            'ad_price'        => 500000,
            'contract_status' => 1,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
        $this->contractOrderId = (int) $db->insertID();

        $db->table('campaigns')->insert([
            'ad_title'          => '__report_campaign__',
            'hospital_id'       => $this->hospitalId,
            'hospital_type'     => 1,
            'ad_type'           => 1,
            'ad_start_date'     => date('Y-m-d'),
            'ad_end_date'       => date('Y-m-d', strtotime('+30 days')),
            'cost_type'         => 1,
            'status'            => 'active',
            'channel'           => 1,
            'is_deleted'        => 0,
            'contract_order_id' => $this->contractOrderId,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        $this->campaignId = (int) $db->insertID();

        // 계약충전 (status=2, 올해)
        $db->table('deposits')->insert([
            'contract_id'       => $this->contractId,
            'contract_order_id' => $this->contractOrderId,
            'status'            => 2,
            'is_minus'          => 0,
            'price'             => 300000,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // DB 소진 (status=3, 올해)
        $db->table('deposits')->insert([
            'contract_id'       => $this->contractId,
            'contract_order_id' => $this->contractOrderId,
            'status'            => 3,
            'is_minus'          => 1,
            'price'             => 100000,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // 발행환불 (status=6, 올해)
        $db->table('deposits')->insert([
            'contract_id'       => $this->contractId,
            'contract_order_id' => $this->contractOrderId,
            'status'            => 6,
            'is_minus'          => 1,
            'price'             => 50000,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
    }

    protected function tearDown(): void
    {
        $db = db_connect();
        $db->table('deposits')->where('contract_id', $this->contractId)->delete();
        $db->table('campaigns')->where('id', $this->campaignId)->delete();
        $db->table('contract_orders')->where('id', $this->contractOrderId)->delete();
        $db->table('contracts')->where('id', $this->contractId)->delete();
        $db->table('hospitals')->where('id', $this->hospitalId)->delete();

        parent::tearDown();
    }

    // ── getYearKpi ─────────────────────────────────────

    public function testGetYearKpiReturnsRequiredKeys(): void
    {
        $result = model(ReportModel::class)->getYearKpi((int) date('Y'));

        $this->assertArrayHasKey('charged', $result);
        $this->assertArrayHasKey('consumed', $result);
        $this->assertArrayHasKey('refunded', $result);
        $this->assertArrayHasKey('balance', $result);
    }

    public function testGetYearKpiValuesAreIntegers(): void
    {
        $result = model(ReportModel::class)->getYearKpi((int) date('Y'));

        $this->assertIsInt($result['charged']);
        $this->assertIsInt($result['consumed']);
        $this->assertIsInt($result['refunded']);
        $this->assertIsInt($result['balance']);
    }

    public function testGetYearKpiBalanceEqualsChargedMinusConsumedAndRefunded(): void
    {
        $result = model(ReportModel::class)->getYearKpi((int) date('Y'));

        $expected = $result['charged'] - $result['consumed'] - $result['refunded'];
        $this->assertSame($expected, $result['balance']);
    }

    public function testGetYearKpiAggregatesCurrentYearDeposits(): void
    {
        $result = model(ReportModel::class)->getYearKpi((int) date('Y'));

        // setUp에 status=2 price=300000 삽입
        $this->assertGreaterThanOrEqual(300000, $result['charged']);
        // setUp에 status=3 price=100000 삽입
        $this->assertGreaterThanOrEqual(100000, $result['consumed']);
        // setUp에 status=6 price=50000 삽입
        $this->assertGreaterThanOrEqual(50000, $result['refunded']);
    }

    public function testGetYearKpiExcludesPreviousYearDeposits(): void
    {
        $db          = db_connect();
        $lastYear    = (int) date('Y') - 1;
        $lastYearNow = $lastYear . date('-m-d H:i:s');

        $db->table('deposits')->insert([
            'contract_id'       => $this->contractId,
            'contract_order_id' => $this->contractOrderId,
            'status'            => 2,
            'is_minus'          => 0,
            'price'             => 999999,
            'created_at'        => $lastYearNow,
            'updated_at'        => $lastYearNow,
        ]);

        $currentResult  = model(ReportModel::class)->getYearKpi((int) date('Y'));
        $previousResult = model(ReportModel::class)->getYearKpi($lastYear);

        // 올해 KPI에는 작년 999999 포함 안 됨 — setUp이 올해 status=2로 300000만 삽입하므로 999999 미만
        $this->assertLessThan(999999, $currentResult['charged']);
        // 작년 KPI에는 999999 포함
        $this->assertGreaterThanOrEqual(999999, $previousResult['charged']);

        $db->table('deposits')->where('price', 999999)->where('contract_id', $this->contractId)->delete();
    }

    // ── getMonthlyRevenue ──────────────────────────────

    public function testGetMonthlyRevenueReturns12Elements(): void
    {
        $result = model(ReportModel::class)->getMonthlyRevenue((int) date('Y'));

        $this->assertCount(12, $result);
    }

    public function testGetMonthlyRevenueAllElementsAreIntegers(): void
    {
        $result = model(ReportModel::class)->getMonthlyRevenue((int) date('Y'));

        foreach ($result as $val) {
            $this->assertIsInt($val);
        }
    }

    public function testGetMonthlyRevenueAggregatesCorrectMonth(): void
    {
        $currentMonth = (int) date('n'); // 1-12
        $result       = model(ReportModel::class)->getMonthlyRevenue((int) date('Y'));

        // setUp에 이번 달 status=2 price=300000 삽입 → 인덱스 0 = 1월, 인덱스 (n-1) = n월
        $this->assertGreaterThanOrEqual(300000, $result[$currentMonth - 1]);
    }

    public function testGetMonthlyRevenueReturnsZeroForFutureMonth(): void
    {
        $result = model(ReportModel::class)->getMonthlyRevenue((int) date('Y') + 1);

        foreach ($result as $val) {
            $this->assertSame(0, $val);
        }
    }

    // ── getCampaignConsumption ─────────────────────────

    public function testGetCampaignConsumptionReturnsArray(): void
    {
        $result = model(ReportModel::class)->getCampaignConsumption([
            'date_from' => '',
            'date_to'   => '',
            'ad_title'  => '',
        ]);

        $this->assertIsArray($result);
    }

    public function testGetCampaignConsumptionContainsInsertedRecord(): void
    {
        $result = model(ReportModel::class)->getCampaignConsumption([
            'date_from' => '',
            'date_to'   => '',
            'ad_title'  => '__report_campaign__',
        ]);

        $this->assertNotEmpty($result);
        $titles = array_column($result, 'ad_title');
        $this->assertContains('__report_campaign__', $titles);
    }

    public function testGetCampaignConsumptionFiltersAdTitle(): void
    {
        $result = model(ReportModel::class)->getCampaignConsumption([
            'date_from' => '',
            'date_to'   => '',
            'ad_title'  => '__존재하지않는캠페인__',
        ]);

        $this->assertEmpty($result);
    }

    public function testGetCampaignConsumptionFiltersByDateFrom(): void
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $result = model(ReportModel::class)->getCampaignConsumption([
            'date_from' => $tomorrow,
            'date_to'   => '',
            'ad_title'  => '__report_campaign__',
        ]);

        // 내일 이후로 필터하면 오늘 삽입된 기록 없음
        $this->assertEmpty($result);
    }

    public function testGetCampaignConsumptionExcludesDeletedCampaigns(): void
    {
        $db = db_connect();
        $db->table('campaigns')->where('id', $this->campaignId)->update(['is_deleted' => 1]);

        $result = model(ReportModel::class)->getCampaignConsumption([
            'date_from' => '',
            'date_to'   => '',
            'ad_title'  => '__report_campaign__',
        ]);

        $this->assertEmpty($result);

        $db->table('campaigns')->where('id', $this->campaignId)->update(['is_deleted' => 0]);
    }
}
