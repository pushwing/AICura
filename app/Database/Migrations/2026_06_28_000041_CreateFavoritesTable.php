<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * favorites 테이블 — 외부(소비자) 앱 공용 찜/즐겨찾기 (이슈 #98)
 *
 * 캠페인(이벤트)·병원 등 대상 유형을 target_type 으로 구분해 한 테이블로 관리한다.
 * 찜 토글은 행 insert/delete 로 처리하므로 soft delete·updated_at 을 두지 않는다.
 *
 *   target_type : 'campaign' | 'hospital'
 */
class CreateFavoritesTable extends Migration
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
            'target_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'target_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        // 한 사용자가 같은 대상을 중복 찜할 수 없음
        $this->forge->addUniqueKey(['user_id', 'target_type', 'target_id'], 'uniq_favorites_user_target');
        // 대상별 찜 카운트·조회용
        $this->forge->addKey(['target_type', 'target_id'], false, false, 'idx_favorites_target');

        $this->forge->createTable('favorites');
    }

    public function down(): void
    {
        $this->forge->dropTable('favorites', true);
    }
}
