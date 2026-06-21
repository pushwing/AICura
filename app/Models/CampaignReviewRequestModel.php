<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignReviewRequestModel extends Model
{
    protected $table      = 'campaign_review_requests';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'campaign_id',
        'request_type',
        'ad_title',
        'ad_type',
        'ad_start_date',
        'ad_end_date',
        'cost_type',
        'general_cost',
        'discount_cost',
        'text_cost',
        'db_cost',
        'category',
        'exposure',
        'contract_id',
        'contract_order_id',
        'region',
        'keyword',
        'deliberation_code',
        'channel',
        't1_image_name',
        't2_image_name',
        'd_image_json',
        'review_status',
        'review_memo',
        'reviewed_by',
        'reviewed_at',
        'created_by',
    ];

    /** 검수 요청에 포함되는 모든 캠페인 콘텐츠 필드 */
    public const CONTENT_FIELDS = [
        'ad_title', 'ad_type', 'ad_start_date', 'ad_end_date',
        'cost_type', 'general_cost', 'discount_cost', 'text_cost', 'db_cost',
        'category', 'exposure', 'contract_id', 'contract_order_id',
        'region', 'keyword', 'deliberation_code', 'channel',
        't1_image_name', 't2_image_name', 'd_image_json',
    ];

    public const REVIEW_TRANSITIONS = [
        'pending'  => ['approved', 'rejected'],
        'approved' => [],
        'rejected' => [],
    ];

    // ── 기록 ──────────────────────────────────────────

    /**
     * 캠페인 검수 요청 기록
     *
     * @param array<string, mixed> $contentData 캠페인 콘텐츠 필드 데이터
     */
    public function record(
        int $campaignId,
        array $contentData,
        ?int $createdBy,
        string $requestType = 'update'
    ): int {
        $payload = ['campaign_id' => $campaignId, 'request_type' => $requestType, 'created_by' => $createdBy];

        foreach (self::CONTENT_FIELDS as $field) {
            $payload[$field] = $contentData[$field] ?? null;
        }

        return (int) $this->insert($payload, true);
    }

    // ── 검수 처리 ─────────────────────────────────────

    /**
     * 검수 승인 — 콘텐츠 필드 배열을 반환 (campaigns 테이블 복사용)
     *
     * @throws \RuntimeException
     * @return array<string, mixed>
     */
    public function approve(int $id, int $reviewedBy, ?string $memo = null): array
    {
        $request = $this->find($id);
        if ($request === null) {
            throw new \RuntimeException('검수 요청을 찾을 수 없습니다.');
        }

        $this->assertTransition($request['review_status'], 'approved');

        $this->update($id, [
            'review_status' => 'approved',
            'review_memo'   => $memo,
            'reviewed_by'   => $reviewedBy,
            'reviewed_at'   => date('Y-m-d H:i:s'),
        ]);

        return array_intersect_key($request, array_flip(self::CONTENT_FIELDS));
    }

    /**
     * 검수 반려
     *
     * @throws \RuntimeException
     */
    public function reject(int $id, int $reviewedBy, ?string $memo = null): void
    {
        $request = $this->find($id);
        if ($request === null) {
            throw new \RuntimeException('검수 요청을 찾을 수 없습니다.');
        }

        $this->assertTransition($request['review_status'], 'rejected');

        $this->update($id, [
            'review_status' => 'rejected',
            'review_memo'   => $memo,
            'reviewed_by'   => $reviewedBy,
            'reviewed_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    // ── 조회 ──────────────────────────────────────────

    /**
     * 캠페인의 최신 대기 요청 (없으면 null)
     *
     * @return array<string, mixed>|null
     */
    public function getLatestPending(int $campaignId): ?array
    {
        return $this->where('campaign_id', $campaignId)
            ->where('review_status', 'pending')
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * 캠페인에 아직 처리되지 않은(pending) 검수 요청이 남아있는지 여부.
     *
     * 승인/반려는 개별 요청 단위지만 campaigns.review_status 는 캠페인 단위 캐시이므로,
     * 한 건을 처리한 뒤 다른 pending 요청이 남아있으면 캐시를 pending 으로 유지해야 한다.
     */
    public function hasPending(int $campaignId): bool
    {
        return $this->where('campaign_id', $campaignId)
            ->where('review_status', 'pending')
            ->countAllResults() > 0;
    }

    /**
     * 캠페인의 최신 요청 (승인 여부 무관) — 어드민 폼 pre-populate용
     *
     * @return array<string, mixed>|null
     */
    public function getLatest(int $campaignId): ?array
    {
        return $this->where('campaign_id', $campaignId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * 검수 상세 (캠페인 + 병원 + 처리자 정보 포함)
     *
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table('campaign_review_requests crr')
            ->select('crr.*')
            ->select('c.ad_title AS approved_title, c.ad_type AS approved_ad_type')
            ->select('c.ad_start_date AS approved_start, c.ad_end_date AS approved_end')
            ->select('c.cost_type AS approved_cost_type, c.general_cost AS approved_general_cost')
            ->select('c.discount_cost AS approved_discount_cost, c.text_cost AS approved_text_cost')
            ->select('c.db_cost AS approved_db_cost, c.category AS approved_category')
            ->select('c.exposure AS approved_exposure, c.region AS approved_region')
            ->select('c.keyword AS approved_keyword, c.deliberation_code AS approved_deliberation_code')
            ->select('c.channel AS approved_channel, c.status AS campaign_status')
            ->select('c.t1_image_name AS approved_t1, c.t2_image_name AS approved_t2, c.d_image_json AS approved_d_json')
            ->select('h.name AS hospital_name')
            ->select('u.name AS created_by_name')
            ->select('r.name AS reviewed_by_name')
            ->join('campaigns c', 'c.id = crr.campaign_id', 'left')
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('users u', 'u.id = crr.created_by', 'left')
            ->join('users r', 'r.id = crr.reviewed_by', 'left')
            ->where('crr.id', $id)
            ->get()
            ->getRowArray() ?: null;
    }

    /**
     * 검수 대기 목록
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getPendingList(array $params = []): array
    {
        $builder = $this->db->table('campaign_review_requests crr')
            ->select('crr.id, crr.campaign_id, crr.request_type, crr.ad_title, crr.review_status, crr.created_at')
            ->select('c.status AS campaign_status, c.ad_title AS approved_title')
            ->select('h.name AS hospital_name')
            ->select('u.name AS created_by_name')
            ->join('campaigns c', 'c.id = crr.campaign_id', 'left')
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('users u', 'u.id = crr.created_by', 'left')
            ->where('crr.review_status', 'pending')
            ->where('c.is_deleted', 0);

        if (!empty($params['keyword'])) {
            $builder->groupStart()
                ->like('crr.ad_title', $params['keyword'])
                ->orLike('c.ad_title', $params['keyword'])
                ->orLike('h.name', $params['keyword'])
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('crr.id', 'ASC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    // ── private ───────────────────────────────────────

    /**
     * @throws \RuntimeException
     */
    private function assertTransition(string $from, string $to): void
    {
        $allowed = self::REVIEW_TRANSITIONS[$from] ?? [];
        if (!in_array($to, $allowed, true)) {
            throw new \RuntimeException(
                sprintf('"%s" 상태에서 "%s"로 변경할 수 없습니다.', $from, $to)
            );
        }
    }
}
