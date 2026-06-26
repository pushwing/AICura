<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 의료광고 심의 사전검사 결과 테이블 (이슈 #71)
 *
 * AI(Gemini)가 소재 텍스트를 의료법 제56조 위반 유형 기준으로 1차 스크리닝한
 * 결과를 보관한다.
 *   risk_level    safe(안전) / warning(주의) / violation(위반)
 *   flags         위반 플래그 JSON 배열 [{rule, severity, quote, reason, suggestion}]
 *   checked_text  검사한 원본 텍스트 (감사·재검토용)
 *   model         검사에 사용한 AI 모델명
 */
class CreateCreativeComplianceChecksTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'campaign_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'review_request_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'risk_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'flags' => [
                'type' => 'JSON',
            ],
            'checked_text' => [
                'type' => 'MEDIUMTEXT',
            ],
            'model' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['campaign_id'], false, false, 'idx_compliance_campaign');

        $this->forge->createTable('creative_compliance_checks');
    }

    public function down(): void
    {
        $this->forge->dropTable('creative_compliance_checks');
    }
}
