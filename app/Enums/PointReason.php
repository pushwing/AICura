<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * 헬스포인트 변동 사유 (이슈 #114)
 *
 * health_point_logs.type 에 저장되는 사유 코드이며, 적립형 사유는 기본 적립 금액을 함께 보유한다.
 * 금액은 정책 상수로 고정하고, 차감(Redeem)은 요청값을 사용하므로 기본 금액이 없다.
 */
enum PointReason: string
{
    case Signup       = 'signup';        // 회원가입 적립
    case ReviewCreate = 'review';        // 후기 작성 적립
    case ReviewRevoke = 'review_revoke'; // 후기 삭제에 따른 적립 회수
    case Redeem       = 'redeem';        // 사용·차감

    /**
     * 사유별 기본 적립 금액 (적립형만 양수, 그 외 0).
     *
     * 회수·차감은 발생 시점에 실제 변동량을 계산하므로 0을 반환한다.
     */
    public function defaultAmount(): int
    {
        return match ($this) {
            self::Signup       => 500,
            self::ReviewCreate => 100,
            self::ReviewRevoke,
            self::Redeem       => 0,
        };
    }

    /** 내역 표시용 한국어 라벨 */
    public function label(): string
    {
        return match ($this) {
            self::Signup       => '회원가입 적립',
            self::ReviewCreate => '후기 작성 적립',
            self::ReviewRevoke => '후기 삭제 회수',
            self::Redeem       => '포인트 사용',
        };
    }
}
