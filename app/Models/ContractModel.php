<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 메인 계약 모델 (병원당 1개)
 */
class ContractModel extends Model
{
    protected $table      = 'contracts';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'hospital_id',
        'hospital_name',
        'title',
        'pay_type',
    ];

    /** @var array<string, string> */
    protected $validationRules = [
        'hospital_id'   => 'required|integer',
        'hospital_name' => 'required|max_length[255]',
        'title'         => 'required|max_length[255]',
        'pay_type'      => 'required|in_list[1,2]',
    ];

    /**
     * 병원 ID로 메인 계약 조회 (없으면 null)
     *
     * @return array<string, mixed>|null
     */
    public function findByHospital(int $hospitalId): ?array
    {
        return $this->where('hospital_id', $hospitalId)->first();
    }

    /**
     * 계약 + 수주계약 목록 조회
     * contract_orders를 JOIN하여 각 계약의 추가계약건 수, 총 계약금액 집계
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getListWithOrders(array $params): array
    {
        $builder = $this->db->table('contracts c')
            ->select('c.id, c.hospital_id, c.hospital_name, c.title, c.pay_type, c.created_at')
            ->select('COUNT(co.id) AS order_count', false)
            ->select('IFNULL(SUM(co.ad_price), 0) AS total_price', false)
            ->join('contract_order_connects coc', 'coc.contract_id = c.id', 'left')
            ->join('contract_orders co', 'co.id = coc.contract_order_id AND co.contract_status = 1', 'left')
            ->groupBy('c.id');

        if (!empty($params['hospital_name'])) {
            $builder->like('c.hospital_name', $params['hospital_name']);
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
     * 계약 상세 (수주계약 목록 포함)
     *
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        $contract = $this->find($id);
        if ($contract === null) {
            return null;
        }

        $contract['orders'] = $this->db->table('contract_orders co')
            ->select('co.*')
            ->join('contract_order_connects coc', 'coc.contract_order_id = co.id')
            ->where('coc.contract_id', $id)
            ->orderBy('co.id', 'DESC')
            ->get()
            ->getResultArray();

        return $contract;
    }
}
