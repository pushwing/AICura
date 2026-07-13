<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignTempModel extends Model
{
    protected $table         = 'campaign_temps';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'campaign_id',
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
        'channel',
        't1_image_name',
        't2_image_name',
        'd_image_json',
        'admin_user_id',
        'is_deleted',
    ];

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('campaign_temps ct')
            ->select('ct.id, ct.campaign_id, ct.ad_title, ct.ad_type, ct.channel, ct.admin_user_id, ct.created_at')
            ->select('h.name as hospital_name', false)
            ->join('hospitals h', 'h.id = ct.hospital_id', 'left')
            ->where('ct.is_deleted', 0);

        if (! empty($params['admin_user_id'])) {
            $builder->where('ct.admin_user_id', (int) $params['admin_user_id']);
        }
        if (! empty($params['keyword'])) {
            $builder->like('ct.ad_title', $params['keyword']);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('ct.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        $row = $this->db->table('campaign_temps ct')
            ->select('ct.*')
            ->select('h.name as hospital_name', false)
            ->join('hospitals h', 'h.id = ct.hospital_id', 'left')
            ->where('ct.id', $id)
            ->where('ct.is_deleted', 0)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function softDelete(int $id): void
    {
        $this->update($id, ['is_deleted' => 1]);
    }
}
