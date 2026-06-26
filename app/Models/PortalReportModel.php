<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 포털(광고주·광고대행사) 리포트 모델 (이슈 #56)
 *
 * 운영자 매출 리포트(ReportModel)와 동일한 deposits status 의미·집계 규칙을 사용하되
 * 범위를 좁힌다.
 *   - 광고주: 자기 병원(hospital_id) 매출만
 *   - 대행사: 소속 광고주(agency_user_id) 합계 + 광고주별 개별
 *
 * 데이터 경로: deposits.contract_id → contracts.hospital_id ← advertisers.hospital_id
 *
 * deposits.status (ReportModel과 동일):
 *   충전(+): 2 계약충전 / 12 이월충전
 *   소진(-): 3 DB소진 / 5 기타- / 8 기타소진 / 9 발행취소 / 10 계약취소 / 11 이월소진
 *   환불(-): 6 발행환불 / 7 계약환불
 *   CPA 환불 복원: 4 — 신청DB 환불요청 승인 시 소진을 상계(−)하는 거래. 충전이 아니라
 *                  소진 차감으로 집계해 전체 충전금에 포함하지 않는다.
 * call_requests.status: 7 내원완료
 */
class PortalReportModel extends Model
{
    protected $table      = 'deposits';
    protected $returnType = 'array';

    private const STATUS_CHARGED    = [2, 12];
    private const STATUS_CONSUMED   = [3, 5, 8, 9, 10, 11];
    private const STATUS_REFUNDED   = [6, 7];
    // CPA 환불 복원 — 소진을 상계(−)하는 거래 (소진 합계에서 차감)
    private const STATUS_CPA_REFUND = [4];

    private const CALL_VISITED = 7;

    /** 드라이버별 연도 표현식 (MySQL: YEAR / SQLite3: strftime) */
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

    // ──────────────────────────────────────────────
    // 광고주 (단일 병원)
    // ──────────────────────────────────────────────

    /**
     * 병원 연간 매출 KPI (충전·소진·환불·잔액)
     *
     * @return array{charged: int, consumed: int, refunded: int, balance: int}
     */
    public function getHospitalYearKpi(int $hospitalId, int $year): array
    {
        $row = $this->db->table('deposits d')
            ->select($this->statusSum(self::STATUS_CHARGED) . ' AS charged', false)
            ->select($this->statusSum(self::STATUS_CONSUMED, self::STATUS_CPA_REFUND) . ' AS consumed', false)
            ->select($this->statusSum(self::STATUS_REFUNDED) . ' AS refunded', false)
            ->join('contracts c', 'c.id = d.contract_id', 'inner')
            ->where('c.hospital_id', $hospitalId)
            ->where($this->yearExpr('d.created_at'), $year)
            ->get()
            ->getRowArray();

        $charged  = (int) ($row['charged'] ?? 0);
        $consumed = (int) ($row['consumed'] ?? 0);
        $refunded = (int) ($row['refunded'] ?? 0);

        return [
            'charged'  => $charged,
            'consumed' => $consumed,
            'refunded' => $refunded,
            'balance'  => $charged - $consumed - $refunded,
        ];
    }

    /**
     * 병원 월별 충전·소진 집계 — 각 12개 원소 배열 (인덱스 0 = 1월)
     *
     * @return array{charged: array<int, int>, consumed: array<int, int>}
     */
    public function getHospitalMonthlyRevenue(int $hospitalId, int $year): array
    {
        $monthExpr = $this->monthExpr('d.created_at');

        $rows = $this->db->table('deposits d')
            ->select($monthExpr . ' AS month', false)
            ->select($this->statusSum(self::STATUS_CHARGED) . ' AS charged', false)
            ->select($this->statusSum(self::STATUS_CONSUMED, self::STATUS_CPA_REFUND) . ' AS consumed', false)
            ->join('contracts c', 'c.id = d.contract_id', 'inner')
            ->where('c.hospital_id', $hospitalId)
            ->where($this->yearExpr('d.created_at'), $year)
            ->groupBy($monthExpr)
            ->get()
            ->getResultArray();

        $charged  = array_fill(0, 12, 0);
        $consumed = array_fill(0, 12, 0);
        foreach ($rows as $row) {
            $idx = (int) $row['month'] - 1;
            if ($idx >= 0 && $idx < 12) {
                $charged[$idx]  = (int) $row['charged'];
                $consumed[$idx] = (int) $row['consumed'];
            }
        }

        return ['charged' => $charged, 'consumed' => $consumed];
    }

    /**
     * 병원 신청DB 요약 (연간 신청 건수·내원완료 건수)
     *
     * @return array{requested: int, visited: int}
     */
    public function getHospitalCallSummary(int $hospitalId, int $year): array
    {
        $row = $this->db->table('call_requests cr')
            ->select('COUNT(cr.id) AS requested', false)
            ->select('SUM(CASE WHEN cr.status = ' . self::CALL_VISITED . ' THEN 1 ELSE 0 END) AS visited', false)
            ->where('cr.hospital_id', $hospitalId)
            ->where('cr.is_delete', 0)
            ->where($this->yearExpr('cr.created_at'), $year)
            ->get()
            ->getRowArray();

        return [
            'requested' => (int) ($row['requested'] ?? 0),
            'visited'   => (int) ($row['visited'] ?? 0),
        ];
    }

    /**
     * 병원 캠페인별 신청·내원완료 통계 (call_requests 기준, 연간)
     *
     * @return list<array<string, mixed>>
     */
    public function getHospitalCampaignStats(int $hospitalId, int $year): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table('call_requests cr')
            ->select('c.id AS campaign_id, c.ad_title', false)
            ->select('COUNT(cr.id) AS request_count', false)
            ->select('SUM(CASE WHEN cr.status = ' . self::CALL_VISITED . ' THEN 1 ELSE 0 END) AS visited_count', false)
            ->select('IFNULL(SUM(cr.event_cost), 0) AS total_cost', false)
            ->join('campaigns c', 'c.id = cr.campaign_id', 'inner')
            ->where('cr.hospital_id', $hospitalId)
            ->where('cr.is_delete', 0)
            ->where('c.is_deleted', 0)
            ->where($this->yearExpr('cr.created_at'), $year)
            ->groupBy('c.id, c.ad_title')
            ->orderBy('request_count', 'DESC')
            ->get()
            ->getResultArray();

        return $rows;
    }

    // ──────────────────────────────────────────────
    // 대행사 (소속 광고주 합계 + 개별)
    // ──────────────────────────────────────────────

    /**
     * 대행사 소속 광고주별 매출·신청 집계 (단일 GROUP BY 쿼리 2건으로 N+1 방지)
     *
     * @return list<array{
     *     advertiser_id: int,
     *     hospital_id: int,
     *     hospital_name: string,
     *     charged: int,
     *     consumed: int,
     *     refunded: int,
     *     balance: int,
     *     requested: int,
     *     visited: int
     * }>
     */
    public function getAgencyAdvertiserBreakdown(int $agencyUserId, int $year): array
    {
        $yearCol = $this->yearExpr('d.created_at');

        // 1) 광고주별 deposits 집계 — 연도 조건을 CASE 안에 넣어 매출 없는 광고주도 0으로 유지
        $depRows = $this->db->table('advertisers a')
            ->select('a.id AS advertiser_id, a.hospital_id, a.hospital_name', false)
            ->select($this->statusSumForYear(self::STATUS_CHARGED, $yearCol, $year) . ' AS charged', false)
            ->select($this->statusSumForYear(self::STATUS_CONSUMED, $yearCol, $year, self::STATUS_CPA_REFUND) . ' AS consumed', false)
            ->select($this->statusSumForYear(self::STATUS_REFUNDED, $yearCol, $year) . ' AS refunded', false)
            ->join('contracts c', 'c.hospital_id = a.hospital_id', 'left')
            ->join('deposits d', 'd.contract_id = c.id', 'left')
            ->where('a.agency_user_id', $agencyUserId)
            ->groupBy('a.id, a.hospital_id, a.hospital_name')
            ->orderBy('charged', 'DESC')
            ->get()
            ->getResultArray();

        if ($depRows === []) {
            return [];
        }

        // 2) 광고주별 call_requests 집계 (hospital_id 기준) — 별도 쿼리로 행 곱셈 방지
        $hospitalIds = array_values(array_unique(array_map(
            static fn (array $r): int => (int) $r['hospital_id'],
            $depRows
        )));

        $callRows = $this->db->table('call_requests cr')
            ->select('cr.hospital_id', false)
            ->select('COUNT(cr.id) AS requested', false)
            ->select('SUM(CASE WHEN cr.status = ' . self::CALL_VISITED . ' THEN 1 ELSE 0 END) AS visited', false)
            ->whereIn('cr.hospital_id', $hospitalIds)
            ->where('cr.is_delete', 0)
            ->where($this->yearExpr('cr.created_at'), $year)
            ->groupBy('cr.hospital_id')
            ->get()
            ->getResultArray();

        $callByHospital = [];
        foreach ($callRows as $row) {
            $callByHospital[(int) $row['hospital_id']] = [
                'requested' => (int) $row['requested'],
                'visited'   => (int) $row['visited'],
            ];
        }

        $result = [];
        foreach ($depRows as $row) {
            $hospitalId = (int) $row['hospital_id'];
            $charged    = (int) $row['charged'];
            $consumed   = (int) $row['consumed'];
            $refunded   = (int) $row['refunded'];
            $call       = $callByHospital[$hospitalId] ?? ['requested' => 0, 'visited' => 0];

            $result[] = [
                'advertiser_id' => (int) $row['advertiser_id'],
                'hospital_id'   => $hospitalId,
                'hospital_name' => (string) ($row['hospital_name'] ?? ''),
                'charged'       => $charged,
                'consumed'      => $consumed,
                'refunded'      => $refunded,
                'balance'       => $charged - $consumed - $refunded,
                'requested'     => $call['requested'],
                'visited'       => $call['visited'],
            ];
        }

        return $result;
    }

    /**
     * 광고주별 집계 행 목록을 합산한 대행사 전체 KPI
     *
     * @param list<array<string, mixed>> $breakdown getAgencyAdvertiserBreakdown() 결과
     * @return array{advertiser_count: int, charged: int, consumed: int, refunded: int, balance: int, requested: int, visited: int}
     */
    public function summarizeAgency(array $breakdown): array
    {
        $sum = [
            'advertiser_count' => count($breakdown),
            'charged'   => 0,
            'consumed'  => 0,
            'refunded'  => 0,
            'balance'   => 0,
            'requested' => 0,
            'visited'   => 0,
        ];

        foreach ($breakdown as $row) {
            $sum['charged']   += (int) $row['charged'];
            $sum['consumed']  += (int) $row['consumed'];
            $sum['refunded']  += (int) $row['refunded'];
            $sum['balance']   += (int) $row['balance'];
            $sum['requested'] += (int) $row['requested'];
            $sum['visited']   += (int) $row['visited'];
        }

        return $sum;
    }

    // ──────────────────────────────────────────────
    // 집계 헬퍼
    // ──────────────────────────────────────────────

    /**
     * 상태 집합 합계 SELECT 표현식 (연도 조건은 호출부 WHERE에서 처리)
     *
     * $contra 상태는 −price로 합산해 상계(소진 차감)에 사용한다.
     *
     * @param array<int, int> $statuses
     * @param array<int, int> $contra
     */
    private function statusSum(array $statuses, array $contra = []): string
    {
        $list = implode(', ', array_map('intval', $statuses));
        $expr = "CASE WHEN d.status IN ({$list}) THEN d.price";

        if ($contra !== []) {
            $contraList = implode(', ', array_map('intval', $contra));
            $expr .= " WHEN d.status IN ({$contraList}) THEN -d.price";
        }

        return "IFNULL(SUM({$expr} ELSE 0 END), 0)";
    }

    /**
     * 상태 집합 + 연도 조건을 CASE 안에 포함한 합계 표현식
     * (LEFT JOIN 집계에서 연도를 WHERE에 두면 매출 없는 광고주가 누락되므로 CASE로 처리)
     *
     * $contra 상태는 −price로 합산해 상계(소진 차감)에 사용한다.
     *
     * @param array<int, int> $statuses
     * @param array<int, int> $contra
     */
    private function statusSumForYear(array $statuses, string $yearCol, int $year, array $contra = []): string
    {
        $list = implode(', ', array_map('intval', $statuses));
        $expr = "CASE WHEN d.status IN ({$list}) AND {$yearCol} = {$year} THEN d.price";

        if ($contra !== []) {
            $contraList = implode(', ', array_map('intval', $contra));
            $expr .= " WHEN d.status IN ({$contraList}) AND {$yearCol} = {$year} THEN -d.price";
        }

        return "IFNULL(SUM({$expr} ELSE 0 END), 0)";
    }
}
