<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 헬스포인트 변동 내역(ledger) 모델 — 외부(소비자) 앱 (이슈 #97, #114)
 *
 * 잔액은 users.health_point 가 보유하며, 본 모델은 적립/차감 내역만 기록·조회한다.
 * 적립/차감 트리거는 HealthPointService 가 트랜잭션으로 처리한다.
 */
class HealthPointLogModel extends Model
{
    protected $table         = 'health_point_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $updatedField  = ''; // updated_at 없음
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id',
        'amount',
        'balance_after',
        'type',
        'ref_id',
        'memo',
    ];

    /**
     * 사용자 포인트 내역 (최신순, 페이징)
     *
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getByUser(int $userId, int $page, int $limit): array
    {
        $builder = $this->where('user_id', $userId);

        $total = $builder->countAllResults(false);

        $list = $builder
            ->select('id, amount, balance_after, type, memo, created_at')
            ->orderBy('id', 'DESC')
            ->findAll($limit, ($page - 1) * $limit);

        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * 특정 사유·발생원에 대한 적립 로그 존재 여부 (멱등 판단용). (이슈 #114)
     *
     * 예: (user, type='review', ref_id=boardId) 가 이미 있으면 후기 재적립을 막는다.
     */
    public function existsForRef(int $userId, string $type, int $refId): bool
    {
        return $this->where('user_id', $userId)
            ->where('type', $type)
            ->where('ref_id', $refId)
            ->countAllResults() > 0;
    }

    /**
     * 특정 발생원(ref_id)에 묶인 변동량 순합. (이슈 #114)
     *
     * 후기 삭제 회수 시, 적립(+)에서 이미 회수(-)된 금액을 차감한 잔여 적립액을 구한다.
     *
     * @param list<string> $types 합산 대상 사유 코드
     */
    public function netForRef(int $userId, int $refId, array $types): int
    {
        if ($types === []) {
            return 0;
        }

        $row = $this->db->table($this->table)
            ->selectSum('amount', 'net')
            ->where('user_id', $userId)
            ->where('ref_id', $refId)
            ->whereIn('type', $types)
            ->get()
            ->getRowArray();

        return (int) ($row['net'] ?? 0);
    }
}
