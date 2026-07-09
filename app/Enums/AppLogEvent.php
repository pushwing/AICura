<?php

namespace App\Enums;

/**
 * 소비자 앱 액션 이벤트 택소노미 (이슈 #120)
 *
 * app_logs.event 에 저장되는 이벤트 키이며, 어드민 통계 화면의 라벨을 일원화한다.
 * 신규 액션 추가 시 case 와 label() 만 보강하면 집계·노출이 자동 확장된다.
 *
 * 이슈 언급 4종(목록·상세·신청폼·신청)에 핵심 동선(실행·검색·찜·병원상세)을 더했다.
 */
enum AppLogEvent: string
{
    case AppOpen            = 'app_open';             // 앱 실행
    case EventListView      = 'event_list_view';      // 이벤트(캠페인) 목록 조회
    case EventDetailView    = 'event_detail_view';    // 이벤트 상세 조회
    case ApplyFormView      = 'apply_form_view';      // 신청폼 진입
    case ApplySubmit        = 'apply_submit';         // 신청 제출(클릭)
    case EventSearch        = 'event_search';         // 이벤트 검색
    case EventLike          = 'event_like';           // 이벤트 찜
    case HospitalDetailView = 'hospital_detail_view'; // 병원 상세 조회

    /**
     * 통계 화면 표시용 한국어 라벨
     */
    public function label(): string
    {
        return match ($this) {
            self::AppOpen            => '앱 실행',
            self::EventListView      => '이벤트 목록',
            self::EventDetailView    => '이벤트 상세',
            self::ApplyFormView      => '신청폼 진입',
            self::ApplySubmit        => '신청 제출',
            self::EventSearch        => '이벤트 검색',
            self::EventLike          => '이벤트 찜',
            self::HospitalDetailView => '병원 상세',
        };
    }

    /**
     * 알 수 없는 이벤트 키를 안전하게 라벨로 변환.
     * 집계 화면이 정의되지 않은 신규 이벤트도 깨지지 않고 원문 키로 노출하도록 한다.
     */
    public static function labelFor(string $event): string
    {
        return self::tryFrom($event)?->label() ?? $event;
    }
}
