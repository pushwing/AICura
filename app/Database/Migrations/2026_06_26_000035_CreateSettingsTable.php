<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * settings — 사이트 전역 환경설정 (이슈 #63)
 *
 * key-value 구조로 추후 항목 추가가 쉽도록 구성한다.
 *   setting_key   : 설정 키 (예: site_name, admin_email) — unique
 *   setting_value : 설정 값 (text, null 허용)
 *
 * `key`·`value`는 MySQL 예약어이므로 setting_ 접두어를 사용한다.
 */
class CreateSettingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'setting_key'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'setting_value' => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('setting_key', 'uniq_settings_setting_key');
        $this->forge->createTable('settings');
    }

    public function down(): void
    {
        $this->forge->dropTable('settings', true);
    }
}
