<?php

namespace App\Libraries\Seo;

/**
 * 작성자명 마스킹 (이슈 #144 — 공개 후기 개인정보 보호)
 *
 * 공개 웹에 후기를 노출할 때 작성자 실명/닉네임을 부분 가린다.
 * - 0자: '익명'
 * - 1자: 그대로 (가릴 것이 없음)
 * - 2자: 첫 글자 + '*'
 * - 3자 이상: 첫 글자 + 가운데 '*' + 끝 글자
 */
final class NameMasker
{
    public static function mask(string $name): string
    {
        $name = trim($name);
        $len  = mb_strlen($name);

        return match (true) {
            $len === 0 => '익명',
            $len === 1 => $name,
            $len === 2 => mb_substr($name, 0, 1) . '*',
            default    => mb_substr($name, 0, 1) . str_repeat('*', $len - 2) . mb_substr($name, -1),
        };
    }
}
