<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table = 'deposits';

    /** @return array<string, int> */
    public function getYearKpi(int $year): array
    {
        $charged  = $this->sumByStatuses([2], $year);
        $consumed = $this->sumByStatuses([3], $year);
        $refunded = $this->sumByStatuses([6, 7], $year);

        return [
            'charged'  => $charged,
            'consumed' => $consumed,
            'refunded' => $refunded,
            'balance'  => $charged - $consumed - $refunded,
        ];
    }

    /**
     * 월별 계약충전(status=2) 집계 — 12개 원소 배열 (인덱스 0=1월)
     *
     * @return array<int, int>
     */
    public function getMonthlyRevenue(int $year): array
    {
        $rows = $this->db->table('deposits')
            ->select('MONTH(created_at) AS month, IFNULL(SUM(price), 0) AS total', false)
            ->where('status', 2)
            ->where('YEAR(created_at)', $year)
            ->groupBy('MONTH(created_at)')
            ->orderBy('MONTH(created_at)', 'ASC')
            ->get()
            ->getResultArray();

        $byMonth = [];
        foreach ($rows as $row) {
            $byMonth[(int) $row['month']] = (int) $row['total'];
        }

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = $byMonth[$m] ?? 0;
        }

        return $result;
    }

    /**
     * 캠페인별 DB 소진(status=3) 집계 — AG Grid용 전체 목록
     *
     * @param array<string, string> $params
     * @return array<int, array<string, mixed>>
     */
    public function getCampaignConsumption(array $params): array
    {
        $builder = $this->db->table('deposits d')
            ->select('c.id AS campaign_id, c.ad_title, co.hospital_name, IFNULL(SUM(d.price), 0) AS consumed, c.ad_start_date, c.ad_end_date', false)
            ->join('campaigns c', 'c.contract_order_id = d.contract_order_id', 'inner')
            ->join('contract_orders co', 'co.id = c.contract_order_id', 'left')
            ->where('d.status', 3)
            ->where('c.is_deleted', 0)
            ->groupBy('c.id, c.ad_title, co.hospital_name, c.ad_start_date, c.ad_end_date')
            ->orderBy('consumed', 'DESC');

        if ($params['date_from'] !== '') {
            $builder->where('d.created_at >=', $params['date_from'] . ' 00:00:00');
        }
        if ($params['date_to'] !== '') {
            $builder->where('d.created_at <=', $params['date_to'] . ' 23:59:59');
        }
        if ($params['ad_title'] !== '') {
            $builder->like('c.ad_title', $params['ad_title']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * @param array<int, int> $statuses
     */
    private function sumByStatuses(array $statuses, int $year): int
    {
        $row = $this->db->table('deposits')
            ->select('IFNULL(SUM(price), 0) AS total', false)
            ->whereIn('status', $statuses)
            ->where('YEAR(created_at)', $year)
            ->get()
            ->getRowArray();

        return (int) ($row['total'] ?? 0);
    }
}
