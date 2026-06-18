<?php

namespace App\Controllers\Api\V1;

use App\Libraries\JwtLibrary;
use App\Models\UserModel;

class AuthController extends BaseApiController
{
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

    public function refresh(): \CodeIgniter\HTTP\ResponseInterface
    {
        $refreshToken = $this->request->getJSON(true)['refresh_token'] ?? '';

        $jwt = new JwtLibrary();
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

    public function logout(): \CodeIgniter\HTTP\ResponseInterface
    {
        // 필요 시 refresh_token DB 폐기 처리
        return $this->success(null);
    }
}
