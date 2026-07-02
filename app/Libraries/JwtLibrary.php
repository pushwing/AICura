<?php

namespace App\Libraries;

use App\Exceptions\TokenException;

class JwtLibrary
{
    private readonly string $secret;
    private int $accessTtl  = 3600;      // 1시간
    private int $refreshTtl = 2592000;   // 30일

    public function __construct()
    {
        $this->secret = env('JWT_SECRET', '');
    }

    public function generateAccessToken(int $userId): string
    {
        return $this->encode([
            'sub' => $userId,
            'type' => 'access',
            'exp' => time() + $this->accessTtl,
            'iat' => time(),
        ]);
    }

    public function generateRefreshToken(int $userId): string
    {
        return $this->encode([
            'sub'  => $userId,
            'type' => 'refresh',
            'exp'  => time() + $this->refreshTtl,
            'iat'  => time(),
        ]);
    }

    /**
     * Access Token 검증
     *
     * @return array<string, mixed> 유효한 페이로드
     * @throws TokenException 무효(서명·형식·타입) 또는 만료
     */
    public function validateAccessToken(string $token): array
    {
        return $this->validate($token, 'access');
    }

    /**
     * Refresh Token 검증
     *
     * @return array<string, mixed> 유효한 페이로드
     * @throws TokenException 무효(서명·형식·타입) 또는 만료
     */
    public function validateRefreshToken(string $token): array
    {
        return $this->validate($token, 'refresh');
    }

    /**
     * 공통 검증 — 서명·형식 → 타입 → 만료 순으로 확인한다.
     * 서명이 위조된 토큰은 exp 를 신뢰할 수 없으므로 INVALID 로 처리한다.
     *
     * @return array<string, mixed>
     * @throws TokenException
     */
    private function validate(string $token, string $expectedType): array
    {
        $payload = $this->decode($token);

        // 서명·형식 오류 또는 타입 불일치 → 신뢰 불가
        if (!$payload || ($payload['type'] ?? '') !== $expectedType) {
            throw TokenException::invalid();
        }

        // 서명은 정상이나 만료
        if (((int) ($payload['exp'] ?? 0)) < time()) {
            throw TokenException::expired();
        }

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    private function encode(array $payload): string
    {
        $header  = $this->base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode($payload));
        $sig     = $this->base64url(hash_hmac('sha256', "$header.$payload", $this->secret, true));

        return "$header.$payload.$sig";
    }

    /** @return array<string, mixed>|false */
    private function decode(string $token): array|false
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $sig] = $parts;

        $expected = $this->base64url(hash_hmac('sha256', "$header.$payload", $this->secret, true));

        if (!hash_equals($expected, $sig)) {
            return false;
        }

        return json_decode($this->base64urlDecode($payload), true);
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
