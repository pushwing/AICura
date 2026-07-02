<?php

namespace App\Exceptions;

/**
 * 상담 신청 도메인 예외 (이슈 #100)
 */
final class CallRequestException extends DomainException
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

    /** 미확인 상태가 아니어서 취소 불가 — 이미 병원이 접촉·예약·내원 처리한 건 */
    public static function cannotCancel(): self
    {
        return new self(409, 'CANNOT_CANCEL', '이미 처리가 진행되어 취소할 수 없는 신청입니다.');
    }
}
