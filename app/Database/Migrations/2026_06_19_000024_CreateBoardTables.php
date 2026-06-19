<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * boards + board_comments + board_files + board_estimations + board_ranks + board_summaries + board_tags
 *
 * 앱 사용자 후기/게시판 관련 테이블 전체.
 * ERD: board, board_comments, board_files, board_estimation, board_rank, board_summary, board_tags
 */
class CreateBoardTables extends Migration
{
    public function up(): void
    {
        // ── boards (후기/게시판 원글) ────────────────────────────
        $this->forge->addField([
            'id'                => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            // 작성자 (2로 시작 11자리 = 웹 비회원)
            'user_id'           => ['type' => 'BIGINT'],
            'user_name'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'user_email'        => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            // 답글인 경우 원글 번호
            'board_pid'         => ['type' => 'INT', 'null' => true],
            // 1 이벤트, 2 병원, 3 접수
            'type'              => ['type' => 'TINYINT', 'default' => 1],
            'target_id'         => ['type' => 'INT'],
            'subject'           => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'contents'          => ['type' => 'TEXT', 'null' => true],
            'is_notice'         => ['type' => 'TINYINT', 'default' => 0],
            'is_member'         => ['type' => 'TINYINT', 'default' => 1],
            'is_secret'         => ['type' => 'TINYINT', 'default' => 0],
            'files_count'       => ['type' => 'INT', 'default' => 0],
            'hit'               => ['type' => 'INT', 'default' => 0],
            'comment_count'     => ['type' => 'INT', 'default' => 0],
            'reply_count'       => ['type' => 'INT', 'default' => 0],
            'like_count'        => ['type' => 'INT', 'default' => 0],
            'complain_count'    => ['type' => 'INT', 'default' => 0],
            'help_yes_count'    => ['type' => 'INT', 'default' => 0],
            'help_no_count'     => ['type' => 'INT', 'default' => 0],
            'info_request_count' => ['type' => 'INT', 'default' => 0],
            'category'          => ['type' => 'INT', 'default' => 0],
            'category_name'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'ip'                => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'is_list'           => ['type' => 'TINYINT', 'default' => 1],
            // 0 미삭제, 1 임시삭제, 2 완전삭제
            'is_delete'         => ['type' => 'TINYINT', 'default' => 0],
            'delete_memo'       => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'delete_date'       => ['type' => 'DATETIME', 'null' => true],
            'password'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'reply_order'       => ['type' => 'TINYINT', 'default' => 0],
            'general_setting'   => ['type' => 'TEXT', 'null' => true],
            // 별점
            'rate_sum'          => ['type' => 'FLOAT', 'default' => 0],
            'rate1'             => ['type' => 'FLOAT', 'default' => 0],
            'rate2'             => ['type' => 'FLOAT', 'default' => 0],
            'rate3'             => ['type' => 'FLOAT', 'default' => 0],
            // 설문 진료항목: 0 없음, 1 시술, 2 수술, 3 라식, 4 피부, 5 치과
            'survey_type'       => ['type' => 'TINYINT', 'null' => true],
            'survey1'           => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'survey2'           => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'survey3'           => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'survey4'           => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'survey5'           => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'survey6'           => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'call_request_id'   => ['type' => 'INT', 'null' => true],
            // 이벤트 신청 없이 후기 작성: 1, 그외: 2
            'not_event_user'    => ['type' => 'TINYINT', 'default' => 2],
            // 1 안드, 2 iOS, 3 웹
            'device'            => ['type' => 'TINYINT', 'null' => true],
            'funnel'            => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id', false, false, 'idx_boards_user_id');
        $this->forge->addKey('target_id', false, false, 'idx_boards_target_id');
        $this->forge->addKey('rate_sum', false, false, 'idx_boards_rate_sum');
        $this->forge->addKey('like_count', false, false, 'idx_boards_like_count');
        $this->forge->createTable('boards');

        // ── board_comments (댓글) ─────────────────────────────────
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'board_id'        => ['type' => 'INT'],
            'user_id'         => ['type' => 'BIGINT'],
            'user_name'       => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
            'contents'        => ['type' => 'TEXT'],
            'is_secret'       => ['type' => 'TINYINT', 'default' => 0],
            'is_list'         => ['type' => 'TINYINT', 'default' => 1],
            'is_delete'       => ['type' => 'TINYINT', 'default' => 0],
            'recommend_count' => ['type' => 'INT', 'default' => 0],
            'files_count'     => ['type' => 'INT', 'default' => 0],
            'ip'              => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'password'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'comment_order'   => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('board_id', false, false, 'idx_board_comments_board_id');
        $this->forge->createTable('board_comments');

        // ── board_files (첨부파일) ────────────────────────────────
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'board_id'      => ['type' => 'INT'],
            // 'reply' 등 추가 구분
            'type'          => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'file_name'     => ['type' => 'VARCHAR', 'constraint' => 500, 'default' => ''],
            'file_type'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'order_by'      => ['type' => 'INT', 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('board_id', false, false, 'idx_board_files_board_id');
        $this->forge->createTable('board_files');

        // ── board_estimations (좋아요/신고 등) ───────────────────
        $this->forge->addField([
            'id'       => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            /*
             * 1 좋아요, 2 신고, 3 도움O, 4 도움X,
             * 5 코멘트좋아요, 6 삭제히스토리, 7 후기수정이력
             */
            'type'     => ['type' => 'TINYINT'],
            'board_id' => ['type' => 'INT'],
            'user_id'  => ['type' => 'BIGINT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('board_id', false, false, 'idx_board_estimations_board_id');
        $this->forge->addKey('user_id', false, false, 'idx_board_estimations_user_id');
        $this->forge->createTable('board_estimations');

        // ── board_ranks (후기 정렬 순서, 1일 1회 갱신) ───────────
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'type'       => ['type' => 'INT', 'null' => true],
            // 차수: 20190402 (연월일)
            'order_date' => ['type' => 'INT'],
            'board_id'   => ['type' => 'INT'],
            'order_by'   => ['type' => 'INT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['order_date', 'board_id'], false, false, 'idx_board_ranks_order_date');
        $this->forge->createTable('board_ranks');

        // ── board_summaries (설문·별점 요약 통계) ────────────────
        $this->forge->addField([
            'id'        => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            // 1 이벤트, 2 병원, 3 접수
            'type'      => ['type' => 'TINYINT'],
            'target_id' => ['type' => 'INT'],
            'rate_sum'  => ['type' => 'FLOAT', 'default' => 0],
            'rate1'     => ['type' => 'FLOAT', 'default' => 0],
            'rate2'     => ['type' => 'FLOAT', 'default' => 0],
            'rate3'     => ['type' => 'FLOAT', 'default' => 0],
            // 0 없음, 1 시술, 2 치과, 3 피부, 4 성형, 5 라식
            'survey_type' => ['type' => 'TINYINT', 'default' => 0],
            // 설문 집계 (pipe-separated: 항목별 선택수)
            'survey11' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey12' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0|0|0'],
            'survey13' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0|0'],
            'survey14' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey15' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey21' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey22' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0|0|0'],
            'survey23' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey31' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey32' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0|0|0'],
            'survey33' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0|0'],
            'survey34' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey35' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey41' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey42' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0'],
            'survey43' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0|0|0'],
            'survey44' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey45' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey46' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0|0'],
            'survey51' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey52' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'survey53' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'survey54' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0|0|0'],
            'survey55' => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => '0|0|0'],
            'reg_date'  => ['type' => 'DATE', 'null' => true],
            'mod_date'  => ['type' => 'DATE', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['type', 'target_id'], false, false, 'idx_board_summaries_type_target');
        $this->forge->createTable('board_summaries');

        // ── board_tags (태그) ─────────────────────────────────────
        $this->forge->addField([
            'id'       => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'board_id' => ['type' => 'INT'],
            'type'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'tag_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('board_id', false, false, 'idx_board_tags_board_id');
        $this->forge->createTable('board_tags');
    }

    public function down(): void
    {
        $this->forge->dropTable('board_tags', true);
        $this->forge->dropTable('board_summaries', true);
        $this->forge->dropTable('board_ranks', true);
        $this->forge->dropTable('board_estimations', true);
        $this->forge->dropTable('board_files', true);
        $this->forge->dropTable('board_comments', true);
        $this->forge->dropTable('boards', true);
    }
}
