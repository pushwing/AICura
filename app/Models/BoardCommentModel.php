<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 후기 댓글 모델 (board_comments) — 외부(소비자) 앱 (이슈 #102)
 */
class BoardCommentModel extends Model
{
    public const DELETE_NONE = 0;
    public const DELETE_DONE = 1;

    protected $table         = 'board_comments';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'board_id',
        'user_id',
        'user_name',
        'contents',
        'is_secret',
        'is_list',
        'is_delete',
    ];

    /**
     * 후기의 공개 댓글 목록 (오래된 순, 페이징)
     *
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getByBoard(int $boardId, int $page, int $limit): array
    {
        $builder = $this->where('board_id', $boardId)
            ->where('is_delete', self::DELETE_NONE)
            ->where('is_secret', 0);

        $total = $builder->countAllResults(false);

        $list = $builder
            ->select('id, user_id, user_name, contents, created_at')
            ->orderBy('id', 'ASC')
            ->findAll($limit, ($page - 1) * $limit);

        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * 댓글 작성 — 생성된 댓글 id 반환.
     */
    public function add(int $boardId, int $userId, string $userName, string $contents): int
    {
        return (int) $this->insert([
            'board_id'  => $boardId,
            'user_id'   => $userId,
            'user_name' => $userName,
            'contents'  => $contents,
        ], true);
    }

    /**
     * 본인 소유·미삭제 댓글 조회.
     *
     * @return array<string, mixed>|null
     */
    public function findOwned(int $id, int $boardId, int $userId): ?array
    {
        return $this->where('id', $id)
            ->where('board_id', $boardId)
            ->where('user_id', $userId)
            ->where('is_delete', self::DELETE_NONE)
            ->first();
    }

    /**
     * 댓글 soft delete.
     */
    public function softDelete(int $id): void
    {
        $this->update($id, ['is_delete' => self::DELETE_DONE]);
    }
}
