<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * guides — 시술 정보성 가이드 아티클 (이슈 #146, GEO 콘텐츠 자산)
 *
 * 공개 웹에 노출되어 Article·FAQPage·MedicalWebPage 구조화 데이터로 AI 검색 인용을 노린다.
 * 발행(status='published') 상태만 공개되며 슬러그로 접근한다.
 */
class CreateGuidesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            // 제목 (Article.headline)
            'title' => ['type' => 'VARCHAR', 'constraint' => 200],
            // URL 슬러그 (예: double-eyelid-surgery-guide)
            'slug' => ['type' => 'VARCHAR', 'constraint' => 220],
            // 요약 (meta description·Article.description)
            'summary' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            // 본문 HTML (Tiptap, Article.articleBody)
            'content' => ['type' => 'TEXT', 'null' => true],
            // FAQ 항목 JSON: [{"q":"...","a":"..."}] (FAQPage)
            'faq_json' => ['type' => 'TEXT', 'null' => true],
            // 관련 시술명 (MedicalProcedure.name) — 선택
            'procedure_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            // 발행 상태: draft | published
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('slug', false, true, 'uniq_guides_slug');
        $this->forge->addKey('status', false, false, 'idx_guides_status');

        $this->forge->createTable('guides');
    }

    public function down(): void
    {
        $this->forge->dropTable('guides');
    }
}
