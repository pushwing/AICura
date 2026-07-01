<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * advertiser_boards + advertiser_board_comments + advertiser_board_files + black_lists
 *
 * 광고주(병원) 전용 공지/문의 게시판 및 사용자 블랙리스트
 */
class CreateAdvertiserBoardAndBlacklistTables extends Migration
{
    public function up(): void
    {
        // ── advertiser_boards (광고주 게시판) ─────────────────────
        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'BIGINT'],
            'user_name'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'board_pid'      => ['type' => 'INT', 'null' => true],
            // 1 공지사항, 2 1:1문의, 3 FAQ
            'type'           => ['type' => 'TINYINT', 'default' => 1],
            'title'          => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'contents'       => ['type' => 'TEXT', 'null' => true],
            'is_notice'      => ['type' => 'TINYINT', 'default' => 0],
            'is_secret'      => ['type' => 'TINYINT', 'default' => 0],
            'files_count'    => ['type' => 'INT', 'default' => 0],
            'hit'            => ['type' => 'INT', 'default' => 0],
            'comment_count'  => ['type' => 'INT', 'default' => 0],
            'like_count'     => ['type' => 'INT', 'default' => 0],
            'complain_count' => ['type' => 'INT', 'default' => 0],
            'category'       => ['type' => 'INT', 'default' => 0],
            'category_name'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'ip'             => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'is_list'        => ['type' => 'TINYINT', 'default' => 1],
            // 0 미삭제, 1 임시삭제, 2 완전삭제
            'is_delete'      => ['type' => 'TINYINT', 'default' => 0],
            'delete_date'    => ['type' => 'DATETIME', 'null' => true],
            'password'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'reply_order'    => ['type' => 'TINYINT', 'default' => 0],
            'general_setting' => ['type' => 'TEXT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id', false, false, 'idx_advertiser_boards_user_id');
        $this->forge->addKey('like_count', false, false, 'idx_advertiser_boards_like_count');
        $this->forge->createTable('advertiser_boards');

        // ── advertiser_board_comments ─────────────────────────────
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
        $this->forge->addKey('board_id', false, false, 'idx_advertiser_board_comments_board_id');
        $this->forge->createTable('advertiser_board_comments');

        // ── advertiser_board_files ────────────────────────────────
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'board_id'      => ['type' => 'INT'],
            'type'          => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'file_name'     => ['type' => 'VARCHAR', 'constraint' => 500, 'default' => ''],
            'file_type'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'order_by'      => ['type' => 'INT', 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('board_id', false, false, 'idx_advertiser_board_files_board_id');
        $this->forge->createTable('advertiser_board_files');

        // ── black_lists (사용자 블랙리스트) ─────────────────────
        $this->forge->addField([
            'id'                  => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'users_id'            => ['type' => 'INT'],
            'advertiser_user_id'  => ['type' => 'INT'],
            'reg_id'              => ['type' => 'INT', 'null' => true],
            'is_delete'           => ['type' => 'TINYINT', 'default' => 0],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('users_id', false, false, 'idx_black_lists_users_id');
        $this->forge->addKey('advertiser_user_id', false, false, 'idx_black_lists_advertiser_user_id');
        $this->forge->createTable('black_lists');
    }

    public function down(): void
    {
        $this->forge->dropTable('black_lists', true);
        $this->forge->dropTable('advertiser_board_files', true);
        $this->forge->dropTable('advertiser_board_comments', true);
        $this->forge->dropTable('advertiser_boards', true);
    }
}
