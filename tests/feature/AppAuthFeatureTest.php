<?php

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 외부(소비자) 앱 인증 API 피처 테스트 (이슈 #96, SQLite3 인메모리 DB)
 *
 * 커버리지:
 *   [A1]  회원가입 성공 → 201 + access_token
 *   [A2]  중복 이메일 가입 → 409 ALREADY_EXISTS
 *   [A3]  잘못된 이메일 → 422 VALIDATION_ERROR
 *   [A4]  가입 후 로그인 성공 → 200 + 토큰
 *   [A5]  비밀번호 불일치 → 401 INVALID_CREDENTIALS
 *   [A6]  비소비자(운영자) 계정 로그인 차단 → 401
 *   [A7]  소셜 신규 로그인 → 200 + 계정 자동 생성
 *   [A8]  동일 provider+uid 재로그인 → 계정 중복 생성 없음
 *   [A9]  이메일 중복 확인 (available true/false)
 *
 * @internal
 */
final class AppAuthFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    /** [A1] 회원가입 성공 */
    public function testRegisterSucceeds(): void
    {
        $result = $this->withBodyFormat('json')->post('api/v1/auth/register', [
            'email'      => 'newuser@aicura.test',
            'password'   => 'password1234',
            'username'   => '홍길동',
            'where_from' => 2,
        ]);

        $result->assertStatus(201);
        $body = json_decode($result->getJSON(), true);
        $this->assertSame('success', $body['status']);
        $this->assertArrayHasKey('access_token', $body['data']);
        $this->assertArrayHasKey('refresh_token', $body['data']);

        $this->seeInDatabase('users', [
            'email'     => 'newuser@aicura.test',
            'user_type' => UserModel::TYPE_USER,
            'provider'  => 9,
        ]);
    }

    /** [A2] 중복 이메일 가입 차단 */
    public function testRegisterDuplicateEmailFails(): void
    {
        $payload = ['email' => 'dup@aicura.test', 'password' => 'password1234'];
        $this->withBodyFormat('json')->post('api/v1/auth/register', $payload);

        $result = $this->withBodyFormat('json')->post('api/v1/auth/register', $payload);
        $result->assertStatus(409);
        $this->assertSame('ALREADY_EXISTS', json_decode($result->getJSON(), true)['code']);
    }

    /** [A3] 유효성 검사 실패 */
    public function testRegisterInvalidEmailFails(): void
    {
        $result = $this->withBodyFormat('json')->post('api/v1/auth/register', [
            'email'    => 'not-an-email',
            'password' => 'password1234',
        ]);
        $result->assertStatus(422);
        $this->assertSame('VALIDATION_ERROR', json_decode($result->getJSON(), true)['code']);
    }

    /** [A4] 로그인 성공 */
    public function testLoginSucceeds(): void
    {
        $this->withBodyFormat('json')->post('api/v1/auth/register', [
            'email'    => 'login@aicura.test',
            'password' => 'password1234',
        ]);

        $result = $this->withBodyFormat('json')->post('api/v1/auth/login', [
            'email'    => 'login@aicura.test',
            'password' => 'password1234',
        ]);
        $result->assertStatus(200);
        $this->assertArrayHasKey('access_token', json_decode($result->getJSON(), true)['data']);
    }

    /** [A5] 비밀번호 불일치 */
    public function testLoginWrongPasswordFails(): void
    {
        $this->withBodyFormat('json')->post('api/v1/auth/register', [
            'email'    => 'wrongpw@aicura.test',
            'password' => 'password1234',
        ]);

        $result = $this->withBodyFormat('json')->post('api/v1/auth/login', [
            'email'    => 'wrongpw@aicura.test',
            'password' => 'wrongpassword',
        ]);
        $result->assertStatus(401);
        $this->assertSame('INVALID_CREDENTIALS', json_decode($result->getJSON(), true)['code']);
    }

    /** [A6] 비소비자(운영자) 계정은 앱 로그인 불가 */
    public function testNonConsumerCannotLogin(): void
    {
        model(UserModel::class)->insert([
            'email'     => 'operator@aicura.test',
            'password'  => password_hash('password1234', PASSWORD_DEFAULT),
            'user_type' => UserModel::TYPE_ADMIN,
            'is_active' => 1,
        ]);

        $result = $this->withBodyFormat('json')->post('api/v1/auth/login', [
            'email'    => 'operator@aicura.test',
            'password' => 'password1234',
        ]);
        $result->assertStatus(401);
        $this->assertSame('INVALID_CREDENTIALS', json_decode($result->getJSON(), true)['code']);
    }

    /** [A7][A8] 소셜 로그인 신규 생성 + 재로그인 중복 없음 */
    public function testSocialLoginCreatesThenReuses(): void
    {
        $payload = ['provider' => 'kakao', 'uid' => '999888', 'username' => '카카오유저', 'where_from' => 3];

        $first = $this->withBodyFormat('json')->post('api/v1/auth/social', $payload);
        $first->assertStatus(200);
        $this->assertArrayHasKey('access_token', json_decode($first->getJSON(), true)['data']);

        $second = $this->withBodyFormat('json')->post('api/v1/auth/social', $payload);
        $second->assertStatus(200);

        // provider(3=kakao) + uid 조합 계정은 1건만 존재
        $count = model(UserModel::class)->where('provider', 3)->where('uid', '999888')->countAllResults();
        $this->assertSame(1, $count);
    }

    /** [A10] 대소문자가 다른 이메일로도 로그인 성공 — 가입·로그인 정규화 일치 */
    public function testLoginIsCaseInsensitiveForEmail(): void
    {
        $this->withBodyFormat('json')->post('api/v1/auth/register', [
            'email'    => 'MixedCase@Aicura.test',
            'password' => 'password1234',
        ]);

        $result = $this->withBodyFormat('json')->post('api/v1/auth/login', [
            'email'    => 'MIXEDCASE@AICURA.TEST',
            'password' => 'password1234',
        ]);
        $result->assertStatus(200);
        $this->assertArrayHasKey('access_token', json_decode($result->getJSON(), true)['data']);
    }

    /** [A9] 이메일 중복 확인 */
    public function testCheckEmailAvailability(): void
    {
        $this->withBodyFormat('json')->post('api/v1/auth/register', [
            'email'    => 'taken@aicura.test',
            'password' => 'password1234',
        ]);

        $taken = $this->withBodyFormat('json')->post('api/v1/auth/check-email', ['email' => 'taken@aicura.test']);
        $taken->assertStatus(200);
        $this->assertFalse(json_decode($taken->getJSON(), true)['data']['available']);

        $free = $this->withBodyFormat('json')->post('api/v1/auth/check-email', ['email' => 'free@aicura.test']);
        $free->assertStatus(200);
        $this->assertTrue(json_decode($free->getJSON(), true)['data']['available']);
    }
}
