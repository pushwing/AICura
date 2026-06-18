<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignHistoryModel extends Model
{
    protected $table      = 'campaign_histories';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'campaign_id',
        'action',
        'status_from',
        'status_to',
        'memo',
        'admin_user_id',
    ];

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getList(int $campaignId, array $params = []): array
    {
        $builder = $this->db->table('campaign_histories ch')
            ->select('ch.*, u.name as admin_name')
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

    public function record(int $campaignId, string $action, string $statusFrom, string $statusTo, ?int $adminUserId, ?string $memo = null): void
    {
        $this->insert([
            'campaign_id'   => $campaignId,
            'action'        => $action,
            'status_from'   => $statusFrom,
            'status_to'     => $statusTo,
            'memo'          => $memo,
            'admin_user_id' => $adminUserId,
        ]);
    }
}
