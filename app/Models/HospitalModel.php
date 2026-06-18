<?php

namespace App\Models;

use CodeIgniter\Model;

class HospitalModel extends Model
{
    protected $table      = 'hospitals';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'name',
        'type',
        'phone',
        'address',
        'status',
        'is_deleted',
    ];

    /** @var array<string, string> */
    protected $validationRules = [
        'name'   => 'required|max_length[255]',
        'type'   => 'required|in_list[1,2,3]',
        'status' => 'in_list[active,inactive]',
    ];

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('hospitals')
            ->select('id, name, type, phone, status, created_at')
            ->where('is_deleted', 0);

        if (!empty($params['name'])) {
            $builder->like('name', $params['name']);
        }
        if (!empty($params['status'])) {
            $builder->where('status', $params['status']);
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

    /** @return array<int, array<string, mixed>> */
    public function getActiveList(): array
    {
        return $this->select('id, name, type')
            ->where('is_deleted', 0)
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
