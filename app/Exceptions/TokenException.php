<?php

namespace App\Exceptions;

/**
 * JWT 토큰 검증 실패 예외 (이슈 #96)
 *
 * 만료(EXPIRED)와 무효(서명·형식 오류 등 INVALID)를 구분해,
 * 클라이언트가 만료 시 refresh 로 복구하고 무효 시 즉시 재로그인하도록 한다.
 */
final class TokenException extends DomainException
{
    private function __construct(
        private readonly string $apiCode,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function httpStatusCode(): int
    {
        return 401;
    }

    public function errorCode(): string
    {
        return $this->apiCode;
    }

    /** 서명·형식 오류 또는 토큰 타입 불일치 — 신뢰할 수 없는 토큰 */
    public static function invalid(): self
    {
        return new self('INVALID_TOKEN', '유효하지 않은 토큰입니다.');
    }

    /** 서명·형식은 정상이나 만료된 토큰 */
    public static function expired(): self
    {
        return new self('TOKEN_EXPIRED', '토큰이 만료되었습니다.');
    }
}
