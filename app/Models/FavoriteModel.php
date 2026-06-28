<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 찜/즐겨찾기 모델 — 외부(소비자) 앱 공용 (이슈 #98)
 *
 * target_type 으로 대상 유형을 구분한다. 토글은 행 insert/delete 로 처리한다.
 */
class FavoriteModel extends Model
{
    protected $table      = 'favorites';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $updatedField  = ''; // updated_at 컬럼 없음
    protected $returnType    = 'array';

    protected $allowedFields = [
        'user_id',
        'target_type',
        'target_id',
    ];

    public const TYPE_CAMPAIGN = 'campaign';
    public const TYPE_HOSPITAL  = 'hospital';

    /**
     * 찜 토글 — 있으면 삭제, 없으면 추가. 토글 후 찜 상태(true=찜됨)를 반환한다.
     */
    public function toggle(int $userId, string $targetType, int $targetId): bool
    {
        $existing = $this->where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->first();

        if ($existing !== null) {
            $this->delete($existing['id']);

            return false;
        }

        $this->insert([
            'user_id'     => $userId,
            'target_type' => $targetType,
            'target_id'   => $targetId,
        ]);

        return true;
    }

    /**
     * 주어진 대상 ID 목록 중 사용자가 찜한 ID만 반환 — 목록 is_liked 오버레이용 (N+1 방지)
     *
     * @param array<int, int> $targetIds
     * @return array<int, int> 찜한 target_id 목록
     */
    public function likedTargetIds(int $userId, string $targetType, array $targetIds): array
    {
        if ($targetIds === []) {
            return [];
        }

        $rows = $this->select('target_id')
            ->where('user_id', $userId)
            ->where('target_type', $targetType)
            ->whereIn('target_id', $targetIds)
            ->findAll();

        return array_map(static fn (array $r): int => (int) $r['target_id'], $rows);
    }
}
