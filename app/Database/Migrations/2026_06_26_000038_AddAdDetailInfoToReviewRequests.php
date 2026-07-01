<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * campaign_review_requests 에 상세문구(ad_detail_info) 컬럼 추가 (이슈 #73)
 *
 * 광고 카피 상세문구도 ad_title 과 동일하게 검수 요청 → 승인 시 campaigns 로
 * 복사되는 흐름을 타도록 검수 요청 테이블에 컬럼을 추가한다.
 * (campaigns.ad_detail_info 는 이미 존재)
 */
class AddAdDetailInfoToReviewRequests extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('campaign_review_requests', [
            'ad_detail_info' => [
                'type'  => 'MEDIUMTEXT',
                'null'  => true,
                'after' => 'ad_title',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('campaign_review_requests', 'ad_detail_info');
    }
}
