<?php

namespace App\Models;

use CodeIgniter\Model;

class AdvertiserModel extends Model
{
    protected $table      = 'advertisers';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'hospital_id',
        'hospital_name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'business_no',
        'is_network',
        'network_parent_id',
        'status',
    ];

    /** @var array<string, string> */
    protected $validationRules = [
        'hospital_id'   => 'required|integer|greater_than[0]',
        'hospital_name' => 'required|max_length[255]',
        'contact_email' => 'permit_empty|valid_email|max_length[255]',
        'contact_phone' => 'permit_empty|max_length[30]',
        'business_no'   => 'permit_empty|max_length[50]',
        'is_network'    => 'required|in_list[0,1,2]',
        'status'        => 'required|in_list[1,2,3]',
    ];

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('advertisers a')
            ->select('a.id, a.hospital_id, a.hospital_name, a.contact_name, a.contact_phone, a.is_network, a.status, a.created_at')
            ->select('pa.hospital_name AS parent_name', false)
            ->join('advertisers pa', 'pa.id = a.network_parent_id', 'left');

        if (!empty($params['hospital_name'])) {
            $builder->like('a.hospital_name', $params['hospital_name']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $builder->where('a.status', (int) $params['status']);
        }
        if (isset($params['is_network']) && $params['is_network'] !== '') {
            $builder->where('a.is_network', (int) $params['is_network']);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('a.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 광고주 상세 + 네트워크 관계 + 계약 목록 + KPI
     *
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        $advertiser = $this->find($id);
        if ($advertiser === null) {
            return null;
        }

        $isNetwork = (int) $advertiser['is_network'];

        if ($isNetwork === 1) {
            $advertiser['children'] = $this->select('id, hospital_name, status')
                ->where('network_parent_id', $id)
                ->findAll();
        }

        if ($isNetwork === 2 && !empty($advertiser['network_parent_id'])) {
            $advertiser['parent'] = $this->select('id, hospital_name, status')
                ->find((int) $advertiser['network_parent_id']);
        }

        $advertiser['contracts'] = $this->db->table('contracts')
            ->select('id, title, pay_type, created_at')
            ->where('hospital_id', (int) $advertiser['hospital_id'])
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $advertiser['kpi'] = $this->getKpi((int) $advertiser['hospital_id']);

        return $advertiser;
    }

    /**
     * @return array<string, mixed>
     */
    private function getKpi(int $hospitalId): array
    {
        $totalRow = $this->db->table('contract_orders co')
            ->select('IFNULL(SUM(co.ad_price), 0) AS total', false)
            ->join('contract_order_connects coc', 'coc.contract_order_id = co.id')
            ->join('contracts c', 'c.id = coc.contract_id')
            ->where('c.hospital_id', $hospitalId)
            ->where('co.contract_status', 1)
            ->get()
            ->getRow();
        $totalAmount = (int) ($totalRow->total ?? 0);

        $balanceRow = $this->db->table('deposits d')
            ->select('IFNULL(SUM(CASE WHEN d.is_minus = 0 THEN d.price ELSE -d.price END), 0) AS balance', false)
            ->join('contracts c', 'c.id = d.contract_id')
            ->where('c.hospital_id', $hospitalId)
            ->get()
            ->getRow();
        $balance = (int) ($balanceRow->balance ?? 0);

        $activeCampaigns = (int) $this->db->table('campaigns')
            ->where('hospital_id', $hospitalId)
            ->where('status', 'active')
            ->where('is_deleted', 0)
            ->countAllResults();

        return [
            'total_amount'     => $totalAmount,
            'balance'          => $balance,
            'active_campaigns' => $activeCampaigns,
        ];
    }
}
