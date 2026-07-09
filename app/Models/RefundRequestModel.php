<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;
use Throwable;

/**
 * 환불요청 (refund_requests) — 이슈 #52
 *
 * 광고주가 신청 건을 중복/결번/취소로 변경하면 생성되는 운영자 처리 대기 건.
 * 운영자 승인 시 CPA 차감액을 deposits에 +(기타충전)로 복원하고, 거부 시 사유를 남긴다.
 *
 *   status: 1 대기, 2 승인, 3 거부
 */
class RefundRequestModel extends Model
{
    public const STATUS_PENDING  = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_REJECTED = 3;

    /**
     * @var array<int, string>
     */
    public const STATUS_LABELS = [
        1 => '대기',
        2 => '승인',
        3 => '거부',
    ];

    /**
     * deposits 환불 복원 거래 상태 (기타충전 +)
     */
    private const int DEPOSIT_RESTORE_STATUS = 4;

    protected $table         = 'refund_requests';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'call_request_id',
        'hospital_id',
        'requested_status',
        'reason',
        'status',
        'reject_reason',
        'deposit_id',
        'requested_by',
        'processed_by',
        'processed_at',
    ];

    /**
     * 신청 건이 환불요청으로 잠겨 있는지 — 대기(1) 또는 승인(2) 건이 존재하면 true.
     *
     * 거부(3)된 건은 잠금을 해제하므로 제외한다. (광고주 재변경 허용)
     */
    public function isLocked(int $callRequestId): bool
    {
        return $this->where('call_request_id', $callRequestId)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED])
            ->countAllResults() > 0;
    }

    /**
     * 신청 건의 최신 환불요청 1건 (상태 배너 표시용)
     *
     * @return array<string, mixed>|null
     */
    public function latestByCallRequest(int $callRequestId): ?array
    {
        return $this->where('call_request_id', $callRequestId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * 환불요청 생성 — 동일 신청 건에 대기 건이 이미 있으면 생성하지 않는다.
     *
     * @return int 생성된 환불요청 id (이미 잠금 상태면 0)
     */
    public function createRequest(int $callRequestId, int $hospitalId, int $requestedStatus, ?string $reason, ?int $requestedBy): int
    {
        if ($this->isLocked($callRequestId)) {
            return 0;
        }

        $this->insert([
            'call_request_id'  => $callRequestId,
            'hospital_id'      => $hospitalId,
            'requested_status' => $requestedStatus,
            'reason'           => $reason !== null && trim($reason) !== '' ? $reason : null,
            'status'           => self::STATUS_PENDING,
            'requested_by'     => $requestedBy ?: null,
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * 운영자 환불요청 목록 (신청자·캠페인·병원명 조인, 상태 필터·페이징)
     *
     * @param array<string, mixed> $params
     *
     * @return array{list: list<array<string, mixed>>, total: int}
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('refund_requests rr')
            ->select('rr.id, rr.call_request_id, rr.requested_status, rr.reason, rr.status, rr.reject_reason, rr.processed_at, rr.created_at')
            ->select('cr.name AS applicant_name, cr.phone AS applicant_phone, cr.event_cost, cr.is_charged', false)
            ->select('h.name AS hospital_name', false)
            ->select('c.ad_title AS campaign_title', false)
            ->join('call_requests cr', 'cr.id = rr.call_request_id', 'left')
            ->join('hospitals h', 'h.id = rr.hospital_id', 'left')
            ->join('campaigns c', 'c.id = cr.campaign_id', 'left');

        if (isset($params['status']) && $params['status'] !== '') {
            $builder->where('rr.status', (int) $params['status']);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        /** @var list<array<string, mixed>> $list */
        $list = $builder
            ->orderBy('rr.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * 환불요청 승인 — 대기(1) 건만 처리. 과금된 신청이면 차감액을 deposits에 +로 복원한다.
     *
     * 복원 거래(deposits status=4 기타충전)를 기록하고 call_requests.is_charged를 0으로 되돌려
     * 이중 복원을 방지한다. 전 과정을 트랜잭션으로 원자 처리한다.
     *
     * @throws RuntimeException 환불요청 없음·이미 처리됨
     */
    public function approve(int $id, int $adminUserId): void
    {
        $refund = $this->find($id);
        if ($refund === null) {
            throw new RuntimeException('환불요청을 찾을 수 없습니다.');
        }
        if ((int) $refund['status'] !== self::STATUS_PENDING) {
            throw new RuntimeException('이미 처리된 환불요청입니다.');
        }

        $db = $this->db;
        $db->transBegin();

        try {
            $now       = date('Y-m-d H:i:s');
            $depositId = null;

            $callRequest = $db->table('call_requests')
                ->select('id, campaign_id, event_cost, is_charged')
                ->where('id', (int) $refund['call_request_id'])
                ->get()
                ->getRowArray();

            // 과금된 신청만 복원 대상 (미과금 건은 복원할 차감액이 없음)
            if ($callRequest !== null && (int) $callRequest['is_charged'] === 1 && (int) $callRequest['event_cost'] > 0) {
                $campaign = $db->table('campaigns')
                    ->select('contract_id, contract_order_id')
                    ->where('id', (int) $callRequest['campaign_id'])
                    ->get()
                    ->getRowArray();

                if ($campaign !== null && ! empty($campaign['contract_id']) && ! empty($campaign['contract_order_id'])) {
                    $db->table('deposits')->insert([
                        'status'            => self::DEPOSIT_RESTORE_STATUS, // 기타충전 (+)
                        'is_minus'          => 0,
                        'contract_id'       => (int) $campaign['contract_id'],
                        'contract_order_id' => (int) $campaign['contract_order_id'],
                        'users_id'          => $adminUserId ?: null,
                        'price'             => (int) $callRequest['event_cost'],
                        'note'              => 'CPA 환불 복원 (call_request:' . (int) $refund['call_request_id'] . ')',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ]);
                    $depositId = (int) $db->insertID();

                    // 이중 복원 방지 — 과금 플래그 해제
                    $db->table('call_requests')
                        ->where('id', (int) $callRequest['id'])
                        ->update(['is_charged' => 0, 'updated_at' => $now]);
                }
            }

            $db->table('refund_requests')
                ->where('id', $id)
                ->where('status', self::STATUS_PENDING)
                ->update([
                    'status'       => self::STATUS_APPROVED,
                    'deposit_id'   => $depositId,
                    'processed_by' => $adminUserId ?: null,
                    'processed_at' => $now,
                    'updated_at'   => $now,
                ]);

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();

            throw $e;
        }
    }

    /**
     * 환불요청 거부 — 대기(1) 건만 처리. 거부 사유를 저장(광고주 노출)하고 잠금을 해제한다.
     *
     * @throws RuntimeException 환불요청 없음·이미 처리됨
     */
    public function reject(int $id, int $adminUserId, string $reason): void
    {
        $refund = $this->find($id);
        if ($refund === null) {
            throw new RuntimeException('환불요청을 찾을 수 없습니다.');
        }
        if ((int) $refund['status'] !== self::STATUS_PENDING) {
            throw new RuntimeException('이미 처리된 환불요청입니다.');
        }

        $this->update($id, [
            'status'        => self::STATUS_REJECTED,
            'reject_reason' => $reason,
            'processed_by'  => $adminUserId ?: null,
            'processed_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
