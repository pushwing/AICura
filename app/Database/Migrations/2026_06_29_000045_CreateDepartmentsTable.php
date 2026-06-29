<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * departments — 진료과 코드 마스터 (이슈 #113)
 *
 * 병원 진료과 필터(filter[department])의 기준이 되는 고정 코드 집합.
 * 실제 병원-진료과 연결은 department_hospital 피벗으로 다대다 매핑한다.
 */
class CreateDepartmentsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // 앱·필터에서 사용하는 안정적 식별 코드 (예: plastic_surgery)
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            // 표시명 (예: 성형외과)
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'sort' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('code', false, true, 'uniq_departments_code');

        $this->forge->createTable('departments');
    }

    public function down(): void
    {
        $this->forge->dropTable('departments');
    }
}
