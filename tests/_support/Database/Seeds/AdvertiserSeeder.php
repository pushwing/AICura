<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdvertiserSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // hospitals — advertisers FK 선행 필요
        foreach ([
            ['id' => 1, 'name' => '강남성형외과',     'type' => 1],
            ['id' => 2, 'name' => '서울네트워크모병원', 'type' => 2],
            ['id' => 3, 'name' => '분당자병원',        'type' => 3],
        ] as $h) {
            $this->db->table('hospitals')->insert(array_merge($h, [
                'status'     => 'active',
                'is_deleted' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // advertisers: 일반 광고주
        $this->db->table('advertisers')->insert([
            'id'                => 1,
            'hospital_id'       => 1,
            'hospital_name'     => '강남성형외과',
            'contact_name'      => '김담당',
            'contact_email'     => 'kim@gannam.com',
            'contact_phone'     => '010-1234-5678',
            'business_no'       => '123-45-67890',
            'is_network'        => 0,
            'network_parent_id' => null,
            'status'            => 1,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // advertisers: 네트워크 모병원
        $this->db->table('advertisers')->insert([
            'id'                => 2,
            'hospital_id'       => 2,
            'hospital_name'     => '서울네트워크모병원',
            'contact_name'      => '이원장',
            'contact_email'     => 'lee@network.com',
            'contact_phone'     => '010-9999-0000',
            'business_no'       => '999-88-77777',
            'is_network'        => 1,
            'network_parent_id' => null,
            'status'            => 1,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // advertisers: 네트워크 자병원 (모병원 ID=2)
        $this->db->table('advertisers')->insert([
            'id'                => 3,
            'hospital_id'       => 3,
            'hospital_name'     => '분당자병원',
            'contact_name'      => '박지점장',
            'contact_email'     => 'park@bundang.com',
            'contact_phone'     => '010-1111-2222',
            'business_no'       => null,
            'is_network'        => 2,
            'network_parent_id' => 2,
            'status'            => 1,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
    }
}
