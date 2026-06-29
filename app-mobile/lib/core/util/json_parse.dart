/// JSON 값 파싱 유틸.
///
/// 백엔드가 숫자 필드를 문자열('1')로 내려주는 경우가 있어,
/// num·String·null 을 모두 안전하게 int 로 변환한다.
int parseInt(dynamic value, [int fallback = 0]) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value.trim()) ?? fallback;
  return fallback;
}

/// nullable int 파싱 — 값이 없으면 null.
int? parseIntOrNull(dynamic value) {
  if (value == null) return null;
  if (value is int) return value;
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value.trim());
  return null;
}
