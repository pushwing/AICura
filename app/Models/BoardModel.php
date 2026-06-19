<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 후기/게시판 (boards)
 *
 * type:      1 이벤트, 2 병원, 3 접수
 * is_delete: 0 미삭제, 1 임시삭제, 2 완전삭제 (모두 논리 상태)
 *
 * 신고는 board_estimations.type = 2 로 적재되며 complain_count에 집계된다.
 */
class BoardModel extends Model
{
    protected $table      = 'boards';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'subject',
        'contents',
        'is_notice',
        'is_list',
        'is_delete',
        'delete_memo',
        'delete_date',
    ];

    /** @var array<int, string> 후기 유형 */
    public const TYPES = [
        1 => '이벤트',
        2 => '병원',
        3 => '접수',
    ];

    /** @var array<int, string> 삭제 상태 */
    public const DELETE_STATES = [
        0 => '정상',
        1 => '임시삭제',
        2 => '완전삭제',
    ];

    public const DELETE_NONE = 0;
    public const DELETE_TEMP = 1;
    public const DELETE_FULL = 2;

    // board_estimations.type
    public const ESTIMATION_REPORT = 2; // 신고

    /**
     * 후기 목록 (유형·삭제상태·신고 필터, 페이징)
     *
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('boards')
            ->select('id, type, target_id, subject, user_name, rate_sum, like_count, complain_count, is_delete, created_at');

        if (($params['type'] ?? '') !== '') {
            $builder->where('type', (int) $params['type']);
        }
        // 삭제 상태: 빈 값이면 전체, 값이 있으면 해당 상태
        if (($params['is_delete'] ?? '') !== '') {
            $builder->where('is_delete', (int) $params['is_delete']);
        }
        // 신고만 보기
        if (!empty($params['reported'])) {
            $builder->where('complain_count >', 0);
        }
        if (($params['keyword'] ?? '') !== '') {
            $builder->groupStart()
                ->like('subject', $params['keyword'])
                ->orLike('user_name', $params['keyword'])
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 후기 상세 (신고 내역 포함)
     *
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        $board = $this->find($id);
        if ($board === null) {
            return null;
        }

        $board['reports'] = $this->db->table('board_estimations be')
            ->select('be.id, be.user_id, be.created_at', false)
            ->select('u.username AS reporter_name', false)
            ->join('users u', 'u.id = be.user_id', 'left')
            ->where('be.board_id', $id)
            ->where('be.type', self::ESTIMATION_REPORT)
            ->orderBy('be.id', 'DESC')
            ->get()
            ->getResultArray();

        return $board;
    }

    /**
     * 삭제 처리 (임시 1 / 완전 2)
     *
     * @throws \RuntimeException 유효하지 않은 삭제 유형 또는 후기 없음
     */
    public function markDeleted(int $id, int $state, string $memo): void
    {
        if (!in_array($state, [self::DELETE_TEMP, self::DELETE_FULL], true)) {
            throw new \RuntimeException('유효하지 않은 삭제 유형입니다.');
        }
        if ($this->find($id) === null) {
            throw new \RuntimeException('후기를 찾을 수 없습니다.');
        }

        $this->update($id, [
            'is_delete'   => $state,
            'delete_memo' => $memo,
            'delete_date' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 삭제 복구 (is_delete → 0)
     *
     * @throws \RuntimeException 후기 없음
     */
    public function restore(int $id): void
    {
        if ($this->find($id) === null) {
            throw new \RuntimeException('후기를 찾을 수 없습니다.');
        }

        $this->update($id, [
            'is_delete'   => self::DELETE_NONE,
            'delete_memo' => null,
            'delete_date' => null,
        ]);
    }
}
