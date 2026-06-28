<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * health_point_logs 테이블 — 헬스포인트 적립/차감 내역 (이슈 #97)
 *
 * 잔액은 users.health_point 가 보유하고, 본 테이블은 변동 내역(ledger)을 남긴다.
 *
 *   amount        : 변동량 (적립 +, 차감 -)
 *   balance_after : 변동 후 잔액 스냅샷
 *   type          : 변동 사유 코드 (예: signup, review, redeem)
 */
class CreateHealthPointLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'amount' => [
                'type' => 'INT',
            ],
            'balance_after' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => '',
            ],
            'memo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'id'], false, false, 'idx_health_point_logs_user');

        $this->forge->createTable('health_point_logs');
    }

    public function down(): void
    {
        $this->forge->dropTable('health_point_logs', true);
    }
}
