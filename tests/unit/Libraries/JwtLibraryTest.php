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

    /** 시크릿이 비어 있으면 생성자에서 즉시 실패한다 (이슈 #187 fail-closed) */
    public function testEmptySecretThrows(): void
    {
        $prev = getenv('JWT_SECRET');
        putenv('JWT_SECRET=');
        $_ENV['JWT_SECRET'] = '';

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessageMatches('/JWT_SECRET/');
            new JwtLibrary();
        } finally {
            // 다른 테스트에 영향이 없도록 원복
            if ($prev === false) {
                putenv('JWT_SECRET');
                unset($_ENV['JWT_SECRET']);
            } else {
                putenv('JWT_SECRET=' . $prev);
                $_ENV['JWT_SECRET'] = $prev;
            }
        }
    }

    /** 32자 미만 시크릿도 거부한다 */
    public function testShortSecretThrows(): void
    {
        $prev = getenv('JWT_SECRET');
        putenv('JWT_SECRET=tooshort');
        $_ENV['JWT_SECRET'] = 'tooshort';

        try {
            $this->expectException(\RuntimeException::class);
            new JwtLibrary();
        } finally {
            if ($prev === false) {
                putenv('JWT_SECRET');
                unset($_ENV['JWT_SECRET']);
            } else {
                putenv('JWT_SECRET=' . $prev);
                $_ENV['JWT_SECRET'] = $prev;
            }
        }
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
