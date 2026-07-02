<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignTempsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // null이면 신규 등록 임시저장, 값이 있으면 기존 캠페인 수정 임시저장
            'campaign_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'ad_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'hospital_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'hospital_type' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
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
            'channel' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            't1_image_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            't2_image_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'd_image_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'admin_user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
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
        $this->forge->addKey('campaign_id', false, false, 'idx_campaign_temps_campaign_id');
        $this->forge->addKey('admin_user_id', false, false, 'idx_campaign_temps_admin_user_id');

        $this->forge->createTable('campaign_temps');
    }

    public function down(): void
    {
        $this->forge->dropTable('campaign_temps');
    }
}
