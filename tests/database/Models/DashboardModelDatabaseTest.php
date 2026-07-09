<?php

use App\Models\DashboardModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class DashboardModelDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace;

    public function testGetMonthlyKpiReturnsRequiredKeys(): void
    {
        $model  = model(DashboardModel::class);
        $result = $model->getMonthlyKpi(2026, 6);

        $this->assertArrayHasKey('total_campaigns', $result);
        $this->assertArrayHasKey('active_campaigns', $result);
        $this->assertArrayHasKey('monthly_contract_count', $result);
        $this->assertArrayHasKey('monthly_contract_amount', $result);
        $this->assertArrayHasKey('monthly_revenue', $result);
    }

    public function testGetMonthlyKpiReturnsIntegers(): void
    {
        $model  = model(DashboardModel::class);
        $result = $model->getMonthlyKpi(2026, 6);

        foreach ($result as $key => $value) {
            $this->assertIsInt($value, "키 '{$key}' 값이 정수여야 합니다.");
        }
    }

    public function testGetMonthlyKpiActiveCampaignsNotExceedTotal(): void
    {
        $model  = model(DashboardModel::class);
        $result = $model->getMonthlyKpi(2026, 6);

        $this->assertLessThanOrEqual($result['total_campaigns'], $result['active_campaigns']);
    }

    public function testGetMonthlyTrendWithEmptyMonthsReturnsEmptyArrays(): void
    {
        $model  = model(DashboardModel::class);
        $result = $model->getMonthlyTrend([]);

        $this->assertArrayHasKey('contracts', $result);
        $this->assertArrayHasKey('revenues', $result);
        $this->assertEmpty($result['contracts']);
        $this->assertEmpty($result['revenues']);
    }

    public function testGetMonthlyTrendWithMonthsReturnsCorrectStructure(): void
    {
        $model  = model(DashboardModel::class);
        $months = [
            ['y' => 2026, 'm' => 1],
            ['y' => 2026, 'm' => 6],
        ];
        $result = $model->getMonthlyTrend($months);

        $this->assertArrayHasKey('contracts', $result);
        $this->assertArrayHasKey('revenues', $result);
        $this->assertIsArray($result['contracts']);
        $this->assertIsArray($result['revenues']);
    }
}
