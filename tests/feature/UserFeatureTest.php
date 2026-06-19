<?php

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 사용자 관리 피처 테스트 (SQLite3 인메모리 DB)
 *
 * 테스트 플랜 커버리지:
 *   [F1]  미인증 index 접근 → /admin/login 리다이렉트
 *   [F2]  미인증 show 접근 → /admin/login 리다이렉트
 *   [F3]  인증 후 index 200 + AG Grid div
 *   [F4]  index type=2 (운영자 탭) → 200
 *   [F5]  index type=3 (광고주/병원 탭) → 200
 *   [F6]  index is_dormant 필터 → 200
 *   [F7]  index search_word 필터 → 200
 *   [F8]  show 존재하지 않는 ID → PageNotFoundException
 *   [F9]  show 존재하는 사용자 → 200 + 이메일 표시
 *   [F10] index 잘못된 type 값 → 1로 보정 (200)
 *
 * @internal
 */
final class UserFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private const ADMIN_SESSION = ['admin_user' => ['id' => 1, 'email' => 'admin@test.com', 'username' => 'admin']];

    private int $testUserId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $now = date('Y-m-d H:i:s');
        db_connect()->table('users')->insert([
            'email'      => '__feature_user__@test.invalid',
            'user_type'  => UserModel::TYPE_USER,
            'is_dormant' => 1,
            'is_active'  => 1,
            'username'   => '__feature_user__',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->testUserId = (int) db_connect()->insertID();
    }

    protected function tearDown(): void
    {
        if ($this->testUserId > 0) {
            db_connect()->table('users')->where('id', $this->testUserId)->delete();
        }

        parent::tearDown();
    }

    // ── [F1] 미인증 index 접근 ────────────────────────

    public function testIndexRedirectsWhenNotAuthenticated(): void
    {
        $result = $this->get('/admin/users');

        $result->assertRedirectTo('/admin/login');
    }

    // ── [F2] 미인증 show 접근 ─────────────────────────

    public function testShowRedirectsWhenNotAuthenticated(): void
    {
        $result = $this->get('/admin/users/' . $this->testUserId);

        $result->assertRedirectTo('/admin/login');
    }

    // ── [F3] 인증 후 index 200 + AG Grid ─────────────

    public function testIndexReturns200WithAuth(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/users');

        $result->assertStatus(200);
        $result->assertSee('userGrid');
    }

    public function testIndexContainsUserManagementTitle(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/users');

        $result->assertStatus(200);
        $result->assertSee('사용자 관리');
    }

    // ── [F4] type=2 운영자 탭 ────────────────────────

    public function testIndexType2Returns200(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/users?type=2');

        $result->assertStatus(200);
    }

    // ── [F5] type=3 광고주/병원 탭 ───────────────────

    public function testIndexType3Returns200(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/users?type=3');

        $result->assertStatus(200);
    }

    // ── [F6] is_dormant 필터 ──────────────────────────

    public function testIndexFilterByIsDormant(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/users?is_dormant=1');

        $result->assertStatus(200);
    }

    // ── [F7] search_word 필터 ─────────────────────────

    public function testIndexFilterBySearchWord(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/users?search_word=__feature_user__');

        $result->assertStatus(200);
        $result->assertSee('__feature_user__@test.invalid');
    }

    // ── [F8] show 존재하지 않는 ID ────────────────────

    public function testShowThrowsPageNotFoundForNonExistentUser(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);

        $this->withSession(self::ADMIN_SESSION)
             ->get('/admin/users/9999999');
    }

    // ── [F9] show 존재하는 사용자 200 ─────────────────

    public function testShowReturns200WithValidUser(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/users/' . $this->testUserId);

        $result->assertStatus(200);
        $result->assertSee('__feature_user__@test.invalid');
    }

    public function testShowDisplaysUserType(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/users/' . $this->testUserId);

        $result->assertStatus(200);
        $result->assertSee('일반 사용자');
    }

    // ── [F10] type 범위 초과 → 1로 보정 ──────────────

    public function testIndexClampsTypeOutOfRange(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/users?type=99');

        $result->assertStatus(200);
        $result->assertSee('userGrid');
    }
}
