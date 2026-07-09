<?php

namespace App\Libraries\Social;

use App\Exceptions\AuthException;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * 소셜 로그인 토큰 검증기 (이슈 #187)
 *
 * 클라이언트가 보낸 access_token 을 제공자의 사용자정보 엔드포인트로 호출해 검증한다.
 * 검증에 성공하면 제공자가 보증한 uid 를 담은 SocialProfile 을 반환한다.
 * 외부 라이브러리 없이 CI4 CURLRequest 서비스로 직접 구현한다(JwtLibrary·GroqClient 관례).
 *
 *   - Kakao: GET https://kapi.kakao.com/v2/user/me
 *   - Naver: GET https://openapi.naver.com/v1/nid/me
 *
 * 두 엔드포인트 모두 사용자 access_token(Bearer) 만으로 호출 가능하며,
 * 우리 서버의 앱 시크릿은 필요하지 않다(토큰 자체가 사용자 인증을 증명).
 */
class SocialTokenVerifier implements SocialVerifierInterface
{
    /**
     * 제공자 → 사용자정보 엔드포인트
     */
    private const array ENDPOINTS = [
        'kakao' => 'https://kapi.kakao.com/v2/user/me',
        'naver' => 'https://openapi.naver.com/v1/nid/me',
    ];

    /**
     * 외부 API 호출 타임아웃(초) — CLAUDE.md 외부 호출 기본 5초
     */
    private const int TIMEOUT = 5;

    public function verify(string $provider, string $accessToken): SocialProfile
    {
        $key   = strtolower(trim($provider));
        $token = trim($accessToken);

        if (! isset(self::ENDPOINTS[$key])) {
            throw AuthException::unsupportedProvider();
        }

        if ($token === '') {
            throw AuthException::socialVerificationFailed();
        }

        $data = $this->fetch($key, self::ENDPOINTS[$key], $token);

        // isset(self::ENDPOINTS[$key]) 통과 → $key 는 'kakao' | 'naver' 로 보장된다.
        return match ($key) {
            'kakao' => $this->parseKakao($data),
            'naver' => $this->parseNaver($data),
        };
    }

    /**
     * 제공자 엔드포인트 호출 → 디코드된 JSON 배열 반환. 실패는 검증 실패로 통일.
     *
     * @return array<string, mixed>
     */
    private function fetch(string $provider, string $endpoint, string $token): array
    {
        try {
            $client = service('curlrequest', [
                'baseURI'     => '',
                'timeout'     => self::TIMEOUT,
                'http_errors' => false,
            ]);

            $response = $client->get($endpoint, [
                'headers' => ['Authorization' => 'Bearer ' . $token],
            ]);
        } catch (Throwable $e) {
            // 네트워크 오류·타임아웃 — 검증 불가로 처리하고 상세는 서버 로그로만 남긴다.
            log_message('error', '[SocialTokenVerifier] {provider} 호출 실패: {message}', [
                'provider' => $provider,
                'message'  => $e->getMessage(),
            ]);

            throw AuthException::socialVerificationFailed();
        }

        return $this->decode($provider, $response);
    }

    /**
     * 응답 상태·본문 검증 후 배열 디코드. 2xx 가 아니거나 파싱 실패면 검증 실패.
     *
     * @return array<string, mixed>
     */
    private function decode(string $provider, ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $body   = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            // 만료·위조 토큰은 제공자가 401 을 반환한다. 본문은 토큰 노출 방지를 위해 로깅하지 않는다.
            log_message('warning', '[SocialTokenVerifier] {provider} 토큰 거부 (HTTP {status})', [
                'provider' => $provider,
                'status'   => $status,
            ]);

            throw AuthException::socialVerificationFailed();
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($body, true);

        if (! is_array($decoded)) {
            throw AuthException::socialVerificationFailed();
        }

        return $decoded;
    }

    /**
     * Kakao 응답 파싱 — id(숫자) 필수. 프로필은 동의 항목에 따라 없을 수 있다.
     *
     * @param array<string, mixed> $data
     */
    private function parseKakao(array $data): SocialProfile
    {
        $uid = $this->stringifyId($data['id'] ?? null);

        /** @var array<string, mixed> $profile */
        $profile = $data['kakao_account']['profile'] ?? [];

        return new SocialProfile(
            uid: $uid,
            username: $this->nullableString($profile['nickname'] ?? null),
            picture: $this->nullableString($profile['profile_image_url'] ?? null),
        );
    }

    /**
     * Naver 응답 파싱 — response.id 필수.
     *
     * @param array<string, mixed> $data
     */
    private function parseNaver(array $data): SocialProfile
    {
        /** @var array<string, mixed> $res */
        $res = is_array($data['response'] ?? null) ? $data['response'] : [];

        $uid = $this->stringifyId($res['id'] ?? null);

        return new SocialProfile(
            uid: $uid,
            username: $this->nullableString($res['nickname'] ?? null),
            picture: $this->nullableString($res['profile_image'] ?? null),
        );
    }

    /**
     * 제공자 고유 id 를 문자열로 정규화. 비어 있으면 검증 실패.
     */
    private function stringifyId(mixed $id): string
    {
        if (is_int($id)) {
            $id = (string) $id;
        }

        if (! is_string($id) || trim($id) === '') {
            throw AuthException::socialVerificationFailed();
        }

        return trim($id);
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
