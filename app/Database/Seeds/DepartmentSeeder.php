<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 진료과 마스터 코드 + 샘플 병원 매핑 Seeder (이슈 #113)
 *
 * departments(고정 코드)와 department_hospital(샘플 병원 1~3 매핑)을 채운다.
 * 샘플 매핑은 SampleDataSeeder 가 삽입한 병원 id(1~3)에 의존한다.
 */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // 진료과 고정 코드 마스터
        $departments = [
            ['id' => 1, 'code' => 'plastic_surgery',   'name' => '성형외과'],
            ['id' => 2, 'code' => 'dermatology',       'name' => '피부과'],
            ['id' => 3, 'code' => 'dental',            'name' => '치과'],
            ['id' => 4, 'code' => 'ophthalmology',     'name' => '안과'],
            ['id' => 5, 'code' => 'hair_transplant',   'name' => '모발이식'],
            ['id' => 6, 'code' => 'obesity',           'name' => '비만·체형'],
            ['id' => 7, 'code' => 'oriental_medicine', 'name' => '한의원'],
            ['id' => 8, 'code' => 'urology',           'name' => '비뇨기과'],
        ];

        foreach ($departments as $row) {
            $this->db->table('departments')->insert(array_merge($row, [
                'sort'       => $row['id'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // 샘플 병원-진료과 매핑 (한 병원이 여러 진료과를 갖는 다대다 예시)
        $mappings = [
            ['hospital_id' => 1, 'department_id' => 1], // 강남성형외과 → 성형외과
            ['hospital_id' => 1, 'department_id' => 2], // 강남성형외과 → 피부과
            ['hospital_id' => 2, 'department_id' => 4], // 서울네트워크모병원 → 안과
            ['hospital_id' => 2, 'department_id' => 2], // 서울네트워크모병원 → 피부과
            ['hospital_id' => 3, 'department_id' => 3], // 분당자병원 → 치과
            ['hospital_id' => 3, 'department_id' => 5], // 분당자병원 → 모발이식
        ];

        foreach ($mappings as $row) {
            $this->db->table('department_hospital')->insert($row);
        }
    }
}
