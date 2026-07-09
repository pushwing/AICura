<?php

use App\Exceptions\AuthException;
use App\Libraries\Social\SocialProfile;
use App\Libraries\Social\SocialVerifierInterface;
use App\Models\UserModel;
use App\Services\AppAuthService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

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
 *   [A11] 소셜 토큰 검증 실패 → 401 SOCIAL_AUTH_FAILED, 계정 미생성 (이슈 #187)
 *   [A12] access_token 누락 → 422 VALIDATION_ERROR (이슈 #187)
 *
 * @internal
 */
final class AppAuthFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $refresh = true;
    protected $namespace;

    /**
     * [A1] 회원가입 성공
     */
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

    /**
     * [A2] 중복 이메일 가입 차단
     */
    public function testRegisterDuplicateEmailFails(): void
    {
        $payload = ['email' => 'dup@aicura.test', 'password' => 'password1234'];
        $this->withBodyFormat('json')->post('api/v1/auth/register', $payload);

        $result = $this->withBodyFormat('json')->post('api/v1/auth/register', $payload);
        $result->assertStatus(409);
        $this->assertSame('ALREADY_EXISTS', json_decode($result->getJSON(), true)['code']);
    }

    /**
     * [A3] 유효성 검사 실패
     */
    public function testRegisterInvalidEmailFails(): void
    {
        $result = $this->withBodyFormat('json')->post('api/v1/auth/register', [
            'email'    => 'not-an-email',
            'password' => 'password1234',
        ]);
        $result->assertStatus(422);
        $this->assertSame('VALIDATION_ERROR', json_decode($result->getJSON(), true)['code']);
    }

    /**
     * [A4] 로그인 성공
     */
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

    /**
     * [A5] 비밀번호 불일치
     */
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

    /**
     * [A6] 비소비자(운영자) 계정은 앱 로그인 불가
     */
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

    /**
     * [A7][A8] 소셜 로그인 신규 생성 + 재로그인 중복 없음 (검증기는 uid 999888 반환하도록 주입)
     */
    public function testSocialLoginCreatesThenReuses(): void
    {
        $payload = ['provider' => 'kakao', 'access_token' => 'valid-kakao-token', 'where_from' => 3];

        // 실제 HTTP 호출 없이 검증 성공을 시뮬레이션 — 제공자가 보증한 uid 를 반환.
        // FeatureTestTrait 는 요청마다 서비스를 리셋하므로 각 요청 직전에 재주입한다.
        $this->injectSocialVerifier(new SocialProfile(uid: '999888', username: '카카오유저'));
        $first = $this->withBodyFormat('json')->post('api/v1/auth/social', $payload);
        $first->assertStatus(200);
        $this->assertArrayHasKey('access_token', json_decode($first->getJSON(), true)['data']);

        $this->injectSocialVerifier(new SocialProfile(uid: '999888', username: '카카오유저'));
        $second = $this->withBodyFormat('json')->post('api/v1/auth/social', $payload);
        $second->assertStatus(200);

        // provider(3=kakao) + uid 조합 계정은 1건만 존재
        $count = model(UserModel::class)->where('provider', 3)->where('uid', '999888')->countAllResults();
        $this->assertSame(1, $count);
    }

    /**
     * [A11] 소셜 토큰 검증 실패 시 401 + 계정 미생성 (이슈 #187 인증 우회 차단)
     */
    public function testSocialLoginRejectsInvalidToken(): void
    {
        // 검증기가 실패를 던지도록 주입 — 위조·만료 토큰을 흉내낸다.
        $this->injectSocialVerifier(null);

        $before = model(UserModel::class)->where('provider', 3)->countAllResults();

        $result = $this->withBodyFormat('json')->post('api/v1/auth/social', [
            'provider'     => 'kakao',
            'access_token' => 'forged-or-expired-token',
        ]);

        $result->assertStatus(401);
        $this->assertSame('SOCIAL_AUTH_FAILED', json_decode($result->getJSON(), true)['code']);

        // 검증 실패 시 계정이 생성되면 안 된다.
        $after = model(UserModel::class)->where('provider', 3)->countAllResults();
        $this->assertSame($before, $after);
    }

    /**
     * [A12] access_token 누락 → 422 (uid 만으로는 로그인 불가)
     */
    public function testSocialLoginRequiresAccessToken(): void
    {
        $result = $this->withBodyFormat('json')->post('api/v1/auth/social', [
            'provider' => 'kakao',
            'uid'      => '999888', // 더 이상 신뢰되지 않는 필드
        ]);

        $result->assertStatus(422);
        $this->assertSame('VALIDATION_ERROR', json_decode($result->getJSON(), true)['code']);
    }

    /**
     * 소셜 검증기를 가짜로 주입한다.
     * $profile 이 주어지면 검증 성공(해당 프로필 반환), null 이면 검증 실패(SOCIAL_AUTH_FAILED)를 던진다.
     *
     * 실제 HTTP 호출을 차단하기 위해 가짜 검증기를 담은 AppAuthService 를 통째로 주입한다.
     * (socialTokenVerifier 만 주입하면, 앞선 테스트가 이미 캐시한 공유 appAuthService 에는 반영되지 않는다.)
     */
    private function injectSocialVerifier(?SocialProfile $profile): void
    {
        $fake = new class ($profile) implements SocialVerifierInterface {
            public function __construct(private readonly ?SocialProfile $profile)
            {
            }

            public function verify(string $provider, string $accessToken): SocialProfile
            {
                if ($this->profile === null) {
                    throw AuthException::socialVerificationFailed();
                }

                return $this->profile;
            }
        };

        Services::injectMock('socialTokenVerifier', $fake);
        Services::injectMock('appAuthService', new AppAuthService(socialVerifier: $fake));
    }

    /**
     * [A10] 대소문자가 다른 이메일로도 로그인 성공 — 가입·로그인 정규화 일치
     */
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

    /**
     * [A9] 이메일 중복 확인
     */
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
