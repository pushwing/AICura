<?php

namespace App\Exceptions;

/**
 * 외부(소비자) 앱 인증 도메인 예외 (이슈 #96)
 *
 * 정적 팩토리로 의미별 인스턴스를 생성하며, 각 케이스에 맞는
 * HTTP 상태코드와 에러 코드(CLAUDE.md 에러 코드 네이밍 규칙)를 보유한다.
 */
final class AuthException extends DomainException
{
    private function __construct(
        private readonly int $status,
        private readonly string $apiCode,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function httpStatusCode(): int
    {
        return $this->status;
    }

    public function errorCode(): string
    {
        return $this->apiCode;
    }

    public static function invalidCredentials(): self
    {
        return new self(401, 'INVALID_CREDENTIALS', '이메일 또는 비밀번호가 올바르지 않습니다.');
    }

    public static function accountInactive(): self
    {
        return new self(403, 'FORBIDDEN', '비활성화되었거나 이용이 제한된 계정입니다.');
    }

    public static function emailAlreadyExists(): self
    {
        return new self(409, 'ALREADY_EXISTS', '이미 가입된 이메일입니다.');
    }

    public static function unsupportedProvider(): self
    {
        return new self(422, 'VALIDATION_ERROR', '지원하지 않는 소셜 로그인 제공자입니다.');
    }
}
