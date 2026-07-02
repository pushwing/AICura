<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ai_reports에 스코프 컬럼 추가 (이슈 #65 — 포털 확장)
 *
 * 같은 매출/소진 보고서를 노출 주체별로 분리 저장한다.
 *   scope_type  global(운영자 전체) / hospital(광고주 단일 병원) / agency(대행사)
 *   scope_id    hospital_id(hospital) / agency_user_id(agency) / null(global)
 */
class AddScopeToAiReportsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('ai_reports', [
            'scope_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'global',
                'after'      => 'id',
            ],
            'scope_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'scope_type',
            ],
        ]);

        $this->db->query(
            'CREATE INDEX idx_ai_reports_scope ON ai_reports (scope_type, scope_id, type, report_date)'
        );
    }

    public function down(): void
    {
        // 드라이버별 DROP INDEX 구문 차이를 forge가 처리 (MySQL: ... ON table / SQLite: DROP INDEX)
        $this->forge->dropKey('ai_reports', 'idx_ai_reports_scope', false);
        $this->forge->dropColumn('ai_reports', ['scope_type', 'scope_id']);
    }
}
