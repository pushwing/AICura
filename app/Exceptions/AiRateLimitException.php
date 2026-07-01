<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * AI 공급자 일시 오류(레이트리밋 429·서버 5xx) 예외 (이슈 #74)
 *
 * 영구 실패(잘못된 요청·파싱 오류 등)와 구분하기 위한 타입. 큐 소비 커맨드는 이 예외를
 * 잡아 대상을 FAILED로 확정하지 않고 PENDING으로 유지해, 다음 실행 때 자동 재시도한다.
 *
 *   httpStatus         원본 HTTP 상태코드 (429 / 5xx)
 *   retryAfterSeconds  공급자가 안내한 권장 재시도 대기 시간(초). 알 수 없으면 0.
 */
class AiRateLimitException extends RuntimeException
{
    public function __construct(
        public readonly int $httpStatus,
        public readonly int $retryAfterSeconds = 0,
        string $message = '',
    ) {
        parent::__construct($message !== '' ? $message : "AI 공급자 일시 오류 (HTTP {$httpStatus})");
    }
}
