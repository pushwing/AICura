/// 앱 전역 설정.
///
/// API 베이스 URL은 실행 환경에 따라 다르다.
/// - iOS 시뮬레이터: http://localhost:8300
/// - Android 에뮬레이터: http://10.0.2.2:8300 (에뮬레이터에서 호스트 PC를 가리키는 주소)
/// - 실기기: PC의 LAN IP (예: http://192.168.0.10:8300)
///
/// 빌드 시 `--dart-define=API_BASE_URL=...` 로 덮어쓸 수 있다.
class AppConfig {
  AppConfig._();

  /// API 루트 (버전 prefix 포함). 기본값은 Android 에뮬레이터 기준.
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8300/api/v1',
  );

  /// 외부 API 호출 타임아웃 (CLAUDE.md 기본 5초 원칙)
  static const Duration connectTimeout = Duration(seconds: 5);
  static const Duration receiveTimeout = Duration(seconds: 10);

  /// 목록 페이지당 기본 개수
  static const int defaultPerPage = 20;
}
