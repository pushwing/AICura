<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 광고 타입별 전용 단가 컬럼 추가 (이슈 #157)
 *
 * CPA는 기존 db_cost, 프로모션·옵션은 기존 text_cost(가격 텍스트)를 그대로 사용하고,
 * CPM(노출당)·CPC(클릭당)만 전용 단가 컬럼이 없어 신설한다.
 * campaign_review_requests 는 캠페인 콘텐츠 필드를 검수 승인 전까지 그대로 미러링하는
 * 스테이징 테이블이므로 동일 컬럼을 함께 추가한다.
 */
class AddPerTypeCostToCampaigns extends Migration
{
    public function up(): void
    {
        $fields = [
            'cpm_price' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'default' => 0, 'after' => 'db_cost'],
            'cpc_price' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'default' => 0, 'after' => 'cpm_price'],
        ];

        $this->forge->addColumn('campaigns', $fields);
        $this->forge->addColumn('campaign_review_requests', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('campaigns', ['cpm_price', 'cpc_price']);
        $this->forge->dropColumn('campaign_review_requests', ['cpm_price', 'cpc_price']);
    }
}
