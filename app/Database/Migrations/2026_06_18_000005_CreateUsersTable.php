<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * users 테이블 마이그레이션
 *
 * user_type 분류:
 *   1        일반 사용자 (앱/웹 가입)
 *   2        운영자 (어드민 로그인 가능)
 *   3        광고주 (병원)
 *   201      광고주병원
 *   202      일반병원
 *   203      접수병원
 *   401      관리자
 *   402      통계운영자
 *   403      일반운영자
 *   404      접수설치
 *   405      외부운영자
 *
 * provider:
 *   9        이메일(일반)
 *   1        Facebook
 *   2        Naver
 *   3        Kakao
 *
 * where_from:
 *   1 웹  2 iOS  3 Android  4 어드민
 */
class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            // 1 일반사용자, 2 운영자, 3 광고주, 201~203 병원유형, 401~405 운영자상세
            'user_type' => [
                'type'    => 'SMALLINT',
                'default' => 1,
            ],
            // 1 웹, 2 iOS, 3 Android, 4 어드민
            'where_from' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            // 9 이메일, 1 Facebook, 2 Naver, 3 Kakao
            'provider' => [
                'type'    => 'TINYINT',
                'default' => 9,
            ],
            'is_agency_account' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],
            'picture' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'age' => [
                'type'    => 'TINYINT',
                'null'    => true,
            ],
            'sex' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'job' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'health_point' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // 1 비휴면, 0 휴면
            'is_dormant' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'dormant_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_login_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_logout_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_activity_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            // 외부 SNS 연동 토큰
            'oauth_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'uid' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            // 권한 코드 (auth_code_id 콤마 구분)
            'group_auth_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addKey('user_type');
        $this->forge->addKey('is_dormant');

        $this->forge->createTable('users');
    }

    public function down(): void
    {
        $this->forge->dropTable('users');
    }
}
