<?php

namespace App\Libraries;

/**
 * 요청 컨텍스트 내 인증된 사용자 ID를 보관하는 정적 홀더.
 * JwtAuthFilter가 set하고 컨트롤러에서 get한다.
 */
class Auth
{
    private static int $userId = 0;

    public static function setUserId(int $id): void
    {
        self::$userId = $id;
    }

    public static function userId(): int
    {
        return self::$userId;
    }

    public static function check(): bool
    {
        return self::$userId > 0;
    }
}
