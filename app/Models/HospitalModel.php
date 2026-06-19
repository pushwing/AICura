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

    // Fix #7: 병원 목록 변경 시 활성 목록 캐시 무효화
    protected $afterInsert = ['clearActiveListCache'];
    protected $afterUpdate = ['clearActiveListCache'];
    protected $afterDelete = ['clearActiveListCache'];

    /** @var array<string, string> */
    protected $validationRules = [
        'name'   => 'required|max_length[255]',
        'type'   => 'required|in_list[1,2,3]',
        'status' => 'in_list[active,inactive]',
    ];

    /**
     * @param array<string, mixed> $params
     * @return array{list: list<array<string, mixed>>, total: int}
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

        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * Fix #7: 결과를 10분 캐시 — 폼 드롭다운용, 변경 빈도 낮음
     *
     * @return list<array<string, mixed>>
     */
    public function getActiveList(): array
    {
        $cacheKey = 'hospitals_active_list';
        /** @var list<array<string, mixed>>|null $cached */
        $cached = cache($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        /** @var list<array<string, mixed>> $list */
        $list = $this->select('id, name, type')
            ->where('is_deleted', 0)
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();

        cache()->save($cacheKey, $list, 600);

        return $list;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function clearActiveListCache(array $data): array
    {
        cache()->delete('hospitals_active_list');
        return $data;
    }
}
