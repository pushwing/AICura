<?php

namespace App\Filters;

use App\Exceptions\TokenException;
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

        try {
            // 만료(TOKEN_EXPIRED)와 무효(INVALID_TOKEN)를 구분해 응답한다.
            $payload = new JwtLibrary()->validateAccessToken($token);
        } catch (TokenException $e) {
            return service('response')
                ->setStatusCode($e->httpStatusCode())
                ->setJSON(['status' => 'error', 'code' => $e->errorCode(), 'message' => $e->getMessage()]);
        }

        Auth::setUserId((int) $payload['sub']);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
