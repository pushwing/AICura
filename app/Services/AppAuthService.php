<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Libraries\JwtLibrary;
use App\Models\UserModel;

/**
 * 외부(소비자) 앱 인증 서비스 (이슈 #96)
 *
 * 일반 사용자(user_type=1) 한정으로 이메일/소셜 로그인·회원가입·이메일 중복 확인을 담당한다.
 * 컨트롤러는 검증된 입력만 전달하고, 토큰 발급·계정 생성 등 유스케이스는 본 서비스가 책임진다.
 *
 * 앱은 모바일(iOS/Android) 전용이므로 where_from 은 2(iOS)/3(Android)만 허용한다(웹=1 미사용).
 */
class AppAuthService
{
    private const ACCESS_TTL = 3600;

    /** 지원 소셜 제공자 → users.provider 코드 매핑 */
    private const PROVIDER_MAP = [
        'naver' => 2,
        'kakao' => 3,
    ];

    private UserModel $users;
    private JwtLibrary $jwt;
    private HealthPointService $points;

    public function __construct(
        ?UserModel $users = null,
        ?JwtLibrary $jwt = null,
        ?HealthPointService $points = null,
    ) {
        $this->users  = $users  ?? model(UserModel::class);
        $this->jwt    = $jwt    ?? new JwtLibrary();
        $this->points = $points ?? service('healthPointService');
    }

    /**
     * 이메일 로그인 — 소비자(user_type=1) 한정
     *
     * @return array<string, mixed> 토큰 번들
     */
    public function loginWithEmail(string $email, string $password): array
    {
        $user = $this->users->findAppUserForAuth($this->normalizeEmail($email));

        if ($user === null || !is_string($user['password']) || !password_verify($password, $user['password'])) {
            throw AuthException::invalidCredentials();
        }

        if ((int) $user['is_active'] !== 1) {
            throw AuthException::accountInactive();
        }

        return $this->finishLogin((int) $user['id']);
    }

    /**
     * 이메일 회원가입 — 소비자 자가가입 (가입 후 자동 로그인 토큰 발급)
     *
     * @param array<string, mixed> $input email·password·username·phone·age·sex·where_from
     * @return array<string, mixed> 토큰 번들
     */
    public function register(array $input): array
    {
        $email = $this->normalizeEmail((string) $input['email']);

        if ($this->users->emailExists($email)) {
            throw AuthException::emailAlreadyExists();
        }

        $userId = $this->createUser([
            'email'      => $email,
            'password'   => (string) $input['password'],
            'username'   => $this->nullableString($input['username'] ?? null),
            'phone'      => $this->nullableString($input['phone'] ?? null),
            'age'        => isset($input['age']) ? (int) $input['age'] : null,
            'sex'        => $this->nullableString($input['sex'] ?? null),
            'where_from' => $this->normalizeWhereFrom($input['where_from'] ?? null),
            'provider'   => 9, // 이메일
        ]);

        return $this->finishLogin($userId);
    }

    /**
     * 소셜 로그인 — provider+uid 로 식별, 미가입 시 자동 가입
     *
     * 이메일 미동의 케이스를 고려해 계정 식별은 provider+uid 로만 하고,
     * email 컬럼(UNIQUE·NOT NULL)에는 충돌 없는 안정적 합성 주소를 저장한다.
     *
     * @param array<string, mixed> $input provider('naver'|'kakao')·uid·username·picture·where_from
     * @return array<string, mixed> 토큰 번들
     */
    public function socialLogin(array $input): array
    {
        $provider = $this->normalizeProvider((string) $input['provider']);
        $uid      = trim((string) $input['uid']);

        if ($uid === '') {
            throw AuthException::unsupportedProvider();
        }

        $user = $this->users->findAppUserByProviderUid($provider, $uid);

        if ($user !== null) {
            if ((int) $user['is_active'] !== 1) {
                throw AuthException::accountInactive();
            }

            return $this->finishLogin((int) $user['id']);
        }

        $userId = $this->createUser([
            'email'      => sprintf('social_%d_%s@aicura.app', $provider, $uid),
            'username'   => $this->nullableString($input['username'] ?? null),
            'picture'    => $this->nullableString($input['picture'] ?? null),
            'where_from' => $this->normalizeWhereFrom($input['where_from'] ?? null),
            'provider'   => $provider,
            'uid'        => $uid,
        ]);

        return $this->finishLogin($userId);
    }

    /**
     * 이메일 사용 가능 여부 (가입 가능 = true)
     */
    public function isEmailAvailable(string $email): bool
    {
        return !$this->users->emailExists($this->normalizeEmail($email));
    }

    /**
     * 계정 생성 위임 — 모델의 저수준 실패(RuntimeException)를 도메인 예외로 변환한다.
     *
     * emailExists·provider+uid 사전 검사를 통과한 뒤의 insert 실패는 예외적 상황이므로,
     * 내부 오류 상세는 서버 로그로만 남기고 클라이언트에는 안전한 메시지를 반환한다.
     *
     * @param array<string, mixed> $data
     */
    private function createUser(array $data): int
    {
        try {
            $userId = $this->users->createAppUser($data);
        } catch (\RuntimeException $e) {
            log_message('error', '[AppAuthService] 앱 계정 생성 실패: {message}', ['message' => $e->getMessage()]);

            throw AuthException::registrationFailed();
        }

        // 가입 적립 — 포인트 적립 실패가 가입 자체를 막지 않도록 로깅만 하고 진행한다.
        try {
            $this->points->awardSignup($userId);
        } catch (\Throwable $e) {
            log_message('error', '[AppAuthService] 가입 적립 실패 (user {id}): {message}', [
                'id'      => $userId,
                'message' => $e->getMessage(),
            ]);
        }

        return $userId;
    }

    /**
     * 로그인 마무리 — 로그인 시각 갱신 후 토큰 발급
     *
     * @return array<string, mixed>
     */
    private function finishLogin(int $userId): array
    {
        $this->users->touchLogin($userId);

        return [
            'access_token'  => $this->jwt->generateAccessToken($userId),
            'refresh_token' => $this->jwt->generateRefreshToken($userId),
            'token_type'    => 'Bearer',
            'expires_in'    => self::ACCESS_TTL,
        ];
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function normalizeProvider(string $provider): int
    {
        $key = strtolower(trim($provider));

        if (!isset(self::PROVIDER_MAP[$key])) {
            throw AuthException::unsupportedProvider();
        }

        return self::PROVIDER_MAP[$key];
    }

    /**
     * where_from 정규화 — 모바일(2 iOS · 3 Android)만 허용, 그 외/누락은 2(iOS) 기본값
     */
    private function normalizeWhereFrom(mixed $value): int
    {
        $int = (int) $value;

        return in_array($int, [2, 3], true) ? $int : 2;
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
