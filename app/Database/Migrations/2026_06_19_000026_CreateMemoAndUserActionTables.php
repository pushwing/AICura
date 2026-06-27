<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * memos + user_actions
 *
 * memos: 영업팀/세금계산서/원장 메모 통합 테이블
 * user_actions: 앱 사용자 포인트/액션 요약
 */
class CreateMemoAndUserActionTables extends Migration
{
    public function up(): void
    {
        // ── memos ────────────────────────────────────────────────
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            /*
             * 1 영업팀 메모, 2 세금계산서 메모,
             * 3 원장메모(운영자), 4 원장메모(광고주)
             */
            'memo_type'     => ['type' => 'TINYINT', 'default' => 1],
            // memoType=1,2 → contract_order_id
            'target_id'     => ['type' => 'INT', 'null' => true],
            // memoType=3 → 원장번호
            'target_id2'    => ['type' => 'INT', 'null' => true],
            'user_id'       => ['type' => 'INT', 'null' => true],
            'memo'          => ['type' => 'TEXT', 'null' => true],
            'customer_memo' => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['memo_type', 'target_id'], false, false, 'idx_memos_type_target');
        $this->forge->createTable('memos');

        // ── user_actions (사용자 포인트/액션 요약) ───────────────
        $this->forge->addField([
            'id'        => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'   => ['type' => 'INT'],
            'sum_point' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id', false, false, 'idx_user_actions_user_id');
        $this->forge->createTable('user_actions');
    }

    public function down(): void
    {
        $this->forge->dropTable('user_actions', true);
        $this->forge->dropTable('memos', true);
    }
}
