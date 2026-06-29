import '../../../core/util/json_parse.dart';

/// 내 찜 항목 (GET /me/likes).
class LikeItem {
  LikeItem({
    required this.type,
    required this.id,
    required this.title,
    required this.hospitalName,
    this.thumbnailUrl,
    this.likedAt,
  });

  final String type; // 예: campaign
  final int id;
  final String title;
  final String hospitalName;
  final String? thumbnailUrl;
  final String? likedAt;

  factory LikeItem.fromJson(Map<String, dynamic> j) {
    return LikeItem(
      type: (j['type'] ?? '').toString(),
      id: parseInt(j['id']),
      title: (j['title'] ?? '').toString(),
      hospitalName: (j['hospital_name'] ?? '').toString(),
      thumbnailUrl: j['thumbnail_url'] as String?,
      likedAt: j['liked_at'] as String?,
    );
  }
}
