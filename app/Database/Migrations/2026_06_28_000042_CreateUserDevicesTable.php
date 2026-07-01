<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * user_devices 테이블 — 외부(소비자) 앱 푸시 토큰·기기 등록 (이슈 #97)
 *
 * 한 사용자가 여러 기기를 가질 수 있으므로 push_token 단위로 1행을 둔다.
 * 토큰은 기기에 고유하므로 UNIQUE 로 두고, 재등록 시 user_id·platform 을 갱신한다.
 *
 *   platform : 2 iOS · 3 Android (users.where_from 과 동일 코드)
 */
class CreateUserDevicesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'platform' => [
                'type'    => 'TINYINT',
                'default' => 2,
            ],
            'push_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('push_token', 'uniq_user_devices_token');
        $this->forge->addKey('user_id', false, false, 'idx_user_devices_user_id');

        $this->forge->createTable('user_devices');
    }

    public function down(): void
    {
        $this->forge->dropTable('user_devices', true);
    }
}
