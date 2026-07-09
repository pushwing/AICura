<?php

namespace App\Exceptions;

/**
 * 헬스포인트 도메인 예외 (이슈 #114)
 *
 * 정적 팩토리로 의미별 인스턴스를 생성하며, 각 케이스에 맞는
 * HTTP 상태코드와 에러 코드(CLAUDE.md 에러 코드 네이밍 규칙)를 보유한다.
 */
final class PointException extends DomainException
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

    /**
     * 차감 요청 금액이 0 이하
     */
    public static function invalidAmount(): self
    {
        return new self(422, 'VALIDATION_ERROR', '차감 금액은 1 이상이어야 합니다.');
    }

    /**
     * 보유 잔액보다 큰 차감 요청
     */
    public static function insufficientBalance(): self
    {
        return new self(422, 'INSUFFICIENT_POINT', '보유한 헬스포인트가 부족합니다.');
    }
}
