import '../../../core/util/json_parse.dart';

/// 게시글 댓글.
class BoardComment {
  BoardComment({
    required this.id,
    required this.userId,
    required this.userName,
    required this.contents,
    this.createdAt,
  });

  final int id;
  final int userId;
  final String userName;
  final String contents;
  final String? createdAt;

  factory BoardComment.fromJson(Map<String, dynamic> j) {
    return BoardComment(
      id: parseInt(j['id']),
      userId: parseInt(j['user_id']),
      userName: (j['user_name'] ?? '').toString(),
      contents: (j['contents'] ?? '').toString(),
      createdAt: j['created_at'] as String?,
    );
  }
}
