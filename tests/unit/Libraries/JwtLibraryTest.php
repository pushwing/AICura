<?php

namespace Tests\Unit\Libraries;

use App\Exceptions\TokenException;
use App\Libraries\JwtLibrary;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionMethod;

/**
 * JwtLibrary 토큰 검증 테스트 (이슈 #96)
 *
 * 만료(TOKEN_EXPIRED)와 무효(INVALID_TOKEN)를 구분하는지 검증한다.
 *
 * @internal
 */
final class JwtLibraryTest extends CIUnitTestCase
{
    private JwtLibrary $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = new JwtLibrary();
    }

    public function testValidAccessTokenReturnsPayload(): void
    {
        $token   = $this->jwt->generateAccessToken(42);
        $payload = $this->jwt->validateAccessToken($token);

        $this->assertSame(42, (int) $payload['sub']);
        $this->assertSame('access', $payload['type']);
    }

    public function testExpiredAccessTokenThrowsExpired(): void
    {
        $token = $this->forgeToken(['sub' => 1, 'type' => 'access', 'exp' => time() - 10, 'iat' => time() - 3610]);

        try {
            $this->jwt->validateAccessToken($token);
            $this->fail('TokenException 이 발생해야 합니다.');
        } catch (TokenException $e) {
            $this->assertSame('TOKEN_EXPIRED', $e->errorCode());
            $this->assertSame(401, $e->httpStatusCode());
        }
    }

    public function testTamperedSignatureThrowsInvalid(): void
    {
        $token   = $this->jwt->generateAccessToken(7);
        $tampered = $token . 'xx'; // 서명 훼손

        $this->expectException(TokenException::class);
        $this->expectExceptionMessageMatches('/유효하지 않은/');

        try {
            $this->jwt->validateAccessToken($tampered);
        } catch (TokenException $e) {
            $this->assertSame('INVALID_TOKEN', $e->errorCode());
            throw $e;
        }
    }

    public function testWrongTypeThrowsInvalid(): void
    {
        // refresh 토큰을 access 로 검증하면 타입 불일치 → INVALID
        $refresh = $this->jwt->generateRefreshToken(5);

        $this->expectException(TokenException::class);

        try {
            $this->jwt->validateAccessToken($refresh);
        } catch (TokenException $e) {
            $this->assertSame('INVALID_TOKEN', $e->errorCode());
            throw $e;
        }
    }

    public function testMalformedTokenThrowsInvalid(): void
    {
        $this->expectException(TokenException::class);

        try {
            $this->jwt->validateAccessToken('not.a.jwt');
        } catch (TokenException $e) {
            $this->assertSame('INVALID_TOKEN', $e->errorCode());
            throw $e;
        }
    }

    public function testExpiredRefreshTokenThrowsExpired(): void
    {
        $token = $this->forgeToken(['sub' => 1, 'type' => 'refresh', 'exp' => time() - 10, 'iat' => time() - 100]);

        try {
            $this->jwt->validateRefreshToken($token);
            $this->fail('TokenException 이 발생해야 합니다.');
        } catch (TokenException $e) {
            $this->assertSame('TOKEN_EXPIRED', $e->errorCode());
        }
    }

    /**
     * 임의 페이로드로 서명이 유효한 토큰을 생성한다(만료 토큰 재현용).
     *
     * @param array<string, mixed> $payload
     */
    private function forgeToken(array $payload): string
    {
        $encode = new ReflectionMethod(JwtLibrary::class, 'encode');

        return (string) $encode->invoke($this->jwt, $payload);
    }
}
