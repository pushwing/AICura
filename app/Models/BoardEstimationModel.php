<?php

namespace App\Models;

use CodeIgniter\Model;
use Throwable;

/**
 * 후기 평가 모델 (board_estimations) — 좋아요/신고 (이슈 #102)
 *
 *   type : 1 좋아요(추천) · 2 신고
 *
 * boards 의 like_count / complain_count 집계 컬럼과 함께 관리한다.
 */
class BoardEstimationModel extends Model
{
    protected $table      = 'board_estimations';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $updatedField  = ''; // updated_at 없음
    protected $returnType    = 'array';

    protected $allowedFields = [
        'type',
        'board_id',
        'user_id',
    ];

    public const TYPE_LIKE   = 1;
    public const TYPE_REPORT = 2;

    /**
     * 좋아요 토글 — 있으면 삭제, 없으면 추가. 토글 후 좋아요 상태(true=좋아요)를 반환한다.
     */
    public function toggleLike(int $boardId, int $userId): bool
    {
        $existing = $this->where('type', self::TYPE_LIKE)
            ->where('board_id', $boardId)
            ->where('user_id', $userId)
            ->first();

        if ($existing !== null) {
            $this->delete($existing['id']);

            return false;
        }

        $this->insert(['type' => self::TYPE_LIKE, 'board_id' => $boardId, 'user_id' => $userId]);

        return true;
    }

    /**
     * 신고 적재 — 1인 1회(멱등). 신규 신고면 true.
     */
    public function report(int $boardId, int $userId): bool
    {
        $existing = $this->where('type', self::TYPE_REPORT)
            ->where('board_id', $boardId)
            ->where('user_id', $userId)
            ->first();

        if ($existing !== null) {
            return false;
        }

        try {
            $this->insert(['type' => self::TYPE_REPORT, 'board_id' => $boardId, 'user_id' => $userId]);
        } catch (Throwable) {
            return false; // 동시 신고 경쟁 — 멱등 처리
        }

        return true;
    }

    /**
     * 주어진 후기 ID 중 사용자가 좋아요한 board_id만 반환 — is_liked 오버레이용.
     *
     * @param array<int, int> $boardIds
     * @return array<int, int>
     */
    public function likedBoardIds(int $userId, array $boardIds): array
    {
        if ($boardIds === []) {
            return [];
        }

        $rows = $this->select('board_id')
            ->where('type', self::TYPE_LIKE)
            ->where('user_id', $userId)
            ->whereIn('board_id', $boardIds)
            ->findAll();

        return array_map(static fn (array $r): int => (int) $r['board_id'], $rows);
    }
}
