<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * AI 일일 보고서 저장 테이블 (이슈 #65)
 *
 * Groq AI가 매일 생성하는 매출/소진 보고서를 보관한다.
 *   type        revenue(매출현황) / consumption(소진보고서)
 *   content     AI가 생성한 마크다운 본문
 *   report_date 보고서 기준일 (집계 대상일)
 *   meta        집계 원본 JSON (감사·재생성 참고용)
 */
class CreateAiReportsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'content' => [
                'type' => 'MEDIUMTEXT',
            ],
            'report_date' => [
                'type' => 'DATE',
            ],
            'meta' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey(['type', 'report_date'], false, false, 'idx_ai_reports_type_date');

        $this->forge->createTable('ai_reports');
    }

    public function down(): void
    {
        $this->forge->dropTable('ai_reports');
    }
}
