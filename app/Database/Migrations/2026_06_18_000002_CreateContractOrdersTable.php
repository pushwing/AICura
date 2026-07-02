<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContractOrdersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'hospital_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'hospital_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            // 1 신규, 2 재계약
            'contract_type' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            // 계약방식: 1 이벤트신청(CPA), 2 기간노출(CPM)
            'ad_type' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            // 상품종류: 1 이벤트, 2 메인배너, 3 이벤트존메인배너, 4 CPM, 5 기타
            'ad_type2' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'ad_price' => [
                'type'    => 'BIGINT',
                'default' => 0,
            ],
            // 계약상태: 1 정상, 2 발행환불, 3 발행취소, 4 계약취소, 5 계약환불, 6 이월종료
            'contract_status' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'deposit_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            // 재계약 시 이전 contract_order id
            'parent_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            // 담당 영업 운영자 (admin user)
            'agency_user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'manage_user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            // 세금계산서 정보
            'tax_charge_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tax_charge_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'tax_business_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tax_issue_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'agency_company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'agency_company_fee_rate' => [
                'type'    => 'DECIMAL',
                'constraint' => '5,2',
                'null'    => true,
            ],
            'memo' => [
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
        $this->forge->addKey('hospital_id');
        $this->forge->addKey('contract_status');
        $this->forge->addKey('parent_id');

        $this->forge->createTable('contract_orders');
    }

    public function down(): void
    {
        $this->forge->dropTable('contract_orders');
    }
}
