<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * creative_histories(이미지 전용) 폐기 → campaign_review_requests(모든 필드) 신설
 *
 * campaign_review_requests: 광고주·어드민이 요청하는 모든 캠페인 콘텐츠 변경 사항.
 * campaigns 테이블은 검수 승인된 데이터만 보관 (외부 노출용).
 */
class ReplaceCampaignReviewTables extends Migration
{
    public function up(): void
    {
        // 기존 이미지 전용 히스토리 테이블 제거 (IF EXISTS)
        $this->forge->dropTable('creative_histories', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'campaign_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            // create | update
            'request_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'update',
            ],

            // ── 캠페인 콘텐츠 필드 (검수 대상) ──────────────
            'ad_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            // 1 CPA, 2 CPM, 3 프로모션, 4 CPC, 5 옵션
            'ad_type' => [
                'type' => 'TINYINT',
                'null' => true,
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
                'type' => 'TINYINT',
                'null' => true,
            ],
            'general_cost' => [
                'type' => 'INT',
                'null' => true,
            ],
            'discount_cost' => [
                'type' => 'INT',
                'null' => true,
            ],
            'text_cost' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'db_cost' => [
                'type' => 'INT',
                'null' => true,
            ],
            'category' => [
                'type' => 'INT',
                'null' => true,
            ],
            // 1 이벤트, 2 병원상세, 3 둘다
            'exposure' => [
                'type' => 'TINYINT',
                'null' => true,
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
            // 1 굿닥, 2 굿닥파트너스
            'channel' => [
                'type' => 'TINYINT',
                'null' => true,
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

            // ── 검수 처리 ────────────────────────────────────
            // pending | approved | rejected
            'review_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            'review_memo' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'reviewed_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
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
        $this->forge->addKey('campaign_id', false, false, 'idx_crr_campaign_id');
        $this->forge->addKey('review_status', false, false, 'idx_crr_review_status');

        $this->forge->createTable('campaign_review_requests');
    }

    public function down(): void
    {
        $this->forge->dropTable('campaign_review_requests', true);
    }
}
