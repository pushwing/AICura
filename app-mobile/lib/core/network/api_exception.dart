/// 서버 표준 에러 응답을 표현한다.
///
/// 서버 포맷: `{ "status": "error", "code": "...", "message": "..." }`
class ApiException implements Exception {
  ApiException({
    required this.code,
    required this.message,
    required this.statusCode,
  });

  /// 도메인 에러 코드 (예: VALIDATION_ERROR, UNAUTHORIZED, NOT_FOUND)
  final String code;

  /// 사용자 노출용 메시지
  final String message;

  /// HTTP 상태 코드
  final int statusCode;

  /// 인증 만료/누락 여부 — 로그인 화면으로 보낼지 판단
  bool get isUnauthorized => statusCode == 401;

  @override
  String toString() => 'ApiException($statusCode, $code): $message';
}
