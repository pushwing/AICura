<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 매출·통계 리포트 모델
 *
 *  - 매출 리포트: deposits 원장 기준 월별 충전/소진 집계
 *  - 캠페인 리포트: call_requests 기준 신청 건수·내원완료 통계
 *
 * deposits.status (ContractOrderModel·DashboardModel 잔액 집계와 동일한 status 집합):
 *   충전(+): 2 계약충전 / 4 기타충전(관리자 수동·CPA 환불 복원) / 12 이월충전
 *   소진(-): 3 DB소진 / 5 기타- / 8 기타소진 / 9 발행취소 / 10 계약취소 / 11 이월소진
 *   환불(-): 6 발행환불 / 7 계약환불  (소진과 별도 KPI로 노출)
 * call_requests.status: 7 내원완료
 */
class ReportModel extends Model
{
    protected $table      = 'deposits';
    protected $returnType = 'array';

    // 충전 — 계약충전 + 기타충전(CPA 환불 복원 포함) + 이월충전
    private const STATUS_CHARGED  = [2, 4, 12];
    // 소진 — DB소진·기타차감·취소·이월소진 (환불 6·7은 별도 집계로 제외)
    private const STATUS_CONSUMED = [3, 5, 8, 9, 10, 11];
    // 환불 — 발행환불 + 계약환불 (별도 KPI)
    private const STATUS_REFUNDED = [6, 7];

    /**
     * 드라이버별 연도 표현식 (MySQL: YEAR / SQLite3: strftime)
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
     * 연간 매출 KPI (충전·소진·환불·잔액)
     *
     * @return array{charged: int, consumed: int, refunded: int, balance: int}
     */
    public function getYearKpi(int $year): array
    {
        $charged  = $this->sumByStatuses(self::STATUS_CHARGED, $year);
        $consumed = $this->sumByStatuses(self::STATUS_CONSUMED, $year);
        $refunded = $this->sumByStatuses(self::STATUS_REFUNDED, $year);

        return [
            'charged'  => $charged,
            'consumed' => $consumed,
            'refunded' => $refunded,
            'balance'  => $charged - $consumed - $refunded,
        ];
    }

    /**
     * 월별 충전·소진 집계 — 각 12개 원소 배열 (인덱스 0 = 1월)
     *
     * @return array{charged: array<int, int>, consumed: array<int, int>}
     */
    public function getMonthlyRevenue(int $year): array
    {
        return [
            'charged'  => $this->monthlySumByStatuses(self::STATUS_CHARGED, $year),
            'consumed' => $this->monthlySumByStatuses(self::STATUS_CONSUMED, $year),
        ];
    }

    /**
     * 캠페인별 신청·내원완료 통계 (call_requests 기준)
     *
     * @param array<string, string> $params 필터 (date_from, date_to, ad_title)
     * @return array<int, array<string, mixed>>
     */
    public function getCampaignStats(array $params): array
    {
        $builder = $this->db->table('call_requests cr')
            ->select('c.id AS campaign_id, c.ad_title', false)
            ->select('h.name AS hospital_name', false)
            ->select('COUNT(cr.id) AS request_count', false)
            ->select('SUM(CASE WHEN cr.status = 7 THEN 1 ELSE 0 END) AS visited_count', false)
            ->select('IFNULL(SUM(cr.event_cost), 0) AS total_cost', false)
            ->join('campaigns c', 'c.id = cr.campaign_id', 'inner')
            ->join('hospitals h', 'h.id = cr.hospital_id', 'left')
            ->where('cr.is_delete', 0)
            ->where('c.is_deleted', 0)
            ->groupBy('c.id, c.ad_title, h.name')
            ->orderBy('request_count', 'DESC');

        if (($params['date_from'] ?? '') !== '') {
            $builder->where('cr.created_at >=', $params['date_from'] . ' 00:00:00');
        }
        if (($params['date_to'] ?? '') !== '') {
            $builder->where('cr.created_at <=', $params['date_to'] . ' 23:59:59');
        }
        if (($params['ad_title'] ?? '') !== '') {
            $builder->like('c.ad_title', $params['ad_title']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * 상태 집합의 연간 합계
     *
     * @param array<int, int> $statuses
     */
    private function sumByStatuses(array $statuses, int $year): int
    {
        $row = $this->db->table('deposits')
            ->select('IFNULL(SUM(price), 0) AS total', false)
            ->whereIn('status', $statuses)
            ->where($this->yearExpr('created_at'), $year)
            ->get()
            ->getRowArray();

        return (int) ($row['total'] ?? 0);
    }

    /**
     * 상태 집합의 월별 합계 (12개 배열)
     *
     * @param array<int, int> $statuses
     * @return array<int, int>
     */
    private function monthlySumByStatuses(array $statuses, int $year): array
    {
        $monthExpr = $this->monthExpr('created_at');

        $rows = $this->db->table('deposits')
            ->select($monthExpr . ' AS month, IFNULL(SUM(price), 0) AS total', false)
            ->whereIn('status', $statuses)
            ->where($this->yearExpr('created_at'), $year)
            ->groupBy($monthExpr)
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
}
