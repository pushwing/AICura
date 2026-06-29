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
     * Admin 관리용 전체 목록 (비활성 포함, 정렬순).
     *
     * @return list<array<string, mixed>>
     */
    public function allForAdmin(): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->orderBy('sort', 'ASC')->orderBy('id', 'ASC')->findAll();

        return $rows;
    }

    /**
     * 특정 병원에 매핑된 진료과 ID 목록 (수정 폼 체크 상태용).
     *
     * @return list<int>
     */
    public function idsByHospital(int $hospitalId): array
    {
        $rows = $this->db->table('department_hospital')
            ->select('department_id')
            ->where('hospital_id', $hospitalId)
            ->get()
            ->getResultArray();

        return array_map(static fn (array $r): int => (int) $r['department_id'], $rows);
    }

    /**
     * 병원-진료과 매핑 재동기화 — 기존 매핑을 모두 지우고 전달된 진료과로 다시 설정한다.
     *
     * @param list<int> $departmentIds
     */
    public function syncForHospital(int $hospitalId, array $departmentIds): void
    {
        $pivot = $this->db->table('department_hospital');
        $pivot->where('hospital_id', $hospitalId)->delete();

        // 중복·0 제거 후 일괄 삽입
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $departmentIds),
            static fn (int $id): bool => $id > 0
        )));

        if ($ids === []) {
            return;
        }

        $rows = array_map(
            static fn (int $id): array => ['hospital_id' => $hospitalId, 'department_id' => $id],
            $ids
        );
        $pivot->insertBatch($rows);
    }

    /**
     * 진료과별 매핑된 병원 수 (목록 표시용, N+1 방지).
     *
     * @return array<int, int> department_id => 병원 수
     */
    public function hospitalCounts(): array
    {
        $rows = $this->db->table('department_hospital')
            ->select('department_id, COUNT(*) AS cnt', false)
            ->groupBy('department_id')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['department_id']] = (int) $row['cnt'];
        }

        return $map;
    }

    /**
     * 해당 진료과가 병원에 매핑되어 있는지 — 삭제 가드용.
     */
    public function hasHospitalMappings(int $departmentId): bool
    {
        return $this->db->table('department_hospital')
            ->where('department_id', $departmentId)
            ->countAllResults() > 0;
    }

    /**
     * 진료과 코드 중복 여부 — 수정 시 자기 자신($ignoreId)은 제외.
     */
    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        $builder = $this->where('code', $code);
        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

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
