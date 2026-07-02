<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRefundsAndBookingsTables extends Migration
{
    public function up(): void
    {
        // ── refunds (환불 결과) ───────────────────────────────────
        $this->forge->addField([
            'id'               => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'          => ['type' => 'INT'],
            'payment_id'       => ['type' => 'INT'],
            'trans_no'         => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
            // 1 전체, 2 부분
            'term_type'        => ['type' => 'TINYINT', 'default' => 1],
            'contract_id'      => ['type' => 'INT'],
            'contract_order_id' => ['type' => 'INT'],
            // 1 가상계좌, 2 카드
            'type'             => ['type' => 'TINYINT', 'default' => 1],
            'amount'           => ['type' => 'INT'],
            // 1 성공, 2 실패
            'result_code'      => ['type' => 'TINYINT', 'default' => 2],
            'result1'          => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => ''],
            'result2'          => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => ''],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('payment_id', false, false, 'idx_refunds_payment_id');
        $this->forge->addKey('contract_order_id', false, false, 'idx_refunds_contract_order_id');
        $this->forge->createTable('refunds');

        // ── bookings (신청 예약) ──────────────────────────────────
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'INT', 'null' => true],
            'hospital_id'     => ['type' => 'INT', 'null' => true],
            'call_request_id' => ['type' => 'INT', 'null' => true],
            /*
             * 상태: 0 대기, 1 예약, 2 취소, 3 완료
             */
            'status'          => ['type' => 'TINYINT', 'default' => 0],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'phone'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'book_date'       => ['type' => 'DATETIME', 'null' => true],
            'confirm_date'    => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('hospital_id', false, false, 'idx_bookings_hospital_id');
        $this->forge->addKey('user_id', false, false, 'idx_bookings_user_id');
        $this->forge->addKey('call_request_id', false, false, 'idx_bookings_call_request_id');
        $this->forge->createTable('bookings');
    }

    public function down(): void
    {
        $this->forge->dropTable('bookings', true);
        $this->forge->dropTable('refunds', true);
    }
}
