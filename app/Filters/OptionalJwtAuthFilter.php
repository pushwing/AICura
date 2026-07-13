<?php

declare(strict_types=1);

namespace App\Filters;

use App\Exceptions\TokenException;
use App\Libraries\Auth;
use App\Libraries\JwtLibrary;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 선택적 JWT 인증 필터.
 *
 * 이벤트 조회처럼 비로그인도 허용하되, 로그인 사용자는 부가 정보(is_liked 등)를
 * 받도록 한다. Bearer 토큰이 유효하면 사용자 ID를 홀더에 저장하고, 없거나
 * 무효하면 401 없이 익명으로 통과시킨다.
 */
class OptionalJwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (! str_starts_with($authHeader, 'Bearer ')) {
            return null; // 익명 통과
        }

        $token = substr($authHeader, 7);

        try {
            $payload = (new JwtLibrary())->validateAccessToken($token);
            Auth::setUserId((int) $payload['sub']);
        } catch (TokenException) {
            // 만료·무효 토큰은 익명으로 처리 (열람은 허용)
            return null;
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
