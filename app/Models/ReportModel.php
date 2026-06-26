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
 *   충전(+): 2 계약충전 / 12 이월충전
 *   소진(-): 3 DB소진 / 5 기타- / 8 기타소진 / 9 발행취소 / 10 계약취소 / 11 이월소진
 *   환불(-): 6 발행환불 / 7 계약환불  (소진과 별도 KPI로 노출)
 *   CPA 환불 복원: 4 — 신청DB 환불요청 승인 시 소진을 상계(−)하는 거래. 충전이 아니라
 *                  소진 차감으로 집계해 전체 충전금에 포함하지 않는다.
 * call_requests.status: 7 내원완료
 */
class ReportModel extends Model
{
    protected $table      = 'deposits';
    protected $returnType = 'array';

    // 충전 — 계약충전 + 이월충전 (CPA 환불 복원 status 4 제외)
    private const STATUS_CHARGED  = [2, 12];
    // 소진 — DB소진·기타차감·취소·이월소진 (환불 6·7은 별도 집계로 제외)
    private const STATUS_CONSUMED = [3, 5, 8, 9, 10, 11];
    // 환불 — 발행환불 + 계약환불 (별도 KPI)
    private const STATUS_REFUNDED = [6, 7];
    // CPA 환불 복원 — 신청DB 환불요청 승인 시 기록되는 소진 상계 거래 (status 4 전용 집계)
    private const STATUS_CPA_REFUND = [4];

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
     * 연간 매출 KPI (충전·소진·환불·CPA환불·잔액)
     *
     * CPA 환불 복원(상계, status 4)은 소진을 되돌리는 거래이므로 소진(consumed)에서만 차감한다.
     * 충전(charged)에는 애초에 status 4가 포함되지 않으므로(STATUS_CHARGED=[2,12]) 차감하지 않는다.
     * 그 결과 status 4만큼 잔액이 복원된다(잔액 = 충전 − 순소진 − 환불).
     * 이는 DashboardModel·ContractOrderModel 잔액 계산과 동일한 상계 정책이다. cpa_refunded는 상계 규모 표시용 지표.
     *
     * @return array{charged: int, consumed: int, refunded: int, cpa_refunded: int, balance: int}
     */
    public function getYearKpi(int $year): array
    {
        $charged     = $this->sumByStatuses(self::STATUS_CHARGED, $year);
        $consumed    = $this->sumByStatuses(self::STATUS_CONSUMED, $year);
        $refunded    = $this->sumByStatuses(self::STATUS_REFUNDED, $year);
        $cpaRefunded = $this->sumByStatuses(self::STATUS_CPA_REFUND, $year);

        $netConsumed = $consumed - $cpaRefunded;

        return [
            'charged'      => $charged,
            'consumed'     => $netConsumed,
            'refunded'     => $refunded,
            'cpa_refunded' => $cpaRefunded,
            'balance'      => $charged - $netConsumed - $refunded,
        ];
    }

