<?php

namespace App\Filters;

use App\Exceptions\TokenException;
use App\Libraries\Auth;
use App\Libraries\JwtLibrary;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        Auth::clear();
        $authHeader = $request->getHeaderLine('Authorization');

        if (! str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'code' => 'UNAUTHORIZED', 'message' => '인증이 필요합니다.']);
        }

        $token = substr($authHeader, 7);

        try {
            // 만료(TOKEN_EXPIRED)와 무효(INVALID_TOKEN)를 구분해 응답한다.
            $payload = (new JwtLibrary())->validateAccessToken($token);
        } catch (TokenException $e) {
            return service('response')
                ->setStatusCode($e->httpStatusCode())
                ->setJSON(['status' => 'error', 'code' => $e->errorCode(), 'message' => $e->getMessage()]);
        }

        $userId = (int) $payload['sub'];
        if (! model(UserModel::class)->isActiveAppUser($userId, (int) ($payload['ver'] ?? 0))) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'code' => 'UNAUTHORIZED', 'message' => '사용할 수 없는 계정입니다.']);
        }

        Auth::setUserId($userId);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        Auth::clear();

        return null;
    }
}
