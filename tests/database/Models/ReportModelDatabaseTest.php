<?php

use App\Models\ReportModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class ReportModelDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private int $hospitalId    = 0;
    private int $campaignId    = 0;
    private int $contractId    = 9001;
    private int $contractOrder = 9002;
    private int $year          = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $this->year = (int) date('Y');

        $db->table('hospitals')->insert([
            'name'       => '__report_hospital__',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $db->table('campaigns')->insert([
            'ad_title'      => '__report_campaign__',
            'hospital_id'   => $this->hospitalId,
            'hospital_type' => 1,
            'ad_type'       => 1,
            'ad_start_date' => $this->year . '-01-01',
            'ad_end_date'   => $this->year . '-12-31',
            'cost_type'     => 1,
            'status'        => 'active',
            'channel'       => 1,
            'is_deleted'    => 0,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        $this->campaignId = (int) $db->insertID();

        // deposits: 충전(2) 1,000,000 / 소진(3) 300,000 / 환불(6) 100,000 / CPA환불복원(4) 50,000
        // CPA 환불 복원(status 4)은 충전이 아니라 소진 상계(−)로 집계돼야 한다.
        foreach ([[2, 1000000], [3, 300000], [6, 100000], [4, 50000]] as [$status, $price]) {
            $db->table('deposits')->insert([
                'status'            => $status,
                'is_minus'          => in_array($status, [2, 4], true) ? 0 : 1,
                'contract_id'       => $this->contractId,
                'contract_order_id' => $this->contractOrder,
                'price'             => $price,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // call_requests: 신청 2건, 그 중 1건 내원완료(7)
        foreach ([1, 7] as $status) {
            $db->table('call_requests')->insert([
                'hospital_id' => $this->hospitalId,
                'campaign_id' => $this->campaignId,
                'status'      => $status,
                'name'        => '__report_user__',
                'phone'       => '01000000000',
                'event_cost'  => 30000,
                'is_delete'   => 0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    protected function tearDown(): void
    {
        $db = db_connect();
        $db->table('deposits')->where('contract_id', $this->contractId)->delete();
        $db->table('call_requests')->where('campaign_id', $this->campaignId)->delete();
        $db->table('campaigns')->where('id', $this->campaignId)->delete();
        $db->table('hospitals')->where('id', $this->hospitalId)->delete();

        parent::tearDown();
    }

    public function testGetYearKpiAggregatesByStatus(): void
    {
        $kpi = model(ReportModel::class)->getYearKpi($this->year);

        // CPA 환불 복원(50,000)은 충전에 포함되지 않고 소진에서 차감된다.
        $this->assertSame(1000000, $kpi['charged']);          // 충전(2)만
        $this->assertSame(250000, $kpi['consumed']);          // 소진(3) 300,000 − CPA환불(4) 50,000
        $this->assertSame(100000, $kpi['refunded']);
        $this->assertSame(50000, $kpi['cpa_refunded']);
        $this->assertSame(650000, $kpi['balance']);           // 1,000,000 - 250,000 - 100,000
    }

    public function testGetYearKpiExcludesCpaRefundFromChargedAndConsumed(): void
    {
        // CPA 환불 복원(상계, status 4) 50,000 추가 — 충전·소진 양쪽에서 차감되어야 한다.
        db_connect()->table('deposits')->insert([
            'status'            => 4,
            'is_minus'          => 0,
            'contract_id'       => $this->contractId,
            'contract_order_id' => $this->contractOrder,
            'price'             => 50000,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        $kpi = model(ReportModel::class)->getYearKpi($this->year);

        $this->assertSame(1000000, $kpi['charged']);      // 1,050,000(raw 2+4) - 50,000(상계)
        $this->assertSame(250000, $kpi['consumed']);      // 300,000 - 50,000(상계)
        $this->assertSame(100000, $kpi['refunded']);
        $this->assertSame(50000, $kpi['cpa_refunded']);
        $this->assertSame(650000, $kpi['balance']);       // 1,000,000 - 250,000 - 100,000
    }

    public function testGetMonthToDateStatsExcludesCpaRefund(): void
    {
        // CPA 환불 복원(상계, status 4) 50,000 추가 — AI 매출보고서 누계에서도 차감되어야 한다.
        db_connect()->table('deposits')->insert([
            'status'            => 4,
            'is_minus'          => 0,
            'contract_id'       => $this->contractId,
            'contract_order_id' => $this->contractOrder,
            'price'             => 50000,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        $monthFrom = date('Y-m-01');
        $today     = date('Y-m-d');
        $mtd       = model(ReportModel::class)->getMonthToDateStats($monthFrom, $today);

        $this->assertSame(1000000, $mtd['charged']);  // 1,050,000(raw 2+4) - 50,000(상계)
        $this->assertSame(250000, $mtd['consumed']);   // 300,000 - 50,000(상계)
        $this->assertSame(100000, $mtd['refunded']);
        $this->assertSame(650000, $mtd['balance']);    // 1,000,000 - 250,000 - 100,000
    }

    public function testGetYearKpiExcludesCpaRefundFromChargedAndConsumed(): void
    {
        // CPA 환불 복원(상계, status 4) 50,000 추가 — 충전·소진 양쪽에서 차감되어야 한다.
        db_connect()->table('deposits')->insert([
            'status'            => 4,
            'is_minus'          => 0,
            'contract_id'       => $this->contractId,
            'contract_order_id' => $this->contractOrder,
            'price'             => 50000,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        $kpi = model(ReportModel::class)->getYearKpi($this->year);

        $this->assertSame(1000000, $kpi['charged']);      // 1,050,000(raw 2+4) - 50,000(상계)
        $this->assertSame(250000, $kpi['consumed']);      // 300,000 - 50,000(상계)
        $this->assertSame(100000, $kpi['refunded']);
        $this->assertSame(50000, $kpi['cpa_refunded']);
        $this->assertSame(650000, $kpi['balance']);       // 1,000,000 - 250,000 - 100,000
    }

    public function testGetMonthToDateStatsExcludesCpaRefund(): void
    {
        // CPA 환불 복원(상계, status 4) 50,000 추가 — AI 매출보고서 누계에서도 차감되어야 한다.
        db_connect()->table('deposits')->insert([
            'status'            => 4,
            'is_minus'          => 0,
            'contract_id'       => $this->contractId,
            'contract_order_id' => $this->contractOrder,
            'price'             => 50000,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        $monthFrom = date('Y-m-01');
        $today     = date('Y-m-d');
        $mtd       = model(ReportModel::class)->getMonthToDateStats($monthFrom, $today);

        $this->assertSame(1000000, $mtd['charged']);  // 1,050,000(raw 2+4) - 50,000(상계)
        $this->assertSame(250000, $mtd['consumed']);   // 300,000 - 50,000(상계)
        $this->assertSame(100000, $mtd['refunded']);
        $this->assertSame(650000, $mtd['balance']);    // 1,000,000 - 250,000 - 100,000
    }

    public function testGetMonthlyRevenueReturnsTwelveElements(): void
    {
        $monthly = model(ReportModel::class)->getMonthlyRevenue($this->year);

        $this->assertCount(12, $monthly['charged']);
        $this->assertCount(12, $monthly['consumed']);

        $monthIndex = (int) date('n') - 1;
        $this->assertSame(1000000, $monthly['charged'][$monthIndex]);
        $this->assertSame(250000, $monthly['consumed'][$monthIndex]); // 300,000 − CPA환불(4) 50,000
    }

    public function testGetCampaignStatsCountsRequestsAndVisits(): void
    {
        $stats = model(ReportModel::class)->getCampaignStats([
            'date_from' => '',
            'date_to'   => '',
            'ad_title'  => '',
        ]);

        $row = null;
        foreach ($stats as $s) {
            if ((int) $s['campaign_id'] === $this->campaignId) {
                $row = $s;
                break;
            }
        }

        $this->assertNotNull($row);
        $this->assertSame(2, (int) $row['request_count']);
        $this->assertSame(1, (int) $row['visited_count']);
        $this->assertSame(60000, (int) $row['total_cost']);
    }
}
