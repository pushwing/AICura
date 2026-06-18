<?php

namespace App\Controllers\Api\V1;

use App\Libraries\JwtLibrary;
use App\Models\UserModel;
use OpenApi\Attributes as OA;

class AuthController extends BaseApiController
{
    #[OA\Post(
        path: '/auth/login',
        summary: '로그인 및 JWT 발급',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email',    type: 'string', format: 'email', example: 'user@aicura.io'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret1234'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '로그인 성공',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'access_token',  type: 'string'),
                            new OA\Property(property: 'refresh_token', type: 'string'),
                            new OA\Property(property: 'token_type',    type: 'string', example: 'Bearer'),
                            new OA\Property(property: 'expires_in',    type: 'integer', example: 3600),
                        ], type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: '이메일 또는 비밀번호 불일치'),
            new OA\Response(response: 422, description: '유효성 검사 실패'),
        ]
    )]
    public function login(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $this->request->getJSON(true)['email'])->first();

        if (!$user || !password_verify($this->request->getJSON(true)['password'], $user['password'])) {
            return $this->error('INVALID_CREDENTIALS', '이메일 또는 비밀번호가 올바르지 않습니다.', 401);
        }

        $jwt = new JwtLibrary();

        return $this->success([
            'access_token'  => $jwt->generateAccessToken($user['id']),
            'refresh_token' => $jwt->generateRefreshToken($user['id']),
            'token_type'    => 'Bearer',
            'expires_in'    => 3600,
        ]);
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
    public function refresh(): \CodeIgniter\HTTP\ResponseInterface
    {
        $refreshToken = $this->request->getJSON(true)['refresh_token'] ?? '';

        $jwt     = new JwtLibrary();
        $payload = $jwt->validateRefreshToken($refreshToken);

        if (!$payload) {
            return $this->error('INVALID_TOKEN', '유효하지 않은 리프레시 토큰입니다.', 401);
        }

        return $this->success([
            'access_token' => $jwt->generateAccessToken($payload['sub']),
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
    public function logout(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->success(null);
    }
}
