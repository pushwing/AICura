<?php

namespace App\Exceptions;

/**
 * 리소스 없음 도메인 예외 (이슈 #98)
 *
 * 조회·접근 대상이 존재하지 않거나 노출 조건을 충족하지 않을 때 사용한다.
 */
final class NotFoundException extends DomainException
{
    public function httpStatusCode(): int
    {
        return 404;
    }

    public function errorCode(): string
    {
        return 'NOT_FOUND';
    }

    public static function of(string $message = '대상을 찾을 수 없습니다.'): self
    {
        return new self($message);
    }
}
