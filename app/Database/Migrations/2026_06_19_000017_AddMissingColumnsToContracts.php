<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToContracts extends Migration
{
    public function up(): void
    {
        $fields = [
            'contract_date' => [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'title',
            ],
            // 이벤트신청(CPA) / 기간노출(CPM)
            'ad_type' => [
                'type'  => 'TINYINT',
                'null'  => true,
                'after' => 'contract_date',
            ],
            // 1 이벤트, 2 메인배너
            'ad_type2' => [
                'type'  => 'TINYINT',
                'null'  => true,
                'after' => 'ad_type',
            ],
            'main_contract' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'ad_type2',
            ],
            // 1 진행, 2 종료
            'use_status' => [
                'type'    => 'TINYINT',
                'default' => 1,
                'after'   => 'main_contract',
            ],
            'is_deleted' => [
                'type'    => 'TINYINT',
                'default' => 0,
                'after'   => 'use_status',
            ],
            // term_list FK (사용안할 수 있으나 ERD 보존)
            'term_list_id' => [
                'type'  => 'INT',
                'null'  => true,
                'after' => 'is_deleted',
            ],
            'channel_id' => [
                'type'  => 'INT',
                'null'  => true,
                'after' => 'term_list_id',
            ],
            'agency_user_id' => [
                'type'  => 'INT',
                'null'  => true,
                'after' => 'channel_id',
            ],
            'manage_user_id' => [
                'type'  => 'INT',
                'null'  => true,
                'after' => 'agency_user_id',
            ],
            'agency_company_id' => [
                'type'  => 'INT',
                'null'  => true,
                'after' => 'manage_user_id',
            ],
            'agency_company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'agency_company_id',
            ],
            // 1 병원, 2 약국, 3 업체
            'hospital_type' => [
                'type'    => 'TINYINT',
                'default' => 1,
                'after'   => 'agency_company_name',
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
            'tax_charge_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'hospital_charge_email',
            ],
            'tax_business_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'tax_charge_name',
            ],
            'tax_charge_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'tax_business_no',
            ],
        ];

        $this->forge->addColumn('contracts', $fields);

        $this->db->query('CREATE INDEX idx_contracts_agency_user ON contracts (agency_user_id)');
        $this->db->query('CREATE INDEX idx_contracts_hospital_id ON contracts (hospital_id)');
    }

    public function down(): void
    {
        $this->forge->dropColumn('contracts', [
            'contract_date', 'ad_type', 'ad_type2', 'main_contract',
            'use_status', 'is_deleted', 'term_list_id', 'channel_id',
            'agency_user_id', 'manage_user_id', 'agency_company_id',
            'agency_company_name', 'hospital_type', 'hospital_charge_name',
            'hospital_charge_phone', 'hospital_charge_email',
            'tax_charge_name', 'tax_business_no', 'tax_charge_email',
        ]);
    }
}
