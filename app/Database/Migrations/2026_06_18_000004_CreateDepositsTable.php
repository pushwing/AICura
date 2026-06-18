<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepositsTable extends Migration
{
    public function up(): void
    {
        /*
         * status 의미:
         *  2  계약충전
         *  3  DB소진
         *  4  기타+(관리자 수동 충전)
         *  5  기타-(관리자 수동 차감)
         *  6  환불금액(발행환불)
         *  7  환불금액(계약환불)
         *  8  기타-소진
         *  9  취소금액(발행취소)
         * 10  취소금액(계약취소)
         * 11  이월소진 (재계약 시 구 계약에서 차감)
         * 12  이월충전 (재계약 시 신 계약에 이전 잔액 이월)
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'contract_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'contract_order_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'status' => [
                'type'    => 'TINYINT',
                'default' => 2,
            ],
            // 0 양수, 1 음수
            'is_minus' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],
            'price' => [
                'type'    => 'BIGINT',
                'default' => 0,
            ],
            'users_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('contract_id');
        $this->forge->addKey('contract_order_id');
        $this->forge->addKey('status');

        $this->forge->createTable('deposits');
    }

    public function down(): void
    {
        $this->forge->dropTable('deposits');
    }
}
