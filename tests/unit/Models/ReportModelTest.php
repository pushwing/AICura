<?php

use App\Models\ReportModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * ReportModel 단위 테스트 (DB 불필요)
 *
 * @internal
 */
final class ReportModelTest extends CIUnitTestCase
{
    private ReportModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ReportModel();
    }

    public function testTableIsDeposits(): void
    {
        $this->assertSame('deposits', $this->model->getTable());
    }

    public function testGetYearKpiMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getYearKpi'));
    }

    public function testGetMonthlyRevenueMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getMonthlyRevenue'));
    }

    public function testGetCampaignConsumptionMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getCampaignConsumption'));
    }
}
