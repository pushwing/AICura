<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 앱 가입 일반 사용자(user_type=1) 샘플 Seeder
 *
 * 추후 구현될 회원가입 API(앱/웹)로 들어올 일반 사용자 데이터를 재현한다.
 * 광고주·대행사·병원 계정은 SampleDataSeeder 에서 생성하므로 여기서는 다루지 않는다.
 *
 * 필드 의미(CreateUsersTable 참고)
 *   where_from : 1 웹 · 2 iOS · 3 Android
 *   provider   : 9 이메일 · 2 Naver · 3 Kakao
 *   is_dormant : 1 비휴면(활성) · 0 휴면  ← 반전 의미 주의
 */
class SampleAppUserSeeder extends Seeder
{
    public function run(): void
    {
        $now      = date('Y-m-d H:i:s');
        $password = password_hash('password1234', PASSWORD_DEFAULT);

        $rows = $this->buildUsers();

        foreach ($rows as $i => $row) {
            $email = sprintf('appuser%02d@aicura.test', $i + 1);

            // 재실행 안전: 이미 존재하는 샘플 이메일은 건너뛴다
            $exists = $this->db->table('users')
                ->where('email', $email)
                ->countAllResults() > 0;

            if ($exists) {
                continue;
            }

            // 가입일 분산 — 최근 60일 내 랜덤, 마지막 로그인은 가입일 이후
            $createdAt   = date('Y-m-d H:i:s', strtotime("-{$row['days_ago']} days"));
            $lastLoginAt = date('Y-m-d H:i:s', strtotime("-" . max(0, $row['days_ago'] - $row['login_offset']) . " days"));

            $this->db->table('users')->insert([
                'email'             => $email,
                'password'          => $row['provider'] === 9 ? $password : null,
                'username'          => $row['username'],
                'user_type'         => 1, // 일반 사용자
                'where_from'        => $row['where_from'],
                'provider'          => $row['provider'],
                'is_agency_account' => 0,
                'phone'             => $row['phone'],
                'age'               => $row['age'],
                'sex'               => $row['sex'],
                'job'               => $row['job'],
                'health_point'      => $row['health_point'],
                'is_dormant'        => $row['is_dormant'],
                'is_active'         => 1,
                'last_login_at'     => $lastLoginAt,
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ]);
        }
    }

