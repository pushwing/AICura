<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToCampaigns extends Migration
{
    public function up(): void
    {
        $fields = [
            // 영업 담당자 번호
            'agency_user_id' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'hospital_type',
            ],
            // 등록자 번호
            'user_id' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'agency_user_id',
            ],
            // 기간 연장 여부
            'ad_date_extend' => [
                'type'    => 'TINYINT',
                'default' => 0,
                'after'   => 'ad_end_date',
            ],
            // 이미지노출: 1 이벤트모델, 2 의료진, 3 전후사진 (다중: 1,2,3)
            'where_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'keyword',
            ],
            // 모델모집 이미지 갯수: 0, 1, 2
            'model_image_count' => [
                'type'    => 'TINYINT',
                'default' => 0,
                'after'   => 'where_image',
            ],
            // 이벤트신청버튼 설정 JSON
            'ad_detail_info' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'model_image_count',
            ],
            // 검수일
            'inspect_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'ad_detail_info',
            ],
            // 후기 노출여부: 1 노출, 2 미노출
            'is_view_board' => [
                'type'    => 'TINYINT',
                'default' => 1,
                'after'   => 'inspect_date',
            ],
            // 커스텀랜딩: 1 케어랩스, 2 병원사업자정보 (다중 가능)
            'custom_randing' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'is_view_board',
            ],
            // 옵션이벤트번호 (다중: 1,2,3)
            'option_ad_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'custom_randing',
            ],
            // 커스텀랜딩 2인 경우 대표명
            'custom1' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'option_ad_id',
            ],
            // 커스텀랜딩 2인 경우 사업자번호
            'custom2' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'custom1',
            ],
            // 커스텀랜딩 2인 경우 연락처
            'custom3' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'custom2',
            ],
            // 제휴매체 (다중: 1,2,3)
            'cooperation' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'region',
            ],
            // 네트워크 자병원 ID (다중)
            'sub_hospital_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'cooperation',
            ],
            'contract_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'after'      => 'contract_order_id',
            ],
            'del_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at',
            ],
            'delete_user_id' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'del_date',
            ],
        ];

        $this->forge->addColumn('campaigns', $fields);

        $this->db->query('CREATE INDEX idx_campaigns_agency_user ON campaigns (agency_user_id)');
        $this->db->query('CREATE INDEX idx_campaigns_contract_order ON campaigns (contract_order_id)');
    }

    public function down(): void
    {
        $this->forge->dropColumn('campaigns', [
            'agency_user_id', 'user_id', 'ad_date_extend', 'where_image',
            'model_image_count', 'ad_detail_info', 'inspect_date',
            'is_view_board', 'custom_randing', 'option_ad_id',
            'custom1', 'custom2', 'custom3', 'cooperation', 'sub_hospital_id',
            'contract_name', 'del_date', 'delete_user_id',
        ]);
    }
}
