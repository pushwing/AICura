<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 헬스포인트 변동 내역(ledger) 모델 — 외부(소비자) 앱 (이슈 #97)
 *
 * 잔액은 users.health_point 가 보유하며, 본 모델은 적립/차감 내역만 기록·조회한다.
 * 적립/차감 트리거는 아직 없으므로 현재는 조회 위주로 사용한다.
 */
class HealthPointLogModel extends Model
{
    protected $table      = 'health_point_logs';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $updatedField  = ''; // updated_at 없음
    protected $returnType    = 'array';

    protected $allowedFields = [
        'user_id',
        'amount',
        'balance_after',
        'type',
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
}
