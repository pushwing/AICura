<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignPackagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            // 1 단일, 2 복합
            'banner_view_type' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            // 1 일반, 2 프리미엄
            'view_type' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'banner' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'detail_info' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // active / inactive / ended
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
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
        $this->forge->addKey(['is_deleted', 'status'], false, false, 'idx_campaign_packages_is_deleted_status');

        $this->forge->createTable('campaign_packages');

        // 캠페인-패키지 매핑 테이블
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'campaign_package_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'campaign_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'sort_order' => [
                'type'    => 'INT',
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
        $this->forge->addKey('campaign_package_id', false, false, 'idx_campaign_package_map_pkg');
        $this->forge->addKey('campaign_id', false, false, 'idx_campaign_package_map_campaign');

        $this->forge->createTable('campaign_package_map');
    }

    public function down(): void
    {
        $this->forge->dropTable('campaign_package_map');
        $this->forge->dropTable('campaign_packages');
    }
}
