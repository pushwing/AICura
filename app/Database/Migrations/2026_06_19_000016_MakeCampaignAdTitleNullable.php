<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 최초 캠페인 등록 시 campaigns에는 메타데이터만 저장하므로
 * ad_title을 NULL 허용으로 변경한다.
 * 실제 콘텐츠(제목/가격/이미지 등)는 campaign_review_requests를 통해 검수 후 복사된다.
 */
class MakeCampaignAdTitleNullable extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('campaigns', [
            'ad_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
    }

    public function down(): void
    {
        // 기존 NULL 행이 있을 경우 빈 문자열로 채운 후 복원
        $this->db->query("UPDATE campaigns SET ad_title = '' WHERE ad_title IS NULL");

        $this->forge->modifyColumn('campaigns', [
            'ad_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
        ]);
    }
}
