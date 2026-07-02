import '../../../core/util/json_parse.dart';

/// 커뮤니티 게시글(후기) — 목록/상세 공용.
class Board {
  Board({
    required this.id,
    required this.type,
    this.typeLabel,
    required this.targetId,
    required this.userName,
    required this.subject,
    required this.rating,
    required this.likeCount,
    required this.commentCount,
    this.excerpt,
    this.contents,
    this.createdAt,
    this.isLiked = false,
  });

  final int id;
  final int type; // 1=이벤트, 2=병원
  final String? typeLabel;
  final int targetId;
  final String userName;
  final String subject;
  final double rating;
  final int likeCount;
  final int commentCount;
  final String? excerpt; // 목록
  final String? contents; // 상세
  final String? createdAt;
  final bool isLiked;

  factory Board.fromJson(Map<String, dynamic> j) {
    return Board(
      id: parseInt(j['id']),
      type: parseInt(j['type']),
      typeLabel: j['type_label'] as String?,
      targetId: parseInt(j['target_id']),
      userName: (j['user_name'] ?? '').toString(),
      subject: (j['subject'] ?? '').toString(),
      rating: (j['rating'] is num)
          ? (j['rating'] as num).toDouble()
          : double.tryParse('${j['rating']}') ?? 0,
      likeCount: parseInt(j['like_count']),
      commentCount: parseInt(j['comment_count']),
      excerpt: j['excerpt'] as String?,
      contents: j['contents'] as String?,
      createdAt: j['created_at'] as String?,
      isLiked: (j['is_liked'] as bool?) ?? false,
    );
  }

  Board copyWith({bool? isLiked, int? likeCount}) {
    return Board(
      id: id,
      type: type,
      typeLabel: typeLabel,
      targetId: targetId,
      userName: userName,
      subject: subject,
      rating: rating,
      likeCount: likeCount ?? this.likeCount,
      commentCount: commentCount,
      excerpt: excerpt,
      contents: contents,
      createdAt: createdAt,
      isLiked: isLiked ?? this.isLiked,
    );
  }
}
