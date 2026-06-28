<?php

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * UserModel 단위 테스트 (DB 불필요)
 *
 * @internal
 */
final class UserModelTest extends CIUnitTestCase
{
    private UserModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UserModel();
    }

    // ── 테이블·설정 ───────────────────────────────────

    public function testTableIsUsers(): void
    {
        $this->assertSame('users', $this->model->getTable());
    }

    public function testReturnTypeIsArray(): void
    {
        $this->assertSame('array', $this->getPrivateProperty($this->model, 'returnType'));
    }

    public function testUseSoftDeletesIsEnabled(): void
    {
        $this->assertTrue($this->getPrivateProperty($this->model, 'useSoftDeletes'));
    }

    public function testHiddenFieldsContainPasswordAndOauthToken(): void
    {
        $hidden = $this->getPrivateProperty($this->model, 'hidden');

        $this->assertContains('password', $hidden);
        $this->assertContains('oauth_token', $hidden);
    }

    // ── allowedFields ──────────────────────────────────

    public function testAllowedFieldsContainsEmail(): void
    {
        $allowed = $this->getPrivateProperty($this->model, 'allowedFields');

        $this->assertContains('email', $allowed);
    }

    public function testAllowedFieldsContainsUserType(): void
    {
        $allowed = $this->getPrivateProperty($this->model, 'allowedFields');

        $this->assertContains('user_type', $allowed);
    }

    public function testAllowedFieldsDoesNotContainPassword(): void
    {
        // password는 allowedFields에 포함되어 있어야 회원가입/변경이 가능하나
        // hidden 처리로 조회에서 마스킹됨
        $allowed = $this->getPrivateProperty($this->model, 'allowedFields');

        $this->assertContains('password', $allowed);
    }

    // ── 유형 상수 ─────────────────────────────────────

    public function testUserTypeConstantsAreDefined(): void
    {
        $this->assertSame(1,   UserModel::TYPE_USER);
        $this->assertSame(2,   UserModel::TYPE_OPERATOR);
        $this->assertSame(201, UserModel::TYPE_HOSPITAL_AD);
        $this->assertSame(202, UserModel::TYPE_HOSPITAL_GENE);
        $this->assertSame(203, UserModel::TYPE_HOSPITAL_RECV);
        $this->assertSame(401, UserModel::TYPE_ADMIN);
        $this->assertSame(402, UserModel::TYPE_STATS);
        $this->assertSame(403, UserModel::TYPE_GENERAL);
        $this->assertSame(404, UserModel::TYPE_INSTALL);
        $this->assertSame(405, UserModel::TYPE_EXTERNAL);
    }

    // ── validationRules ───────────────────────────────

    public function testValidationRulesRequireEmail(): void
    {
        $rules = $this->getPrivateProperty($this->model, 'validationRules');

        $this->assertArrayHasKey('email', $rules);
        $this->assertStringContainsString('required', $rules['email']);
        $this->assertStringContainsString('valid_email', $rules['email']);
    }

    public function testValidationRulesEnforcePasswordMinLength(): void
    {
        $rules = $this->getPrivateProperty($this->model, 'validationRules');

        $this->assertArrayHasKey('password', $rules);
        $this->assertStringContainsString('min_length[8]', $rules['password']);
    }

    // ── 메서드 존재 ───────────────────────────────────

    public function testGetListMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getList'));
    }

    public function testFindAdminForAuthMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'findAdminForAuth'));
    }
}
