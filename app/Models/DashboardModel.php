<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $table = 'contract_orders';

    /**
     * YEAR() / MONTH() 대신 드라이버별 연도·월 표현식 반환
     * — MySQL: YEAR(col), SQLite3: CAST(strftime('%Y', col) AS INTEGER)
     */
    private function yearExpr(string $col): string
    {
        return str_starts_with($this->db->DBDriver, 'MySQLi')
            ? "YEAR({$col})"
            : "CAST(strftime('%Y', {$col}) AS INTEGER)";
    }

    private function monthExpr(string $col): string
    {
        return str_starts_with($this->db->DBDriver, 'MySQLi')
            ? "MONTH({$col})"
            : "CAST(strftime('%m', {$col}) AS INTEGER)";
    }

    /**
     * 월간 KPI 집계
     *
     * @return array<string, int>
     */
    public function getMonthlyKpi(int $year, int $month): array
    {
        $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $endDate   = date('Y-m-t 23:59:59', (int) mktime(0, 0, 0, $month, 1, $year));

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
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
            ->get()
            ->getRowArray();

        $revenueRow = $this->db->table('deposits')
            ->selectSum('price')
            ->where('status', 2)
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
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
     * 전체 잔액 (충전 − 소진) — deposits 원장 누계
     *
     * 충전: status IN (2, 12) / 소진: status IN (3, 5, 6, 7, 8, 9, 10, 11)
     * CPA 환불 복원(status 4)은 신청DB 환불요청 승인 시 소진을 상계하는 거래이므로
     * 충전이 아닌 소진 차감(−)으로 집계한다. (잔액 항등식은 불변)
     * (ContractOrderModel 잔액 계산과 동일한 status 집합)
     */
    public function getTotalBalance(): int
    {
        $row = $this->db->table('deposits')
            ->select('IFNULL(SUM(CASE WHEN status IN (2, 12) THEN price ELSE 0 END), 0) AS charged', false)
            ->select('IFNULL(SUM(CASE WHEN status IN (3, 5, 6, 7, 8, 9, 10, 11) THEN price WHEN status = 4 THEN -price ELSE 0 END), 0) AS used', false)
            ->get()
            ->getRowArray();

        return (int) ($row['charged'] ?? 0) - (int) ($row['used'] ?? 0);
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

        $y = $this->yearExpr('created_at');
        $m = $this->monthExpr('created_at');

        $contractRows = $this->db->table('contract_orders')
            ->select("{$y} AS y, {$m} AS m, COUNT(*) AS cnt, IFNULL(SUM(ad_price), 0) AS total_price", false)
            ->where('contract_status', 1)
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate . ' 23:59:59')
            ->groupBy("{$y}, {$m}")
            ->get()
            ->getResultArray();

        $revenueRows = $this->db->table('deposits')
            ->select("{$y} AS y, {$m} AS m, IFNULL(SUM(price), 0) AS total_price", false)
            ->where('status', 2)
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate . ' 23:59:59')
            ->groupBy("{$y}, {$m}")
            ->get()
            ->getResultArray();

        return ['contracts' => $contractRows, 'revenues' => $revenueRows];
    }
}
