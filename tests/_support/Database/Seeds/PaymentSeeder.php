<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // hospitals
        $this->db->table('hospitals')->insert([
            'id'         => 1,
            'name'       => '강남성형외과',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // contracts
        $this->db->table('contracts')->insert([
            'id'            => 1,
            'hospital_id'   => 1,
            'hospital_name' => '강남성형외과',
            'title'         => '2026년 강남성형외과 계약',
            'pay_type'      => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // contract_orders
        $this->db->table('contract_orders')->insert([
            'id'              => 1,
            'hospital_id'     => 1,
            'hospital_name'   => '강남성형외과',
            'contract_type'   => 1,
            'ad_type'         => 1,
            'ad_type2'        => 1,
            'ad_price'        => 1100000,
            'contract_status' => 1, // 정상
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        // contract_order_connects
        $this->db->table('contract_order_connects')->insert([
            'contract_id'       => 1,
            'contract_order_id' => 1,
            'created_at'        => $now,
        ]);

        // payments
        // id=1: 결제완료 (환불 테스트 대상)
        $this->db->table('payments')->insert([
            'id'                => 1,
            'hospital_id'       => 1,
            'contract_id'       => 1,
            'contract_order_id' => 1,
            'type'              => 2, // 신용카드
            'amount'            => 1100000,
            'result_code'       => '0021',
            'trans_no'          => 'TRANS001',
            'auth_date'         => '20260618120000',
            'status'            => 'paid',
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // id=2: 이미 환불된 결제
        $this->db->table('payments')->insert([
            'id'                => 2,
            'hospital_id'       => 1,
            'contract_id'       => 1,
            'contract_order_id' => 1,
            'type'              => 1, // 가상계좌
            'amount'            => 550000,
            'result_code'       => '0021',
            'trans_no'          => 'TRANS002',
            'status'            => 'refunded',
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // id=3: 입금대기
        $this->db->table('payments')->insert([
            'id'                => 3,
            'hospital_id'       => 1,
            'contract_id'       => 1,
            'contract_order_id' => 1,
            'type'              => 1, // 가상계좌
            'amount'            => 220000,
            'result_code'       => '0051',
            'trans_no'          => 'TRANS003',
            'vbank_no'          => '110-123-456789',
            'vbank_expire'      => '20260619235959',
            'status'            => 'pending',
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
    }
}
