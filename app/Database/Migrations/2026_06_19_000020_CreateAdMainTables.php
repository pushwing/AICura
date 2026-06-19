<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ads_main (메인 광고) + ads_main_map (소재 매핑) + ad_recommend_maps (추천)
 *
 * 구조:
 *   ad_mains (병원당 캠페인 단위)
 *     └── ad_main_maps (1:N — 소재(campaign) 버전 히스토리 매핑)
 *             └── campaign (각 버전 데이터)
 */
class CreateAdMainTables extends Migration
{
    public function up(): void
    {
        // ── ad_mains ────────────────────────────────────────────
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'campaign_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'ad_title'    => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => ''],
            'hospital_id' => ['type' => 'INT'],
            'agency_user_id' => ['type' => 'INT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('campaign_id', false, false, 'idx_ad_mains_campaign_id');
        $this->forge->addKey('hospital_id', false, false, 'idx_ad_mains_hospital_id');
        $this->forge->createTable('ad_mains');

        // ── ad_main_maps ─────────────────────────────────────────
        // 소재(campaign) 버전마다 레코드 생성 — 검수 이력 관리 기반
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'ad_main_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'campaign_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            // 1 메인노출, 2 미노출
            'is_main'      => ['type' => 'TINYINT', 'default' => 1],
            // 1 검수완료, 2 미검수
            'is_inspect'   => ['type' => 'TINYINT', 'default' => 2],
            'url'          => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ad_main_id', false, false, 'idx_ad_main_maps_ad_main_id');
        $this->forge->addKey('campaign_id', false, false, 'idx_ad_main_maps_campaign_id');
        $this->forge->createTable('ad_main_maps');

        // ── ad_recommend_maps ────────────────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'campaign_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'admin_user_id' => ['type' => 'INT', 'null' => true],
            'ads_order'   => ['type' => 'INT', 'null' => true],
            'is_delete'   => ['type' => 'TINYINT', 'default' => 0],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('campaign_id', false, false, 'idx_ad_recommend_maps_campaign_id');
        $this->forge->createTable('ad_recommend_maps');
    }

    public function down(): void
    {
        $this->forge->dropTable('ad_recommend_maps', true);
        $this->forge->dropTable('ad_main_maps', true);
        $this->forge->dropTable('ad_mains', true);
    }
}
