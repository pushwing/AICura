<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToContractOrders extends Migration
{
    public function up(): void
    {
        $fields = [
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'parent_id',
            ],
            'contract_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'title',
            ],
            'contract_agree_date' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'contract_date',
            ],
            // 0 동의 전, 1 동의, 2 취소
            'agree' => [
                'type'    => 'TINYINT',
                'default' => 0,
                'after'   => 'contract_agree_date',
            ],
            // 광고주 계약금액 입금 확인 ID
            'deposit_check_id' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'agree',
            ],
            'main_contract' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'deposit_check_id',
            ],
            'is_delete' => [
                'type'    => 'TINYINT',
                'default' => 0,
                'after'   => 'main_contract',
            ],
            // 차감 병원 아이디 (네트워크 모병원이 자병원 대신 결제)
            'purchase_owner_id' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'is_delete',
            ],
            // 0 일반, 1 네트워크 모병원, 2 네트워크 자병원
            'is_network' => [
                'type'    => 'TINYINT',
                'default' => 0,
                'after'   => 'purchase_owner_id',
            ],
            'hospital_type' => [
                'type'    => 'TINYINT',
                'default' => 1,
                'after'   => 'is_network',
            ],
            'hospital_charge_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'hospital_type',
            ],
            'hospital_charge_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'hospital_charge_name',
            ],
            'hospital_charge_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'hospital_charge_phone',
            ],
            'agency_company_charge_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'agency_company_fee_rate',
            ],
            'agency_company_charge_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'agency_company_charge_name',
            ],
            'agency_company_charge_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'agency_company_charge_phone',
            ],
            'tax_issue_request_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'tax_issue_date',
            ],
            'ads_count' => [
                'type' => 'SMALLINT',
                'null' => true,
                'after' => 'agency_company_charge_email',
            ],
            'ads_count_bonus' => [
                'type' => 'TINYINT',
                'null' => true,
                'after' => 'ads_count',
            ],
            'pay_method' => [
                'type' => 'TINYINT',
                'null' => true,
                'after' => 'ads_count_bonus',
            ],
        ];

        $this->forge->addColumn('contract_orders', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('contract_orders', [
            'title', 'contract_date', 'contract_agree_date', 'agree',
            'deposit_check_id', 'main_contract', 'is_delete',
            'purchase_owner_id', 'is_network', 'hospital_type',
            'hospital_charge_name', 'hospital_charge_phone', 'hospital_charge_email',
            'agency_company_charge_name', 'agency_company_charge_phone', 'agency_company_charge_email',
            'tax_issue_request_date', 'ads_count', 'ads_count_bonus', 'pay_method',
        ]);
    }
}
