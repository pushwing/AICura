<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * department_hospital — 병원-진료과 다대다 매핑 피벗 (이슈 #113)
 *
 * 한 병원이 여러 진료과(예: 성형외과 + 피부과)를 가질 수 있어 피벗으로 표현한다.
 * 명명 규칙: 두 테이블 알파벳순 · 단수 (department_hospital).
 * FK 제약은 기존 테이블(hospitals·campaigns) 관례에 맞춰 인덱스로만 보장한다.
 */
class CreateDepartmentHospitalTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'hospital_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'department_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        // 동일 병원-진료과 중복 매핑 방지
        $this->forge->addKey(['hospital_id', 'department_id'], false, true, 'uniq_department_hospital_pair');
        // 진료과 기준 필터 조인용
        $this->forge->addKey('department_id', false, false, 'idx_department_hospital_department_id');

        $this->forge->createTable('department_hospital');
    }

    public function down(): void
    {
        $this->forge->dropTable('department_hospital');
    }
}
