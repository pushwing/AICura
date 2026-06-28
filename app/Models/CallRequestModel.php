<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 이벤트 신청 DB (call_requests)
 *
 * 앱/웹에서 이벤트 신청 시 생성되는 CPA 과금의 핵심 테이블.
 *
 * status (9단계):
 *   1 미확인, 2 부재중, 3 취소, 4 기타, 5 예약,
 *   6 예약취소, 7 내원완료, 8 중복, 9 결번
 */
class CallRequestModel extends Model
{
    protected $table      = 'call_requests';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'hospital_id',
        'campaign_id',
        'user_id',
        'device',
        'status',
        'is_charged',
        'confirm_date',
        'reserved_at',
        'name',
        'phone',
        'content',
        'call_time',
        'age',
        'sex',
        'privacy_agree',
        'supply_third_party_agree',
        'event_cost',
        'funnel',
        'region',
        'finger_print',
        'is_delete',
        'parent_id',
        'is_save_phone',
        'ai_score',
        'ai_summary',
        'ai_next_action',
        'ai_status',
    ];

    /** @var array<int, string> 신청 상태 라벨 (1~9) */
    public const STATUSES = [
        1 => '미확인',
        2 => '부재중',
        3 => '취소',
        4 => '기타',
        5 => '예약',
        6 => '예약취소',
        7 => '내원완료',
        8 => '중복',
        9 => '결번',
    ];

    /** @var array<int, string> 디바이스 라벨 */
    public const DEVICES = [
        1 => '안드로이드',
        2 => 'iOS',
        3 => '웹',
    ];

    /** 예약 상태 — 변경 시 예약 일시 입력 필수 */
    public const STATUS_RESERVED = 5;

    /**
     * @var array<int, string> 환불요청을 유발하는 상태 (중복/결번/취소)
     *
     * 광고주가 이 상태로 변경하면 운영자에게 환불요청이 생성되고, 운영자 처리 전까지
     * 광고주의 추가 상태 변경이 잠긴다. (이슈 #52)
     */
    public const REFUND_STATUSES = [
        3 => '취소',
        8 => '중복',
        9 => '결번',
    ];

    /**
     * AI 리드 분석 큐 상태 (이슈 #72)
     *
     * Redis 등 별도 큐 인프라 없이 ai_status 컬럼을 큐로 사용한다.
     * 신청·메모 저장 시 PENDING으로 표시하고, leads:analyze 커맨드가 소비한다.
     */
    public const AI_STATUS_IDLE    = 0; // 미분석
    public const AI_STATUS_PENDING = 1; // 대기 (큐 적재됨)
    public const AI_STATUS_DONE    = 2; // 완료
    public const AI_STATUS_FAILED  = 3; // 실패

    /**
     * 신청 목록 (병원별·캠페인별·상태별 필터, 페이징)
     *
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('call_requests cr')
            ->select('cr.id, cr.hospital_id, cr.campaign_id, cr.status, cr.is_charged, cr.name, cr.phone, cr.event_cost, cr.confirm_date, cr.created_at, cr.ai_score, cr.ai_summary')
            ->select('h.name AS hospital_name', false)
            ->select('c.ad_title AS campaign_title', false)
            ->join('hospitals h', 'h.id = cr.hospital_id', 'left')
            ->join('campaigns c', 'c.id = cr.campaign_id', 'left')
            ->where('cr.is_delete', 0);

        if (!empty($params['hospital_id'])) {
            $builder->where('cr.hospital_id', (int) $params['hospital_id']);
        }
        if (!empty($params['campaign_id'])) {
            $builder->where('cr.campaign_id', (int) $params['campaign_id']);
        }
        if (!empty($params['status'])) {
            $builder->where('cr.status', (int) $params['status']);
        }
        if (!empty($params['keyword'])) {
            $builder->groupStart()
                ->like('cr.name', $params['keyword'])
                ->orLike('cr.phone', $params['keyword'])
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        // 전환점수순: 점수 높은 순(NULL은 MySQL DESC에서 자동으로 뒤로), 동점은 최신순
        if (($params['sort'] ?? '') === 'score') {
            $builder->orderBy('cr.ai_score', 'DESC')->orderBy('cr.id', 'DESC');
        } else {
            $builder->orderBy('cr.id', 'DESC');
        }

        $list = $builder
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 특정 사용자가 신청한 이벤트 목록 (캠페인명 포함, 페이징) — 사용자 상세용. (이슈 #90)
     *
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getByUser(int $userId, int $limit, int $offset): array
    {
        $builder = $this->db->table('call_requests cr')
            ->select('cr.id, cr.campaign_id, cr.status, cr.created_at')
            ->select('c.ad_title AS campaign_title', false)
            ->join('campaigns c', 'c.id = cr.campaign_id', 'left')
            ->where('cr.user_id', $userId)
            ->where('cr.is_delete', 0);

        $total = (clone $builder)->countAllResults(false);

        $list = $builder
            ->orderBy('cr.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 신청 상세 (병원·캠페인명 + 메모 목록 포함)
     *
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        $row = $this->db->table('call_requests cr')
            ->select('cr.*')
            ->select('h.name AS hospital_name', false)
            ->select('c.ad_title AS campaign_title', false)
            ->join('hospitals h', 'h.id = cr.hospital_id', 'left')
            ->join('campaigns c', 'c.id = cr.campaign_id', 'left')
            ->where('cr.id', $id)
            ->where('cr.is_delete', 0)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        $row['memos'] = model(CallMemoModel::class)->getListByRequest($id);

        return $row;
    }

    /**
     * 상태 변경 (9단계 중 유효한 값으로만)
     *
     * - 미확인(1)에서 다른 상태로 처음 전환되는 시점에 confirm_date를 기록한다.
     * - 예약(5)으로 변경 시 예약 일시($reservedAt)를 함께 저장한다.
     * - 모든 상태 전환은 call_memos에 시스템 메모로 히스토리를 남긴다. (이슈 #52)
     *
     * @param string|null $reservedAt 예약 일시 (Y-m-d H:i[:s]) — 예약 상태일 때 필수
     * @param int|null    $memoUserId 변경 주체 user id (히스토리 메모 작성자)
     * @throws \RuntimeException 신청 건 없음·유효하지 않은 상태·예약 일시 누락
     */
    public function changeStatus(int $id, int $status, ?string $reservedAt = null, ?int $memoUserId = null): void
    {
        if (!isset(self::STATUSES[$status])) {
            throw new \RuntimeException('유효하지 않은 상태입니다.');
        }

        $request = $this->select('id, status, confirm_date')
            ->where('is_delete', 0)
            ->find($id);
        if ($request === null) {
            throw new \RuntimeException('신청 건을 찾을 수 없습니다.');
        }

        $fromStatus = (int) $request['status'];
        $update     = ['status' => $status];

        // 최초 확인 시점 기록 (미확인 → 확인 계열)
        if ($fromStatus === 1 && $status !== 1 && empty($request['confirm_date'])) {
            $update['confirm_date'] = date('Y-m-d H:i:s');
        }

        // 예약 상태 전환 — 예약 일시 필수
        if ($status === self::STATUS_RESERVED) {
            $normalized = $this->normalizeReservedAt($reservedAt);
            if ($normalized === null) {
                throw new \RuntimeException('예약 상태는 예약 일시를 입력해야 합니다.');
            }
            $update['reserved_at'] = $normalized;
        }

        $this->update($id, $update);

        $this->recordStatusHistory($id, $fromStatus, $status, $update['reserved_at'] ?? null, $memoUserId);
    }

    /**
     * 예약 일시 정규화 — 유효한 datetime이면 'Y-m-d H:i:s', 아니면 null.
     */
    private function normalizeReservedAt(?string $reservedAt): ?string
    {
        if ($reservedAt === null || trim($reservedAt) === '') {
            return null;
        }

        $ts = strtotime($reservedAt);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $ts);
    }

    /**
     * 상태 전환 히스토리를 call_memos에 시스템 메모로 기록한다.
     */
    private function recordStatusHistory(int $id, int $fromStatus, int $toStatus, ?string $reservedAt, ?int $memoUserId): void
    {
        $fromLabel = self::STATUSES[$fromStatus] ?? (string) $fromStatus;
        $toLabel   = self::STATUSES[$toStatus] ?? (string) $toStatus;

        $memo = sprintf('[상태변경] %s → %s', $fromLabel, $toLabel);
        if ($reservedAt !== null) {
            $memo .= ' (예약 ' . $reservedAt . ')';
        }

        model(CallMemoModel::class)->insert([
            'call_request_id' => $id,
            'user_id'         => $memoUserId ?: null,
            'memo'            => $memo,
        ]);
    }

    /**
     * CPA 과금 (deposits status=3 소진 1회 기록) — 멱등 처리.
     *
     * 이벤트 신청이 API로 들어오는 순간 호출되도록 설계되었으며,
     * is_charged 플래그로 이중과금을 방지한다. (Admin에서는 자동 호출하지 않음)
     *
     * 동시 호출 경쟁 조건은 트랜잭션 내 조건부 UPDATE(`WHERE is_charged = 0`)로
     * 선점하여 차단한다 — 선점에 성공한 호출만 deposits에 1건을 기록한다.
     *
     * @return bool 실제 과금 발생 여부 (이미 과금/금액 없음/계약 미연결 시 false)
     */
    public function chargeCpa(int $id, int $userId): bool
    {
        // 사전 검증 (빠른 종료 — 실제 멱등 보장은 아래 조건부 UPDATE)
        $request = $this->find($id);
        if ($request === null || (int) $request['is_charged'] === 1) {
            return false;
        }

        $eventCost = (int) $request['event_cost'];
        if ($eventCost <= 0) {
            return false;
        }

        // 캠페인 → 계약 연결 정보 조회
        $campaign = $this->db->table('campaigns')
            ->select('contract_id, contract_order_id')
            ->where('id', (int) $request['campaign_id'])
            ->get()
            ->getRowArray();

        if ($campaign === null
            || empty($campaign['contract_id'])
            || empty($campaign['contract_order_id'])
        ) {
            return false;
        }

        $db = $this->db;
        $db->transBegin();

        try {
            // 원자적 선점: is_charged 0 → 1. 동시 호출 중 한 건만 성공한다.
            $db->table('call_requests')
                ->where('id', $id)
                ->where('is_charged', 0)
                ->update(['is_charged' => 1, 'updated_at' => date('Y-m-d H:i:s')]);

            if ($db->affectedRows() === 0) {
                // 이미 다른 호출이 과금을 선점함 → 이중과금 방지
                $db->transRollback();

                return false;
            }

            $now = date('Y-m-d H:i:s');

            $db->table('deposits')->insert([
                'status'            => 3, // DB소진
                'is_minus'          => 1,
                'contract_id'       => (int) $campaign['contract_id'],
                'contract_order_id' => (int) $campaign['contract_order_id'],
                'users_id'          => $userId ?: null,
                'price'             => $eventCost,
                'note'              => 'CPA 소진 (call_request:' . $id . ')',
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            $db->transCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    // ──────────────────────────────────────────────
    // AI 리드 분석 큐 (이슈 #72)
    // ──────────────────────────────────────────────

    /**
     * 분석 큐에 적재 — ai_status를 PENDING으로 표시 (동기, AI 호출 없음).
     *
     * 신청 INSERT·메모 저장 시점에 호출한다. 실제 분석은 leads:analyze 커맨드가
     * 비동기로 수행하므로 요청 응답을 막지 않는다. 앱/웹 신청 INSERT 경로가
     * 추가되면 이 메서드를 동일하게 호출하면 된다.
     */
    public function enqueueAnalysis(int $id): void
    {
        $this->where('is_delete', 0)
            ->set('ai_status', self::AI_STATUS_PENDING)
            ->update($id);
    }

    /**
     * 분석 대기(PENDING) 건을 분석 입력 필드만 추려서 반환 (오래된 순).
     *
     * PII(name·phone)는 프롬프트에 넣지 않으므로 조회 대상에서 제외한다.
     *
     * @return array<int, array{id: int, content: string|null, funnel: string|null, age: int|null, sex: int}>
     */
    public function getPendingAnalysis(int $limit = 50): array
    {
        /** @var array<int, array{id: int, content: string|null, funnel: string|null, age: int|null, sex: int}> $rows */
        $rows = $this->select('id, content, funnel, age, sex')
            ->where('ai_status', self::AI_STATUS_PENDING)
            ->where('is_delete', 0)
            ->orderBy('id', 'ASC')
            ->findAll(max(1, $limit));

        return $rows;
    }

    /**
     * 분석 결과 저장 — 점수·요약·다음액션 + 상태를 DONE으로.
     *
     * @param array{score: int, summary: string, next_action: string} $result
     */
    public function saveAnalysis(int $id, array $result): void
    {
        $this->update($id, [
            'ai_score'       => $result['score'],
            'ai_summary'     => $result['summary'],
            'ai_next_action' => $result['next_action'],
            'ai_status'      => self::AI_STATUS_DONE,
        ]);
    }

    /**
     * 분석 실패 표시 — 재처리 대상에서 빠지도록 FAILED로.
     */
    public function markAnalysisFailed(int $id): void
    {
        $this->update($id, ['ai_status' => self::AI_STATUS_FAILED]);
    }
}
