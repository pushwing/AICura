<?php

namespace App\Libraries\Social;

/**
 * 소셜 제공자에서 검증된 사용자 프로필 (이슈 #187)
 *
 * SocialVerifierInterface 가 제공자 API 응답을 검증·정규화한 결과를 담는다.
 * uid 는 제공자가 보증한 고유 식별자로, 계정 매칭·생성의 유일한 신뢰 기준이다.
 */
final readonly class SocialProfile
{
    public function __construct(
        public string $uid,
        public ?string $username = null,
        public ?string $picture = null,
    ) {
    }
}
