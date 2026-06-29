# AICura 소비자 앱 (Flutter)

AICura 백엔드(`/api/v1`)에 대응하는 소비자 모바일 앱. 이슈 #117.
**최초 실행 화면은 이벤트 리스트(홈)** 이며, 인증(로그인/회원가입)을 거쳐 진입한다.

## 요구사항

- Flutter SDK 3.4+ (Dart 3.4+)
- 실행 중인 AICura 백엔드 (`make serve`, 포트 8300)

## 최초 설정

이 디렉토리는 `lib/` 소스와 설정만 포함한다. 플랫폼 폴더(android/ios)는
아직 생성되지 않았으므로 최초 1회 아래로 스캐폴딩한다.

```bash
cd app-mobile
flutter create .          # android/ios/ 등 플랫폼 폴더 생성 (lib/ 는 유지됨)
flutter pub get
```

## 실행

API 베이스 URL은 실행 환경에 맞춰 `--dart-define` 으로 지정한다.

```bash
# Android 에뮬레이터 (기본값 — 10.0.2.2 가 호스트 PC)
flutter run

# iOS 시뮬레이터
flutter run --dart-define=API_BASE_URL=http://localhost:8300/api/v1

# 실기기 (PC LAN IP)
flutter run --dart-define=API_BASE_URL=http://192.168.0.10:8300/api/v1
```

## 구조

```
lib/
  main.dart                  앱 진입점
  app.dart                   DI 조립 + 인증 게이트(로그인/홈 분기)
  core/
    config/app_config.dart   API 베이스 URL·타임아웃
    network/api_client.dart  Dio + JWT 인터셉터 + 401 자동 갱신 + 봉투 언래핑
    network/api_exception.dart
    storage/token_storage.dart  토큰 보안 저장
    theme/app_theme.dart     브랜드 테마(#0F6E56 / #1D9E75)
  features/
    auth/                    로그인·회원가입·소셜·세션 상태
    events/                  이벤트 목록(홈)·배너·추천·카테고리·정렬·찜
```

## 구현 범위 (이슈 #117 1차 증분)

- [x] 인증: 로그인 / 회원가입(이메일 중복확인) / 세션 자동 갱신·만료 처리
- [x] 홈(이벤트 리스트): 배너·추천·카테고리·정렬·무한 스크롤·찜 토글
- [ ] 이벤트 상세 (`GET /campaigns/{id}`)
- [ ] 병원 / 커뮤니티 / 마이페이지 / 예약 등 (후속 증분)

## API 연동 포맷

- 응답 봉투: `{ "status": "success", "data": ..., "meta": ... }`
- 목록 `meta`: `page` · `per_page` · `total` · `last_page`
- 인증: `Authorization: Bearer {accessToken}` (인터셉터 자동 첨부)
