<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $table = 'contract_orders';

    /**
     * 월간 KPI 집계
     *
     * @return array<string, int>
     */
    public function getMonthlyKpi(int $year, int $month): array
    {
        $totalCampaigns = (int) $this->db->table('campaigns')
            ->where('is_deleted', 0)
            ->countAllResults();

        $activeCampaigns = (int) $this->db->table('campaigns')
            ->where('is_deleted', 0)
            ->where('status', 'active')
            ->countAllResults();

        $contractRow = $this->db->table('contract_orders')
            ->select('COUNT(*) AS cnt, IFNULL(SUM(ad_price), 0) AS total_price', false)
            ->where('contract_status', 1)
            ->where('YEAR(created_at)', $year)
            ->where('MONTH(created_at)', $month)
            ->get()
            ->getRowArray();

        $revenueRow = $this->db->table('deposits')
            ->selectSum('price')
            ->where('status', 2)
            ->where('YEAR(created_at)', $year)
            ->where('MONTH(created_at)', $month)
            ->get()
            ->getRowArray();

        return [
            'total_campaigns'         => $totalCampaigns,
            'active_campaigns'        => $activeCampaigns,
            'monthly_contract_count'  => (int) ($contractRow['cnt'] ?? 0),
            'monthly_contract_amount' => (int) ($contractRow['total_price'] ?? 0),
            'monthly_revenue'         => (int) ($revenueRow['price'] ?? 0),
        ];
    }

    /**
     * 최근 N개월 월별 계약/매출 추이
     *
     * @param array<int, array{y: int, m: int}> $months
     * @return array{contracts: array<int, array<string, mixed>>, revenues: array<int, array<string, mixed>>}
     */
    public function getMonthlyTrend(array $months): array
    {
        if ($months === []) {
            return ['contracts' => [], 'revenues' => []];
        }

        $first     = $months[0];
        $last      = $months[count($months) - 1];
        $startDate = sprintf('%04d-%02d-01', $first['y'], $first['m']);
        $endDate   = date('Y-m-t', mktime(0, 0, 0, $last['m'], 1, $last['y']));

        $contractRows = $this->db->table('contract_orders')
            ->select('YEAR(created_at) AS y, MONTH(created_at) AS m, COUNT(*) AS cnt, IFNULL(SUM(ad_price), 0) AS total_price', false)
            ->where('contract_status', 1)
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate . ' 23:59:59')
            ->groupBy('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->getResultArray();

        $revenueRows = $this->db->table('deposits')
            ->select('YEAR(created_at) AS y, MONTH(created_at) AS m, IFNULL(SUM(price), 0) AS total_price', false)
            ->where('status', 2)
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate . ' 23:59:59')
            ->groupBy('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->getResultArray();

        return ['contracts' => $contractRows, 'revenues' => $revenueRows];
    }
}
