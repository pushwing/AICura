<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignModel extends Model
{
    protected $table      = 'campaigns';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'ad_title',
        'hospital_id',
        'hospital_type',
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
        'status',
        'channel',
        'is_deleted',
        't1_image_name',
        't2_image_name',
        'd_image_json',
    ];

    /** @var array<string, string> */
    protected $validationRules = [
        'ad_title'    => 'required|max_length[255]',
        'hospital_id' => 'required|integer',
        'ad_type'     => 'required|in_list[1,2,3,4,5]',
        'status'      => 'in_list[pending,active,rejected,ended]',
    ];

    // 상태 전이 허용 맵: 현재 상태 → 가능한 다음 상태
    public const STATUS_TRANSITIONS = [
        'pending'  => ['active', 'rejected'],
        'active'   => ['ended'],
        'rejected' => ['pending'],
        'ended'    => [],
    ];

    public const AD_TYPES = [
        1 => 'CPA',
        2 => 'CPM',
        3 => '프로모션',
        4 => 'CPC',
        5 => '옵션',
    ];

    public const CHANNELS = [
        1 => '굿닥',
        2 => '굿닥파트너스',
    ];

    /**
     * 캠페인 목록 (페이징, 검색, 상태 필터)
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getCampaignList(array $params): array
    {
        $builder = $this->db->table('campaigns c')
            ->select('c.id, c.ad_title, c.ad_type, c.status, c.channel, c.ad_start_date, c.ad_end_date, c.db_cost, c.created_at')
            ->select('h.name as hospital_name', false)
            ->select('co.title as contract_name', false)
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('contracts co', 'co.id = c.contract_id', 'left')
            ->where('c.is_deleted', 0);

        if (!empty($params['status'])) {
            $builder->where('c.status', $params['status']);
        }
        if (!empty($params['ad_type'])) {
            $builder->where('c.ad_type', (int) $params['ad_type']);
        }
        if (!empty($params['channel'])) {
            $builder->where('c.channel', (int) $params['channel']);
        }
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
            ->orderBy('c.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 캠페인 상세 (JOIN: hospital, contract, packages)
     *
     * @return array<string, mixed>|null
     */
    public function getCampaignDetail(int $id): ?array
    {
        $row = $this->db->table('campaigns c')
            ->select('c.*')
            ->select('h.name as hospital_name, h.type as hospital_type_label', false)
            ->select('co.title as contract_title', false)
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('contracts co', 'co.id = c.contract_id', 'left')
            ->where('c.id', $id)
            ->where('c.is_deleted', 0)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        // 연결된 패키지 목록
        $row['packages'] = $this->db->table('campaign_packages cp')
            ->select('cp.id, cp.title, cp.status, cp.start_date, cp.end_date')
            ->join('campaign_package_map cpm', 'cpm.campaign_package_id = cp.id')
            ->where('cpm.campaign_id', $id)
            ->where('cp.is_deleted', 0)
            ->orderBy('cpm.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        return $row;
    }

    /**
     * 상태 변경 (상태 전이 검증 포함)
     *
     * @throws \RuntimeException 허용되지 않는 상태 전이
     */
    public function updateStatus(int $id, string $action): string
    {
        $campaign = $this->select('id, status')->find($id);
        if ($campaign === null) {
            throw new \RuntimeException('캠페인을 찾을 수 없습니다.');
        }

        $currentStatus = $campaign['status'];
        $nextStatus    = match ($action) {
            'approve' => 'active',
            'reject'  => 'rejected',
            'end'     => 'ended',
            'reopen'  => 'pending',
            default   => throw new \RuntimeException('알 수 없는 액션입니다.'),
        };

        $allowed = self::STATUS_TRANSITIONS[$currentStatus] ?? [];
        if (!in_array($nextStatus, $allowed, true)) {
            throw new \RuntimeException(
                sprintf('"%s" 상태에서 "%s"로 변경할 수 없습니다.', $currentStatus, $nextStatus)
            );
        }

        $this->update($id, ['status' => $nextStatus]);

        return $nextStatus;
    }

    /**
     * 히스토리 목록 (campaign_histories 테이블)
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getHistoryList(int $campaignId, array $params = []): array
    {
        $builder = $this->db->table('campaign_histories ch')
            ->select('ch.id, ch.action, ch.status_from, ch.status_to, ch.memo, ch.created_at')
            ->select('u.name as admin_name', false)
            ->join('users u', 'u.id = ch.admin_user_id', 'left')
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
     * 연결된 패키지 목록
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPackageList(int $campaignId): array
    {
        return $this->db->table('campaign_packages cp')
            ->select('cp.id, cp.title, cp.banner_view_type, cp.view_type, cp.start_date, cp.end_date, cp.status, cpm.sort_order')
            ->join('campaign_package_map cpm', 'cpm.campaign_package_id = cp.id')
            ->where('cpm.campaign_id', $campaignId)
            ->where('cp.is_deleted', 0)
            ->orderBy('cpm.sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }
}