    /**
     * 20명의 일반 사용자 샘플 정의
     *
     * @return list<array{username:string, phone:string, age:int, sex:string, job:string, where_from:int, provider:int, health_point:int, is_dormant:int, days_ago:int, login_offset:int}>
     */
    private function buildUsers(): array
    {
        return [
            ['username' => '김서연', 'phone' => '010-2001-0001', 'age' => 27, 'sex' => 'F', 'job' => '회사원',     'where_from' => 2, 'provider' => 3, 'health_point' => 1500, 'is_dormant' => 1, 'days_ago' => 58, 'login_offset' => 56],
            ['username' => '이준호', 'phone' => '010-2001-0002', 'age' => 34, 'sex' => 'M', 'job' => '자영업',     'where_from' => 3, 'provider' => 9, 'health_point' => 300,  'is_dormant' => 1, 'days_ago' => 55, 'login_offset' => 50],
            ['username' => '박지민', 'phone' => '010-2001-0003', 'age' => 23, 'sex' => 'F', 'job' => '대학생',     'where_from' => 2, 'provider' => 2, 'health_point' => 2200, 'is_dormant' => 1, 'days_ago' => 52, 'login_offset' => 51],
            ['username' => '최우진', 'phone' => '010-2001-0004', 'age' => 41, 'sex' => 'M', 'job' => '전문직',     'where_from' => 1, 'provider' => 9, 'health_point' => 0,    'is_dormant' => 0, 'days_ago' => 50, 'login_offset' => 5],
            ['username' => '정하윤', 'phone' => '010-2001-0005', 'age' => 29, 'sex' => 'F', 'job' => '프리랜서',   'where_from' => 3, 'provider' => 3, 'health_point' => 800,  'is_dormant' => 1, 'days_ago' => 47, 'login_offset' => 45],
            ['username' => '강도현', 'phone' => '010-2001-0006', 'age' => 36, 'sex' => 'M', 'job' => '회사원',     'where_from' => 2, 'provider' => 9, 'health_point' => 450,  'is_dormant' => 1, 'days_ago' => 44, 'login_offset' => 40],
            ['username' => '윤서아', 'phone' => '010-2001-0007', 'age' => 25, 'sex' => 'F', 'job' => '디자이너',   'where_from' => 2, 'provider' => 3, 'health_point' => 1800, 'is_dormant' => 1, 'days_ago' => 41, 'login_offset' => 39],
            ['username' => '임재현', 'phone' => '010-2001-0008', 'age' => 32, 'sex' => 'M', 'job' => '개발자',     'where_from' => 3, 'provider' => 2, 'health_point' => 600,  'is_dormant' => 1, 'days_ago' => 38, 'login_offset' => 35],
            ['username' => '한예린', 'phone' => '010-2001-0009', 'age' => 28, 'sex' => 'F', 'job' => '간호사',     'where_from' => 2, 'provider' => 9, 'health_point' => 1200, 'is_dormant' => 1, 'days_ago' => 35, 'login_offset' => 33],
            ['username' => '오민석', 'phone' => '010-2001-0010', 'age' => 45, 'sex' => 'M', 'job' => '자영업',     'where_from' => 1, 'provider' => 9, 'health_point' => 0,    'is_dormant' => 0, 'days_ago' => 33, 'login_offset' => 3],
            ['username' => '서지우', 'phone' => '010-2001-0011', 'age' => 22, 'sex' => 'F', 'job' => '대학생',     'where_from' => 2, 'provider' => 3, 'health_point' => 2500, 'is_dormant' => 1, 'days_ago' => 30, 'login_offset' => 29],
            ['username' => '신동욱', 'phone' => '010-2001-0012', 'age' => 38, 'sex' => 'M', 'job' => '회사원',     'where_from' => 3, 'provider' => 2, 'health_point' => 350,  'is_dormant' => 1, 'days_ago' => 27, 'login_offset' => 24],
            ['username' => '권나연', 'phone' => '010-2001-0013', 'age' => 31, 'sex' => 'F', 'job' => '마케터',     'where_from' => 2, 'provider' => 9, 'health_point' => 950,  'is_dormant' => 1, 'days_ago' => 24, 'login_offset' => 22],
            ['username' => '배준영', 'phone' => '010-2001-0014', 'age' => 26, 'sex' => 'M', 'job' => '대학원생',   'where_from' => 3, 'provider' => 3, 'health_point' => 1100, 'is_dormant' => 1, 'days_ago' => 21, 'login_offset' => 19],
            ['username' => '문가은', 'phone' => '010-2001-0015', 'age' => 30, 'sex' => 'F', 'job' => '교사',       'where_from' => 2, 'provider' => 9, 'health_point' => 700,  'is_dormant' => 1, 'days_ago' => 18, 'login_offset' => 16],
            ['username' => '조성민', 'phone' => '010-2001-0016', 'age' => 39, 'sex' => 'M', 'job' => '전문직',     'where_from' => 1, 'provider' => 9, 'health_point' => 0,    'is_dormant' => 0, 'days_ago' => 15, 'login_offset' => 2],
            ['username' => '홍수빈', 'phone' => '010-2001-0017', 'age' => 24, 'sex' => 'F', 'job' => '회사원',     'where_from' => 2, 'provider' => 2, 'health_point' => 1600, 'is_dormant' => 1, 'days_ago' => 12, 'login_offset' => 11],
            ['username' => '곽태양', 'phone' => '010-2001-0018', 'age' => 33, 'sex' => 'M', 'job' => '자영업',     'where_from' => 3, 'provider' => 3, 'health_point' => 500,  'is_dormant' => 1, 'days_ago' => 9,  'login_offset' => 7],
            ['username' => '남유나', 'phone' => '010-2001-0019', 'age' => 27, 'sex' => 'F', 'job' => '승무원',     'where_from' => 2, 'provider' => 9, 'health_point' => 2000, 'is_dormant' => 1, 'days_ago' => 5,  'login_offset' => 4],
            ['username' => '류현우', 'phone' => '010-2001-0020', 'age' => 35, 'sex' => 'M', 'job' => '개발자',     'where_from' => 3, 'provider' => 2, 'health_point' => 250,  'is_dormant' => 1, 'days_ago' => 2,  'login_offset' => 1],
        ];
    }
}
