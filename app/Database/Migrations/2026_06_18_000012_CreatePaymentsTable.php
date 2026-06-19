<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
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
                'null'     => true,
            ],
            'hospital_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'contract_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'contract_order_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            // 1 가상계좌, 2 신용카드
            'type' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'amount' => [
                'type'    => 'BIGINT',
                'default' => 0,
            ],
            // 세틀뱅크 결과코드 (0021 성공, 0031 실패, 0051 입금대기)
            'result_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'trans_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'auth_date' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'auth_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'fn_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'vbank_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'vbank_expire' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            // pending / paid / refunded / failed
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
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
        $this->forge->addKey('hospital_id', false, false, 'idx_payments_hospital_id');
        $this->forge->addKey('contract_order_id', false, false, 'idx_payments_contract_order_id');
        $this->forge->addKey('status', false, false, 'idx_payments_status');

        $this->forge->createTable('payments');
    }

    public function down(): void
    {
        $this->forge->dropTable('payments');
    }
}