    /**
     * 월별 충전·소진 집계 — 각 12개 원소 배열 (인덱스 0 = 1월)
     *
     * @return array{charged: array<int, int>, consumed: array<int, int>}
     */
    public function getMonthlyRevenue(int $year): array
    {
        $charged  = $this->monthlySumByStatuses(self::STATUS_CHARGED, $year);
        $consumed = $this->monthlySumByStatuses(self::STATUS_CONSUMED, $year);
        $cpa      = $this->monthlySumByStatuses(self::STATUS_CPA_REFUND, $year);

        // CPA 환불 복원(상계, status 4)은 소진에서만 차감해 순액 그래프로 노출 (충전에는 미포함)
        for ($i = 0; $i < 12; $i++) {
            $consumed[$i] -= $cpa[$i];
        }

        return ['charged' => $charged, 'consumed' => $consumed];
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
     * 일자별 충전·소진·환불 합계 (AI 매출보고서용 — 전일 1일치)
     *
     * @param list<int>|null $hospitalIds 집계 대상 병원 한정 (null이면 전체)
     * @return array{charged: int, consumed: int, refunded: int}
     */
    public function getDailyStats(string $date, ?array $hospitalIds = null): array
    {
        $charged  = $this->sumByStatusesBetween(self::STATUS_CHARGED, $date, $date, $hospitalIds);
        $consumed = $this->sumByStatusesBetween(self::STATUS_CONSUMED, $date, $date, $hospitalIds);
        $cpa      = $this->sumByStatusesBetween(self::STATUS_CPA_REFUND, $date, $date, $hospitalIds);

        // CPA 환불 복원(상계, status 4)은 소진에서만 차감해 순액으로 노출 (리포트 KPI와 동일, 충전 미포함)
        return [
            'charged'  => $charged,
            'consumed' => $consumed - $cpa,
            'refunded' => $this->sumByStatusesBetween(self::STATUS_REFUNDED, $date, $date, $hospitalIds),
        ];
    }

    /**
     * 당월 누계 충전·소진·환불·잔액 (AI 매출보고서용)
     *
     * @param list<int>|null $hospitalIds 집계 대상 병원 한정 (null이면 전체)
     * @return array{charged: int, consumed: int, refunded: int, balance: int}
     */
    public function getMonthToDateStats(string $fromDate, string $toDate, ?array $hospitalIds = null): array
    {
        $charged  = $this->sumByStatusesBetween(self::STATUS_CHARGED, $fromDate, $toDate, $hospitalIds);
        $consumed = $this->sumByStatusesBetween(self::STATUS_CONSUMED, $fromDate, $toDate, $hospitalIds);
        $refunded = $this->sumByStatusesBetween(self::STATUS_REFUNDED, $fromDate, $toDate, $hospitalIds);
        $cpa      = $this->sumByStatusesBetween(self::STATUS_CPA_REFUND, $fromDate, $toDate, $hospitalIds);

        $netConsumed = $consumed - $cpa;

        // CPA 환불 복원(상계, status 4)은 소진에서만 차감 — status 4만큼 잔액이 복원된다 (리포트 KPI와 동일)
        return [
            'charged'  => $charged,
            'consumed' => $netConsumed,
            'refunded' => $refunded,
            'balance'  => $charged - $netConsumed - $refunded,
        ];
    }

    /**
     * 광고주(병원) 단위 충전금/소진/잔액 요약 — 잔액이 충전금의 임계 비율 이하인 병원만 반환
     *
     * AI 소진보고서용. charged > 0 이고 (balance / charged) <= $thresholdRatio 인 병원을
     * 잔액 비율 오름차순으로 정렬해 돌려준다.
     *
     * 충전·소진은 CPA 환불 복원(상계, status 4)을 양쪽에서 차감한 순액 기준이다(리포트 KPI와 동일).
     * 원자료 충전: status IN (2, 4, 12) / 소진: status IN (3, 5, 6, 7, 8, 9, 10, 11)
     *
     * @param list<int>|null $hospitalIds 집계 대상 병원 한정 (null이면 전체)
     * @return array<int, array{hospital_id: int, hospital_name: string, charged: int, used: int, balance: int, ratio: float}>
     */
    public function getLowBalanceHospitals(float $thresholdRatio = 0.05, ?array $hospitalIds = null): array
    {
        if ($hospitalIds === []) {
            return [];
        }

        $builder = $this->db->table('deposits d')
            ->select('h.id AS hospital_id, h.name AS hospital_name', false)
            ->select('SUM(CASE WHEN d.status IN (2, 4, 12) THEN d.price ELSE 0 END) AS charged', false)
            ->select('SUM(CASE WHEN d.status IN (3, 5, 6, 7, 8, 9, 10, 11) THEN d.price ELSE 0 END) AS used', false)
            ->select('SUM(CASE WHEN d.status = 4 THEN d.price ELSE 0 END) AS cpa_refunded', false)
            ->join('contracts c', 'c.id = d.contract_id', 'inner')
            ->join('hospitals h', 'h.id = c.hospital_id', 'inner')
            ->groupBy('h.id, h.name')
            ->having('charged >', 0);

        if ($hospitalIds !== null) {
            $builder->whereIn('h.id', $hospitalIds);
        }

        $rows = $builder->get()->getResultArray();

        $result = [];
        foreach ($rows as $row) {
            // CPA 환불 복원(상계, status 4)을 충전·소진 양쪽에서 차감한 순액으로 비율 산정
            $cpa     = (int) $row['cpa_refunded'];
            $charged = (int) $row['charged'] - $cpa;
            $used    = (int) $row['used'] - $cpa;

            // 순 충전금이 0 이하면(상계만 존재) 비율 산정 불가 — 제외
            if ($charged <= 0) {
                continue;
            }

            $balance = $charged - $used;
            $ratio   = $balance / $charged;

            if ($ratio > $thresholdRatio) {
                continue;
            }

            $result[] = [
                'hospital_id'   => (int) $row['hospital_id'],
                'hospital_name' => (string) $row['hospital_name'],
                'charged'       => $charged,
                'used'          => $used,
                'balance'       => $balance,
                'ratio'         => $ratio,
            ];
        }

        usort($result, static fn (array $a, array $b): int => $a['ratio'] <=> $b['ratio']);

        return $result;
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
     * 상태 집합의 기간(날짜 범위) 합계 — created_at 기준 [from 00:00:00 ~ to 23:59:59]
     *
     * @param array<int, int> $statuses
     * @param list<int>|null  $hospitalIds 집계 대상 병원 한정 (null이면 전체)
     */
    private function sumByStatusesBetween(array $statuses, string $fromDate, string $toDate, ?array $hospitalIds = null): int
    {
        if ($hospitalIds === []) {
            return 0;
        }

        $builder = $this->db->table('deposits d')
            ->select('IFNULL(SUM(d.price), 0) AS total', false)
            ->whereIn('d.status', $statuses)
            ->where('d.created_at >=', $fromDate . ' 00:00:00')
            ->where('d.created_at <=', $toDate . ' 23:59:59');

        if ($hospitalIds !== null) {
            $builder->join('contracts c', 'c.id = d.contract_id', 'inner')
                ->whereIn('c.hospital_id', $hospitalIds);
        }

        $row = $builder->get()->getRowArray();

        return (int) ($row['total'] ?? 0);
    }

    // ──────────────────────────────────────────────
    // 보고서 생성 대상 스코프 조회 (AI 배치 — 이슈 #65 포털 확장)
    // ──────────────────────────────────────────────

    /**
     * 보고서 생성 대상 병원 목록 — 병원이 연결된 광고주 (계약 가능 상태)
     *
     * @return list<array{hospital_id: int, hospital_name: string}>
     */
    public function getReportableHospitals(): array
    {
        $rows = $this->db->table('advertisers')
            ->select('hospital_id, hospital_name')
            ->where('hospital_id IS NOT NULL')
            ->groupBy('hospital_id, hospital_name')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $r): array => [
            'hospital_id'   => (int) $r['hospital_id'],
            'hospital_name' => (string) ($r['hospital_name'] ?? ''),
        ], $rows);
    }

    /**
     * 보고서 생성 대상 대행사 목록 — 광고주를 보유한 대행사 사용자
     *
     * @return list<array{agency_user_id: int, agency_name: string}>
     */
    public function getReportableAgencies(): array
    {
        $rows = $this->db->table('advertisers a')
            ->select('a.agency_user_id, u.username AS agency_name', false)
            ->join('users u', 'u.id = a.agency_user_id', 'inner')
            ->where('a.agency_user_id IS NOT NULL')
            ->groupBy('a.agency_user_id, u.username')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $r): array => [
            'agency_user_id' => (int) $r['agency_user_id'],
            'agency_name'    => (string) ($r['agency_name'] ?? ''),
        ], $rows);
    }

    /**
     * 대행사 소속 광고주들의 병원 id 집합
     *
     * @return list<int>
     */
    public function hospitalIdsForAgency(int $agencyUserId): array
    {
        $rows = $this->db->table('advertisers')
            ->select('hospital_id')
            ->where('agency_user_id', $agencyUserId)
            ->where('hospital_id IS NOT NULL')
            ->groupBy('hospital_id')
            ->get()
            ->getResultArray();

        return array_values(array_map(static fn (array $r): int => (int) $r['hospital_id'], $rows));
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
