<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoryAndCodeTables extends Migration
{
    public function up(): void
    {
        // ── event_categories (광고 카테고리) ─────────────────────
        $this->forge->addField([
            'id'        => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'parent_id' => ['type' => 'INT', 'null' => true],
            'title'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            // 0 기본, 1 설문용, 2 태그용
            'category_type'   => ['type' => 'TINYINT', 'default' => 0],
            'sort'            => ['type' => 'INT', 'null' => true],
            'is_visible'      => ['type' => 'TINYINT', 'default' => 1],
            'image'           => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'coocha_tags'     => ['type' => 'TEXT', 'null' => true],
            'coocha_category' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('parent_id', false, false, 'idx_event_categories_parent_id');
        $this->forge->createTable('event_categories');

        // ── mapping_categories (v1-v2 카테고리 매핑, 설문용) ─────
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'v1' => ['type' => 'INT'],
            // s1 시술, s2 치과, s3 피부, s4 성형, s5 라식
            'v2' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => ''],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('v1', false, false, 'idx_mapping_categories_v1');
        $this->forge->createTable('mapping_categories');

        // ── codes (공통 코드 테이블) ─────────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'code'        => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => ''],
            // 종류 구분 (광고유형, 상태, 채널 등)
            'type'   => ['type' => 'VARCHAR', 'constraint' => 20],
            'is_use' => ['type' => 'TINYINT', 'default' => 1],
            'sort'   => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['type', 'code'], false, false, 'idx_codes_type_code');
        $this->forge->createTable('codes');
    }

    public function down(): void
    {
        $this->forge->dropTable('codes', true);
        $this->forge->dropTable('mapping_categories', true);
        $this->forge->dropTable('event_categories', true);
    }
}
