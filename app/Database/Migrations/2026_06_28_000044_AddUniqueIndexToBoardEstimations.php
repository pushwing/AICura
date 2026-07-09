<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * board_estimations 유니크 인덱스 — 좋아요/신고 1인 1회 보장 (이슈 #102)
 *
 * 같은 (type, board_id, user_id) 조합의 중복 행을 DB 차원에서 차단한다.
 * 이 제약이 없으면 동시 요청 시 SELECT-후-INSERT 경쟁으로 중복 행이 생겨
 * like_count·complain_count 집계가 이중 증가한다.
 *
 *   type : 1 좋아요 · 2 신고 (서로 다른 type 은 공존 가능하므로 type 을 키에 포함)
 */
class AddUniqueIndexToBoardEstimations extends Migration
{
    private const string INDEX_NAME = 'uniq_board_estimations_type_board_user';

    public function up(): void
    {
        // 기존 테이블이므로 forge 가 아닌 직접 인덱스 생성 (MySQL·SQLite 공통 문법)
        $this->db->query(
            'CREATE UNIQUE INDEX ' . self::INDEX_NAME
            . ' ON board_estimations (type, board_id, user_id)',
        );
    }

    public function down(): void
    {
        // Forge 가 플랫폼별 DROP INDEX 문법을 생성 (prefixKeyName=false: 인덱스명 그대로 사용)
        $this->forge->dropKey('board_estimations', self::INDEX_NAME, false);
    }
}
