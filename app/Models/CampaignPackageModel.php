<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignPackageModel extends Model
{
    protected $table         = 'campaign_packages';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'title',
        'banner_view_type',
        'view_type',
        'start_date',
        'end_date',
        'banner',
        'detail_info',
        'status',
        'is_deleted',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'title'  => 'required|max_length[255]',
        'status' => 'in_list[active,inactive,ended]',
    ];

    /**
     * 캠페인에 연결된 패키지 목록
     *
     * @return array<int, array<string, mixed>>
     */
    public function getListByCampaign(int $campaignId): array
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

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('campaign_packages')
            ->select('id, title, banner_view_type, view_type, start_date, end_date, status, created_at')
            ->where('is_deleted', 0);

        if (! empty($params['status'])) {
            $builder->where('status', $params['status']);
        }
        if (! empty($params['keyword'])) {
            $builder->like('title', $params['keyword']);
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

    public function attachToCampaign(int $packageId, int $campaignId, int $sortOrder = 0): void
    {
        $exists = $this->db->table('campaign_package_map')
            ->where('campaign_package_id', $packageId)
            ->where('campaign_id', $campaignId)
            ->countAllResults();

        if ($exists === 0) {
            $this->db->table('campaign_package_map')->insert([
                'campaign_package_id' => $packageId,
                'campaign_id'         => $campaignId,
                'sort_order'          => $sortOrder,
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
