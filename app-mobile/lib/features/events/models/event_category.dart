import '../../../core/util/json_parse.dart';

/// 이벤트 카테고리 (필터 칩).
class EventCategory {
  EventCategory({required this.id, required this.title});

  final int id;
  final String title;

  factory EventCategory.fromJson(Map<String, dynamic> j) {
    return EventCategory(
      // 백엔드가 id 를 문자열('1')로 내려줄 수 있어 안전 파싱
      id: parseInt(j['id']),
      // 어드민 카테고리 응답 키 호환 (title / name)
      title: (j['title'] ?? j['name'] ?? '').toString(),
    );
  }
}
