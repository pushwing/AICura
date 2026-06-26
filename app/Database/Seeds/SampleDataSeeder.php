<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 개발용 샘플 데이터 Seeder (이슈 #18)
 *
 * 삽입 순서: hospitals → advertisers → contracts → contract_orders
 *          → contract_order_connects → deposits → campaigns
 */
class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->insertHospitals($now);
        $links = $this->insertUsers($now);
        $this->insertAdvertisers($now, $links);
        $this->insertContractsAndOrders($now);
        $this->insertCampaigns($now);
    }

    /**
     * 광고주(병원 유형) 로그인 계정 + 대행사 계정 생성
     *
     * advertisers.owner_user_id(광고주 본인 계정) / agency_user_id(대행사) 연결에 사용한다.
     * 사용자 관리 화면의 '광고주/병원' 탭·'대행사' 탭에 데이터가 표시되도록 한다.
     *
     * @return array{owners: array<int, int>, agency: int}  owners: hospital_id → user_id
     */
    private function insertUsers(string $now): array
    {
        $password = password_hash('password1234', PASSWORD_DEFAULT);

        // 대행사 계정 1건
        $this->db->table('users')->insert([
            'email'             => 'agency@aicura.test',
            'password'          => $password,
            'username'          => '에이스광고대행',
            'user_type'         => 1,
            'is_agency_account' => 1,
            'phone'             => '010-7000-0001',
            'is_dormant'        => 1,
            'is_active'         => 1,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        $agencyId = (int) $this->db->insertID();

        // 광고주병원(user_type=201) 계정 — advertisers.hospital_id 와 매핑
        $owners = [
            1 => ['email' => 'gangnam@aicura.test',  'username' => '강남성형외과 담당',    'phone' => '010-1234-5678'],
            2 => ['email' => 'network@aicura.test',  'username' => '서울네트워크 담당',    'phone' => '010-9999-0000'],
            3 => ['email' => 'bundang@aicura.test',  'username' => '분당자병원 담당',      'phone' => '010-1111-2222'],
        ];

        $ownerIds = [];
        foreach ($owners as $hospitalId => $info) {
            $this->db->table('users')->insert([
                'email'             => $info['email'],
                'password'          => $password,
                'username'          => $info['username'],
                'user_type'         => 201, // 광고주병원
                'is_agency_account' => 0,
                'phone'             => $info['phone'],
                'is_dormant'        => 1,
                'is_active'         => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $ownerIds[$hospitalId] = (int) $this->db->insertID();
        }

        return ['owners' => $ownerIds, 'agency' => $agencyId];
    }

    private function insertHospitals(string $now): void
    {
        $hospitals = [
            [
                'id'         => 1,
                'name'       => '강남성형외과',
                'type'       => 1,
                'phone'      => '02-1234-5678',
                'address'    => '서울 강남구 테헤란로 123',
                'status'     => 'active',
                'is_deleted' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'name'       => '서울네트워크모병원',
                'type'       => 2,
                'phone'      => '02-9999-0000',
                'address'    => '서울 서초구 반포대로 456',
                'status'     => 'active',
                'is_deleted' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 3,
                'name'       => '분당자병원',
                'type'       => 3,
                'phone'      => '031-1111-2222',
                'address'    => '경기 성남시 분당구 판교로 789',
                'status'     => 'active',
                'is_deleted' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($hospitals as $row) {
            $this->db->table('hospitals')->insert($row);
        }
    }

    /**
     * @param array{owners: array<int, int>, agency: int} $links
     */
    private function insertAdvertisers(string $now, array $links): void
    {
        $owners = $links['owners'];
        $agency = $links['agency'];

        $advertisers = [
            [
                'id'                => 1,
                'hospital_id'       => 1,
                'hospital_name'     => '강남성형외과',
                'contact_name'      => '김담당',
                'contact_email'     => 'kim@gannam.com',
                'contact_phone'     => '010-1234-5678',
                'business_no'       => '123-45-67890',
                'is_network'        => 0,
                'network_parent_id' => null,
                'agency_user_id'    => $agency,             // 대행사 소유
                'owner_user_id'     => $owners[1] ?? null,
                'status'            => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id'                => 2,
                'hospital_id'       => 2,
                'hospital_name'     => '서울네트워크모병원',
                'contact_name'      => '이원장',
                'contact_email'     => 'lee@network.com',
                'contact_phone'     => '010-9999-0000',
                'business_no'       => '999-88-77777',
                'is_network'        => 1,
                'network_parent_id' => null,
                'agency_user_id'    => $agency,             // 대행사 소유
                'owner_user_id'     => $owners[2] ?? null,
                'status'            => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id'                => 3,
                'hospital_id'       => 3,
                'hospital_name'     => '분당자병원',
                'contact_name'      => '박지점장',
                'contact_email'     => 'park@bundang.com',
                'contact_phone'     => '010-1111-2222',
                'business_no'       => null,
                'is_network'        => 2,
                'network_parent_id' => 2,
                'agency_user_id'    => null,                // 대행사 미연결(직접 광고주)
                'owner_user_id'     => $owners[3] ?? null,
                'status'            => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ];

        foreach ($advertisers as $row) {
            $this->db->table('advertisers')->insert($row);
        }
    }

    private function insertContractsAndOrders(string $now): void
    {
        // 광고주별 contracts 데이터 (광고주당 1건)
        $contracts = [
            ['id' => 1, 'hospital_id' => 1, 'hospital_name' => '강남성형외과',    'title' => '강남성형외과 2026 계약', 'pay_type' => 1],
            ['id' => 2, 'hospital_id' => 2, 'hospital_name' => '서울네트워크모병원', 'title' => '서울네트워크 2026 계약',  'pay_type' => 1],
            ['id' => 3, 'hospital_id' => 3, 'hospital_name' => '분당자병원',       'title' => '분당자병원 2026 계약',   'pay_type' => 2],
        ];

        foreach ($contracts as $row) {
            $this->db->table('contracts')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // 광고주별 contract_orders 데이터 (광고주당 3건 = 총 9건)
        $orderGroups = [
            // hospital_id=1 (contract_id=1), order id 1~3
            [
                ['id' => 1, 'hospital_id' => 1, 'hospital_name' => '강남성형외과', 'ad_type' => 1, 'ad_type2' => 1, 'ad_price' => 2000000, 'contract_status' => 1, 'deposit_date' => $now],
                ['id' => 2, 'hospital_id' => 1, 'hospital_name' => '강남성형외과', 'ad_type' => 1, 'ad_type2' => 2, 'ad_price' => 3000000, 'contract_status' => 1, 'deposit_date' => $now],
                ['id' => 3, 'hospital_id' => 1, 'hospital_name' => '강남성형외과', 'ad_type' => 2, 'ad_type2' => 4, 'ad_price' => 1500000, 'contract_status' => 1, 'deposit_date' => $now],
            ],
            // hospital_id=2 (contract_id=2), order id 4~6
            [
                ['id' => 4, 'hospital_id' => 2, 'hospital_name' => '서울네트워크모병원', 'ad_type' => 1, 'ad_type2' => 1, 'ad_price' => 5000000, 'contract_status' => 1, 'deposit_date' => $now],
                ['id' => 5, 'hospital_id' => 2, 'hospital_name' => '서울네트워크모병원', 'ad_type' => 2, 'ad_type2' => 4, 'ad_price' => 4000000, 'contract_status' => 1, 'deposit_date' => $now],
                ['id' => 6, 'hospital_id' => 2, 'hospital_name' => '서울네트워크모병원', 'ad_type' => 1, 'ad_type2' => 3, 'ad_price' => 2500000, 'contract_status' => 1, 'deposit_date' => $now],
            ],
            // hospital_id=3 (contract_id=3), order id 7~9
            [
                ['id' => 7, 'hospital_id' => 3, 'hospital_name' => '분당자병원', 'ad_type' => 1, 'ad_type2' => 1, 'ad_price' => 1000000, 'contract_status' => 1, 'deposit_date' => $now],
                ['id' => 8, 'hospital_id' => 3, 'hospital_name' => '분당자병원', 'ad_type' => 1, 'ad_type2' => 2, 'ad_price' => 800000,  'contract_status' => 1, 'deposit_date' => $now],
                ['id' => 9, 'hospital_id' => 3, 'hospital_name' => '분당자병원', 'ad_type' => 2, 'ad_type2' => 4, 'ad_price' => 1200000, 'contract_status' => 1, 'deposit_date' => $now],
            ],
        ];

        $contractId = 1;

        foreach ($orderGroups as $orders) {
            foreach ($orders as $order) {
                $this->db->table('contract_orders')->insert(array_merge($order, [
                    'contract_type' => 1,
                    'parent_id'     => null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]));

                // contract_order_connects: contract ↔ contract_order 연결
                $this->db->table('contract_order_connects')->insert([
                    'contract_id'       => $contractId,
                    'contract_order_id' => $order['id'],
                    'created_at'        => $now,
                ]);

                // deposits: 계약충전(status=2) 1건씩 삽입 — KPI 잔액 집계 동작을 위해
                $this->db->table('deposits')->insert([
                    'contract_id'       => $contractId,
                    'contract_order_id' => $order['id'],
                    'status'            => 2,
                    'is_minus'          => 0,
                    'price'             => $order['ad_price'],
                    'users_id'          => null,
                    'note'              => '샘플 데이터 초기 충전',
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }

            $contractId++;
        }
    }

    private function insertCampaigns(string $now): void
    {
        // 광고주당 캠페인 2개씩 (총 6개)
        $campaigns = [
            // 광고주 1 (hospital_id=1)
            [
                'ad_title'          => '강남성형 눈성형 이벤트',
                'hospital_id'       => 1,
                'hospital_type'     => 1,
                'ad_type'           => 1,
                'ad_start_date'     => '2026-01-01',
                'ad_end_date'       => '2026-12-31',
                'cost_type'         => 1,
                'general_cost'      => 500000,
                'discount_cost'     => 450000,
                'db_cost'           => 30000,
                'category'          => 1,
                'exposure'          => 1,
                'contract_id'       => 1,
                'contract_order_id' => 1,
                'status'            => 'active',
                'channel'           => 1,
            ],
            [
                'ad_title'          => '강남성형 코성형 이벤트',
                'hospital_id'       => 1,
                'hospital_type'     => 1,
                'ad_type'           => 1,
                'ad_start_date'     => '2026-03-01',
                'ad_end_date'       => '2026-06-30',
                'cost_type'         => 1,
                'general_cost'      => 700000,
                'discount_cost'     => 600000,
                'db_cost'           => 40000,
                'category'          => 1,
                'exposure'          => 3,
                'contract_id'       => 1,
                'contract_order_id' => 2,
                'status'            => 'pending',
                'channel'           => 1,
            ],
            // 광고주 2 (hospital_id=2)
            [
                'ad_title'          => '서울네트워크 라식 이벤트',
                'hospital_id'       => 2,
                'hospital_type'     => 2,
                'ad_type'           => 2,
                'ad_start_date'     => '2026-01-01',
                'ad_end_date'       => '2026-12-31',
                'cost_type'         => 1,
                'general_cost'      => 1000000,
                'discount_cost'     => 850000,
                'db_cost'           => 0,
                'category'          => 2,
                'exposure'          => 2,
                'contract_id'       => 2,
                'contract_order_id' => 4,
                'status'            => 'active',
                'channel'           => 1,
            ],
            [
                'ad_title'          => '서울네트워크 메인배너 광고',
                'hospital_id'       => 2,
                'hospital_type'     => 2,
                'ad_type'           => 2,
                'ad_start_date'     => '2026-02-01',
                'ad_end_date'       => '2026-07-31',
                'cost_type'         => 1,
                'general_cost'      => 0,
                'discount_cost'     => 0,
                'db_cost'           => 0,
                'category'          => 0,
                'exposure'          => 1,
                'contract_id'       => 2,
                'contract_order_id' => 5,
                'status'            => 'active',
                'channel'           => 2,
            ],
            // 광고주 3 (hospital_id=3)
            [
                'ad_title'          => '분당자병원 피부과 이벤트',
                'hospital_id'       => 3,
                'hospital_type'     => 3,
                'ad_type'           => 1,
                'ad_start_date'     => '2026-04-01',
                'ad_end_date'       => '2026-09-30',
                'cost_type'         => 1,
                'general_cost'      => 300000,
                'discount_cost'     => 250000,
                'db_cost'           => 20000,
                'category'          => 3,
                'exposure'          => 1,
                'contract_id'       => 3,
                'contract_order_id' => 7,
                'status'            => 'pending',
                'channel'           => 1,
            ],
            [
                'ad_title'          => '분당자병원 탈모 치료 이벤트',
                'hospital_id'       => 3,
                'hospital_type'     => 3,
                'ad_type'           => 1,
                'ad_start_date'     => '2026-05-01',
                'ad_end_date'       => '2026-10-31',
                'cost_type'         => 1,
                'general_cost'      => 200000,
                'discount_cost'     => 180000,
                'db_cost'           => 15000,
                'category'          => 3,
                'exposure'          => 1,
                'contract_id'       => 3,
                'contract_order_id' => 8,
                'status'            => 'active',
                'channel'           => 1,
            ],
        ];

        foreach ($campaigns as $row) {
            $this->db->table('campaigns')->insert(array_merge($row, [
                'is_deleted' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
