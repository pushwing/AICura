<?php

namespace App\Libraries;

class JwtLibrary
{
    private string $secret;
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

    public function validateAccessToken(string $token): array|false
    {
        $payload = $this->decode($token);

        if (!$payload || ($payload['type'] ?? '') !== 'access' || $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    public function validateRefreshToken(string $token): array|false
    {
        $payload = $this->decode($token);

        if (!$payload || ($payload['type'] ?? '') !== 'refresh' || $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    private function encode(array $payload): string
    {
        $header  = $this->base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode($payload));
        $sig     = $this->base64url(hash_hmac('sha256', "$header.$payload", $this->secret, true));

        return "$header.$payload.$sig";
    }

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
