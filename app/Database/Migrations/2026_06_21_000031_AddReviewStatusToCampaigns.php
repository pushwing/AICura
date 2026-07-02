<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * campaigns 에 검수 현재 상태(review_status) 캐시 컬럼 추가.
 *
 * 검수 이력의 원천(source of truth)은 campaign_review_requests 이며,
 * campaigns.review_status 는 목록·배지 표시를 위한 비정규화 캐시다.
 * - 등록/수정 요청 시: pending
 * - 검수 승인 시:     approved (콘텐츠도 campaigns 로 복사)
 * - 검수 반려 시:     rejected
 */
class AddReviewStatusToCampaigns extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('campaigns', [
            'review_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
                'after'      => 'status',
            ],
        ]);

        // 기존 캠페인(검수 흐름 도입 이전 데이터)은 이미 노출 중이므로 approved 로 간주
        $this->db->query("UPDATE campaigns SET review_status = 'approved'");

        $this->db->query('CREATE INDEX idx_campaigns_review_status ON campaigns (review_status)');
    }

    public function down(): void
    {
        $this->forge->dropColumn('campaigns', 'review_status');
    }
}
