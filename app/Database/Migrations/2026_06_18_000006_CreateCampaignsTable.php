<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ad_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'hospital_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            // 1 일반, 2 네트워크모, 3 네트워크자
            'hospital_type' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            // 1 CPA, 2 CPM, 3 프로모션, 4 CPC, 5 옵션
            'ad_type' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'ad_start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'ad_end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            // 1 숫자, 2 텍스트
            'cost_type' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'general_cost' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'discount_cost' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'text_cost' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'db_cost' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'category' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            // 1 이벤트, 2 병원상세, 3 둘다
            'exposure' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'contract_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'contract_order_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'region' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'keyword' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'deliberation_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            // pending / active / rejected / ended
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            // 1 굿닥, 2 굿닥파트너스
            'channel' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'is_deleted' => [
                'type'    => 'TINYINT',
                'default' => 0,
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
        $this->forge->addKey('hospital_id', false, false, 'idx_campaigns_hospital_id');
        $this->forge->addKey('ad_type', false, false, 'idx_campaigns_ad_type');
        // is_deleted + status 복합 인덱스 — 카운트 쿼리 커버링
        $this->forge->addKey(['is_deleted', 'status'], false, false, 'idx_campaigns_is_deleted_status');

        $this->forge->createTable('campaigns');
    }

    public function down(): void
    {
        $this->forge->dropTable('campaigns');
    }
}
