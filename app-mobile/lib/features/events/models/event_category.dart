/// 이벤트 카테고리 (필터 칩).
class EventCategory {
  EventCategory({required this.id, required this.title});

  final int id;
  final String title;

  factory EventCategory.fromJson(Map<String, dynamic> j) {
    return EventCategory(
      id: (j['id'] as num?)?.toInt() ?? 0,
      // 어드민 카테고리 응답 키 호환 (title / name)
      title: (j['title'] ?? j['name'] ?? '') as String,
    );
  }
}
