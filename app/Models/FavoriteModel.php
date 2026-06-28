<?php

namespace App\Models;

use CodeIgniter\Model;
use Throwable;

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
     *
     * 동시 더블탭(같은 사용자가 거의 동시에 두 번 요청)으로 insert 가 유니크 키
     * (uniq_favorites_user_target)를 위반하면, 다른 요청이 이미 찜을 생성한 것이므로
     * 멱등하게 찜됨(true)으로 처리한다.
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

        try {
            $this->insert([
                'user_id'     => $userId,
                'target_type' => $targetType,
                'target_id'   => $targetId,
            ]);
        } catch (Throwable $e) {
            // 유니크 키 충돌(uniq_favorites_user_target)만 멱등 처리하고,
            // 그 외 DB 오류는 그대로 전파한다.
            if (stripos($e->getMessage(), 'uniq_favorites_user_target') === false
                && stripos($e->getMessage(), 'Duplicate') === false) {
                throw $e;
            }
        }

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
