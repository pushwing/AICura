<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 진료과 코드 마스터 모델 (이슈 #113)
 *
 * departments(고정 코드) + department_hospital(다대다 피벗) 접근을 담당한다.
 */
class DepartmentModel extends Model
{
    protected $table         = 'departments';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'code',
        'name',
        'sort',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $validationRules = [
        'code' => 'required|max_length[30]',
        'name' => 'required|max_length[100]',
    ];

    /**
     * 병원 ID 묶음 → 병원별 진료과 목록 (N+1 방지용 배치 조회).
     *
     * @param list<int> $hospitalIds
     * @return array<int, list<array{id: int, code: string, name: string}>> hospital_id => 진료과 목록
     */
    public function byHospitalIds(array $hospitalIds): array
    {
        if ($hospitalIds === []) {
            return [];
        }

        $rows = $this->db->table('department_hospital dh')
            ->select('dh.hospital_id, d.id, d.code, d.name')
            ->join('departments d', 'd.id = dh.department_id', 'inner')
            ->whereIn('dh.hospital_id', $hospitalIds)
            ->where('d.is_active', 1)
            ->orderBy('d.sort', 'ASC')
            ->orderBy('d.id', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['hospital_id']][] = [
                'id'   => (int) $row['id'],
                'code' => (string) $row['code'],
                'name' => (string) $row['name'],
            ];
        }

        return $map;
    }
}
