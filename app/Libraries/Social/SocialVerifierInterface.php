<?php

namespace App\Libraries\Social;

use App\Exceptions\AuthException;

/**
 * 소셜 로그인 토큰 검증 계약 (이슈 #187)
 *
 * 클라이언트가 보낸 access_token 을 제공자(Kakao/Naver) API 로 검증해
 * 신뢰 가능한 사용자 프로필(uid 등)을 반환한다. 검증 실패 시 AuthException 을 던진다.
 * 인터페이스로 분리해 테스트에서 실제 HTTP 호출 없이 대체(주입)할 수 있게 한다.
 */
interface SocialVerifierInterface
{
    /**
     * @param string $provider 'naver' | 'kakao'
     * @param string $accessToken 소셜 제공자 발급 액세스 토큰
     * @throws AuthException 지원하지 않는 제공자·토큰 검증 실패
     */
    public function verify(string $provider, string $accessToken): SocialProfile;
}
