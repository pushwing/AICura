<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * call_requests (이벤트 신청 DB) + call_memos (신청 메모)
 *
 * CPA 과금의 핵심 테이블. 앱/웹에서 이벤트 신청 시 생성.
 */
class CreateCallRequestTables extends Migration
{
    public function up(): void
    {
        // ── call_requests ────────────────────────────────────────
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'hospital_id'  => ['type' => 'INT'],
            'campaign_id'  => ['type' => 'INT'],
            'user_id'      => ['type' => 'INT', 'null' => true],
            // 디바이스: 1 안드, 2 iOS, 3 웹
            'device'       => ['type' => 'TINYINT', 'null' => true],
            /*
             * 상태:
             *  1 미확인, 2 부재중, 3 취소, 4 기타, 5 예약,
             *  6 예약취소, 7 내원완료, 8 중복, 9 결번
             */
            'status'       => ['type' => 'TINYINT', 'default' => 1],
            'confirm_date' => ['type' => 'DATETIME', 'null' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
            'phone'        => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
            'content'      => ['type' => 'TEXT', 'null' => true],
            'call_time'    => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
            'age'          => ['type' => 'INT', 'null' => true],
            // 성별: 1 남, 2 여
            'sex'          => ['type' => 'TINYINT', 'default' => 0],
            'privacy_agree'  => ['type' => 'TINYINT', 'null' => true],
            'supply_third_party_agree' => ['type' => 'TINYINT', 'null' => true],
            'event_cost'   => ['type' => 'INT', 'default' => 0],
            'funnel'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'region'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'finger_print' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_delete'    => ['type' => 'TINYINT', 'default' => 0],
            'parent_id'    => ['type' => 'INT', 'null' => true],
            'is_save_phone' => ['type' => 'TINYINT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('hospital_id', false, false, 'idx_call_requests_hospital_id');
        $this->forge->addKey('campaign_id', false, false, 'idx_call_requests_campaign_id');
        $this->forge->addKey('user_id', false, false, 'idx_call_requests_user_id');
        $this->forge->addKey('status', false, false, 'idx_call_requests_status');
        $this->forge->addKey('created_at', false, false, 'idx_call_requests_created_at');
        $this->forge->createTable('call_requests');

        // ── call_memos ───────────────────────────────────────────
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'call_request_id' => ['type' => 'INT', 'null' => true],
            'user_id'         => ['type' => 'INT', 'null' => true],
            'memo'            => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('call_request_id', false, false, 'idx_call_memos_call_request_id');
        $this->forge->createTable('call_memos');
    }

    public function down(): void
    {
        $this->forge->dropTable('call_memos', true);
        $this->forge->dropTable('call_requests', true);
    }
}
