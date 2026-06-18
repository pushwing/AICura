<?php

namespace App\Filters;

use App\Libraries\Auth;
use App\Libraries\JwtLibrary;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'code' => 'UNAUTHORIZED', 'message' => '인증이 필요합니다.']);
        }

        $token = substr($authHeader, 7);
        $jwt   = new JwtLibrary();
        $payload = $jwt->validateAccessToken($token);

        if (!$payload) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'code' => 'TOKEN_EXPIRED', 'message' => '토큰이 만료되었거나 유효하지 않습니다.']);
        }

        Auth::setUserId((int) $payload['sub']);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
