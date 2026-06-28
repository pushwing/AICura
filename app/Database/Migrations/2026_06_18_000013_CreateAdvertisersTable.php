<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * advertisers 테이블 마이그레이션
 *
 * is_network 분류:
 *   0  일반 병원
 *   1  네트워크 모병원
 *   2  네트워크 자병원 (network_parent_id → 모병원 advertisers.id)
 *
 * status:
 *   1  활성
 *   2  정지
 *   3  탈퇴
 */
class CreateAdvertisersTable extends Migration
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
            'hospital_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'contact_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'business_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'is_network' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],
            'network_parent_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'status' => [
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
        $this->forge->addUniqueKey('hospital_id', 'uniq_advertisers_hospital_id');
        $this->forge->addKey('status', false, false, 'idx_advertisers_status');
        $this->forge->addKey('is_network', false, false, 'idx_advertisers_is_network');
        $this->forge->addKey('network_parent_id', false, false, 'idx_advertisers_network_parent_id');
        // Fix #3: DB 레벨 참조 무결성 — hospitals 테이블 선행 생성 (000008) 필수
        $this->forge->addForeignKey('hospital_id', 'hospitals', 'id', 'RESTRICT', 'RESTRICT');

        $this->forge->createTable('advertisers');
    }

    public function down(): void
    {
        $isMySQLi = $this->db instanceof \CodeIgniter\Database\MySQLi\Connection;
        if ($isMySQLi) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        }
        $this->forge->dropTable('advertisers');
        if ($isMySQLi) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
