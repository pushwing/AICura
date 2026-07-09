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

    /**
     * 소셜 제공자 토큰 검증 실패 — 만료·위조·네트워크 오류를 클라이언트에는 통일된 401 로 응답
     */
    public static function socialVerificationFailed(): self
    {
        return new self(401, 'SOCIAL_AUTH_FAILED', '소셜 로그인 인증에 실패했습니다. 다시 시도해 주세요.');
    }

    /**
     * 사전 검사를 통과했으나 계정 생성(insert)이 실패한 예외적 상황 — 상세는 서버 로그로만 남긴다
     */
    public static function registrationFailed(): self
    {
        return new self(500, 'INTERNAL_ERROR', '계정 생성에 실패했습니다. 잠시 후 다시 시도해 주세요.');
    }
}
