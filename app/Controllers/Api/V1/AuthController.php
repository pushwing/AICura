<?php

namespace App\Controllers\Api\V1;

use App\Exceptions\DomainException;
use App\Libraries\JwtLibrary;
use App\Services\AppAuthService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use OpenApi\Attributes as OA;

/**
 * 외부(소비자) 앱 인증 컨트롤러 (이슈 #96)
 *
 * 일반 사용자(user_type=1) 한정 — 이메일/소셜 로그인·회원가입·토큰 갱신.
 * 비즈니스 로직은 AppAuthService 가 담당하고, 컨트롤러는 입력 검증·응답 변환만 수행한다.
 */
class AuthController extends BaseApiController
{
    private AppAuthService $auth;

    public function __construct()
    {
        $this->auth = Services::appAuthService();
    }

    #[OA\Post(
        path: '/auth/login',
        summary: '이메일 로그인 및 JWT 발급 (소비자 전용)',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email',    type: 'string', format: 'email', example: 'appuser01@aicura.test'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password1234'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: '로그인 성공'),
            new OA\Response(response: 401, description: '이메일 또는 비밀번호 불일치'),
            new OA\Response(response: 422, description: '유효성 검사 실패'),
        ]
    )]
    public function login(): ResponseInterface
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        $input = $this->json();

        try {
            $tokens = $this->auth->loginWithEmail((string) $input['email'], (string) $input['password']);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($tokens);
    }

    #[OA\Post(
        path: '/auth/register',
        summary: '이메일 회원가입 (소비자 자가가입)',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email',      type: 'string', format: 'email', example: 'newuser@aicura.test'),
                    new OA\Property(property: 'password',   type: 'string', format: 'password', example: 'password1234'),
                    new OA\Property(property: 'username',   type: 'string', example: '홍길동'),
                    new OA\Property(property: 'phone',      type: 'string', example: '01012345678'),
                    new OA\Property(property: 'age',        type: 'integer', example: 29),
                    new OA\Property(property: 'sex',        type: 'string', example: 'F'),
                    new OA\Property(property: 'where_from', type: 'integer', description: '2 iOS · 3 Android', example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: '가입 성공 (자동 로그인 토큰 발급)'),
            new OA\Response(response: 409, description: '이미 가입된 이메일'),
            new OA\Response(response: 422, description: '유효성 검사 실패'),
        ]
    )]
    public function register(): ResponseInterface
    {
        $rules = [
            'email'      => 'required|valid_email|max_length[255]',
            'password'   => 'required|min_length[8]|max_length[72]',
            'username'   => 'permit_empty|max_length[100]',
            'phone'      => 'permit_empty|max_length[30]',
            'age'        => 'permit_empty|is_natural_no_zero|less_than[150]',
            'sex'        => 'permit_empty|max_length[10]',
            'where_from' => 'permit_empty|in_list[2,3]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        try {
            $tokens = $this->auth->register($this->json());
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($tokens, [], 201);
    }

    #[OA\Post(
        path: '/auth/social',
        summary: '소셜 로그인 (Naver/Kakao) — 미가입 시 자동 가입',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['provider', 'uid'],
                properties: [
                    new OA\Property(property: 'provider',   type: 'string', enum: ['naver', 'kakao'], example: 'kakao'),
                    new OA\Property(property: 'uid',        type: 'string', description: '소셜 제공자 고유 ID', example: '1234567890'),
                    new OA\Property(property: 'username',   type: 'string', example: '홍길동'),
                    new OA\Property(property: 'picture',    type: 'string', example: 'https://...'),
                    new OA\Property(property: 'where_from', type: 'integer', description: '2 iOS · 3 Android', example: 3),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: '로그인 성공'),
            new OA\Response(response: 403, description: '이용이 제한된 계정'),
            new OA\Response(response: 422, description: '유효성 검사 실패'),
        ]
    )]
    public function social(): ResponseInterface
    {
        $rules = [
            'provider' => 'required|in_list[naver,kakao]',
            'uid'      => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        try {
            $tokens = $this->auth->socialLogin($this->json());
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($tokens);
    }

    #[OA\Post(
        path: '/auth/check-email',
        summary: '이메일 중복 확인',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'newuser@aicura.test'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: '확인 성공 (available: 가입 가능 여부)'),
            new OA\Response(response: 422, description: '유효성 검사 실패'),
        ]
    )]
    public function checkEmail(): ResponseInterface
    {
        if (!$this->validate(['email' => 'required|valid_email'])) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        $available = $this->auth->isEmailAvailable((string) $this->json()['email']);

        return $this->success(['available' => $available]);
    }

    #[OA\Post(
        path: '/auth/refresh',
        summary: 'Access Token 갱신',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['refresh_token'],
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: '토큰 갱신 성공'),
            new OA\Response(response: 401, description: '유효하지 않은 리프레시 토큰'),
        ]
    )]
    public function refresh(): ResponseInterface
    {
        $refreshToken = (string) ($this->json()['refresh_token'] ?? '');

        $jwt = new JwtLibrary();

        try {
            // 만료(TOKEN_EXPIRED)와 무효(INVALID_TOKEN)를 구분해 응답한다.
            $payload = $jwt->validateRefreshToken($refreshToken);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success([
            'access_token' => $jwt->generateAccessToken((int) $payload['sub']),
            'token_type'   => 'Bearer',
            'expires_in'   => 3600,
        ]);
    }

    #[OA\Post(
        path: '/auth/logout',
        summary: '로그아웃',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: '로그아웃 성공'),
            new OA\Response(response: 401, description: '인증 필요'),
        ]
    )]
    public function logout(): ResponseInterface
    {
        return $this->success(null);
    }

    /**
     * 요청 JSON 본문을 연관 배열로 반환
     *
     * @return array<string, mixed>
     */
    private function json(): array
    {
        $data = $this->request->getJSON(true);

        return is_array($data) ? $data : [];
    }
}
