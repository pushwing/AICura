<?php

namespace App\Models;

use CodeIgniter\Model;

class CreativeHistoryModel extends Model
{
    protected $table      = 'creative_histories';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'campaign_id',
        't1_before',
        't1_after',
        't2_before',
        't2_after',
        'd_images_before',
        'd_images_after',
        'review_status',
        'review_memo',
        'reviewed_by',
        'reviewed_at',
        'created_by',
    ];

    public const REVIEW_TRANSITIONS = [
        'pending'  => ['approved', 'rejected'],
        'approved' => [],
        'rejected' => [],
    ];

    /**
     * 소재 수정 이력 기록
     *
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     */
    public function record(int $campaignId, array $before, array $after, ?int $createdBy): int
    {
        return (int) $this->insert([
            'campaign_id'     => $campaignId,
            't1_before'       => $before['t1_image_name'] ?? null,
            't1_after'        => $after['t1_image_name'] ?? null,
            't2_before'       => $before['t2_image_name'] ?? null,
            't2_after'        => $after['t2_image_name'] ?? null,
            'd_images_before' => $before['d_image_json'] ?? null,
            'd_images_after'  => $after['d_image_json'] ?? null,
            'review_status'   => 'pending',
            'created_by'      => $createdBy,
        ], true);
    }

    /**
     * 검수 처리 (승인/반려)
     *
     * @throws \RuntimeException 허용되지 않는 상태 전이
     */
    public function review(int $id, string $action, ?int $reviewedBy, ?string $memo = null): void
    {
        $history = $this->select('id, review_status')->find($id);
        if ($history === null) {
            throw new \RuntimeException('소재 이력을 찾을 수 없습니다.');
        }

        $nextStatus = match ($action) {
            'approve' => 'approved',
            'reject'  => 'rejected',
            default   => throw new \RuntimeException('알 수 없는 액션입니다.'),
        };

        $allowed = self::REVIEW_TRANSITIONS[$history['review_status']] ?? [];
        if (!in_array($nextStatus, $allowed, true)) {
            throw new \RuntimeException(
                sprintf('"%s" 상태에서 "%s"로 변경할 수 없습니다.', $history['review_status'], $nextStatus)
            );
        }

        $this->update($id, [
            'review_status' => $nextStatus,
            'review_memo'   => $memo,
            'reviewed_by'   => $reviewedBy,
            'reviewed_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 캠페인별 소재 이력 목록
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getListByCampaign(int $campaignId, array $params = []): array
    {
        $builder = $this->db->table('creative_histories ch')
            ->select('ch.*, u.name AS created_by_name, r.name AS reviewed_by_name')
            ->join('users u', 'u.id = ch.created_by', 'left')
            ->join('users r', 'r.id = ch.reviewed_by', 'left')
            ->where('ch.campaign_id', $campaignId);

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('ch.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 검수 대기 목록 (campaigns 조인)
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getPendingList(array $params = []): array
    {
        $builder = $this->db->table('creative_histories ch')
            ->select('ch.id, ch.campaign_id, ch.t1_before, ch.t1_after, ch.t2_before, ch.t2_after')
            ->select('ch.d_images_before, ch.d_images_after, ch.review_status, ch.created_at')
            ->select('c.ad_title, c.status AS campaign_status')
            ->select('h.name AS hospital_name')
            ->select('u.name AS created_by_name')
            ->join('campaigns c', 'c.id = ch.campaign_id', 'left')
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('users u', 'u.id = ch.created_by', 'left')
            ->where('ch.review_status', 'pending')
            ->where('c.is_deleted', 0);

        if (!empty($params['keyword'])) {
            $builder->groupStart()
                ->like('c.ad_title', $params['keyword'])
                ->orLike('h.name', $params['keyword'])
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('ch.id', 'ASC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 검수 상세 (캠페인 + 병원 정보 포함)
     *
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table('creative_histories ch')
            ->select('ch.*')
            ->select('c.ad_title, c.status AS campaign_status, c.ad_type, c.ad_start_date, c.ad_end_date, c.review_status AS campaign_review_status')
            ->select('h.name AS hospital_name')
            ->select('u.name AS created_by_name')
            ->select('r.name AS reviewed_by_name')
            ->join('campaigns c', 'c.id = ch.campaign_id', 'left')
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('users u', 'u.id = ch.created_by', 'left')
            ->join('users r', 'r.id = ch.reviewed_by', 'left')
            ->where('ch.id', $id)
            ->get()
            ->getRowArray() ?: null;
    }
}
