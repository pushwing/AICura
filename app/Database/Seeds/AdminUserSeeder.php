<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

/**
 * 기본 관리자 계정(user_type=401) Seeder
 *
 * 최초 어드민 로그인을 위한 관리자 계정을 생성한다.
 *   이메일   : admin@aicura.com
 *   비밀번호 : Admin@2026!
 *
 * 실행: php spark db:seed AdminUserSeeder
 *
 * 주의:
 *   - 재실행 안전 — 동일 이메일이 이미 있으면 건너뛴다(비밀번호 덮어쓰기 없음).
 *   - 운영 환경에서는 최초 로그인 후 반드시 비밀번호를 변경할 것.
 */
class AdminUserSeeder extends Seeder
{
    /** 기본 관리자 이메일 */
    private const ADMIN_EMAIL = 'admin@aicura.com';

    /** 기본 관리자 비밀번호(최초 로그인 후 변경 권장) */
    private const ADMIN_PASSWORD = 'Admin@2026!';

    public function run(): void
    {
        // 재실행 안전: 이미 존재하면 아무것도 하지 않는다.
        $exists = $this->db->table('users')
            ->where('email', self::ADMIN_EMAIL)
            ->countAllResults() > 0;

        if ($exists) {
            echo "이미 존재 — 건너뜀: " . self::ADMIN_EMAIL . PHP_EOL;

            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->table('users')->insert([
            'email'             => self::ADMIN_EMAIL,
            'password'          => password_hash(self::ADMIN_PASSWORD, PASSWORD_DEFAULT),
            'username'          => '관리자',
            'user_type'         => UserModel::TYPE_ADMIN, // 401 관리자
            'where_from'        => 4,                     // 4 어드민
            'provider'          => 9,                     // 9 이메일(일반)
            'is_agency_account' => 0,
            'is_dormant'        => 1,                     // 1 비휴면(활성)
            'is_active'         => 1,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        echo "생성 완료 — " . self::ADMIN_EMAIL . " (user_type=" . UserModel::TYPE_ADMIN . ")" . PHP_EOL;
    }
}
