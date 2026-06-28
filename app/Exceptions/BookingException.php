<?php

namespace App\Exceptions;

/**
 * 예약 도메인 예외 (이슈 #101)
 */
final class BookingException extends DomainException
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

    /** 이미 취소된 예약은 변경·재취소 불가 */
    public static function alreadyCancelled(): self
    {
        return new self(409, 'ALREADY_CANCELLED', '이미 취소된 예약입니다.');
    }
}
