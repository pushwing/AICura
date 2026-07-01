<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * 도메인 예외 기반 클래스 (CLAUDE.md 도메인 예외 처리 규칙)
 *
 * 모든 도메인 예외는 HTTP 상태코드 + 에러 코드(UPPER_SNAKE_CASE)를 함께 노출한다.
 * API 컨트롤러는 이 타입을 잡아 표준 에러 응답으로 변환한다.
 */
abstract class DomainException extends RuntimeException
{
    abstract public function httpStatusCode(): int;

    abstract public function errorCode(): string;
}
