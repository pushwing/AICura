import '../../../core/util/json_parse.dart';

/// 병원 후기 (GET /hospitals/{id}/reviews).
class HospitalReview {
  HospitalReview({
    required this.id,
    required this.userName,
    required this.subject,
    required this.contents,
    required this.rating,
    this.likeCount = 0,
    this.commentCount = 0,
    this.createdAt,
  });

  final int id;
  final String userName;
  final String subject;
  final String contents;
  final double rating;
  final int likeCount;
  final int commentCount;
  final String? createdAt;

  factory HospitalReview.fromJson(Map<String, dynamic> j) {
    return HospitalReview(
      id: parseInt(j['id']),
      userName: (j['user_name'] ?? '').toString(),
      subject: (j['subject'] ?? '').toString(),
      contents: (j['contents'] ?? '').toString(),
      rating: (j['rating'] is num)
          ? (j['rating'] as num).toDouble()
          : double.tryParse('${j['rating']}') ?? 0,
      likeCount: parseInt(j['like_count']),
      commentCount: parseInt(j['comment_count']),
      createdAt: j['created_at'] as String?,
    );
  }
}
