import '../../core/config/app_config.dart';
import '../../core/network/api_client.dart';
import 'models/board.dart';
import 'models/board_comment.dart';

/// 게시글 목록 + 페이지네이션.
class BoardPage {
  BoardPage({required this.items, required this.page, required this.lastPage});

  final List<Board> items;
  final int page;
  final int lastPage;

  bool get hasMore => page < lastPage;
}

/// 게시글 상세 + 인라인 댓글.
class BoardDetail {
  BoardDetail({required this.board, required this.comments});

  final Board board;
  final List<BoardComment> comments;
}

/// 커뮤니티(후기 게시판) API. 조회는 비로그인 허용, 작성·댓글·좋아요는 로그인 필요.
class BoardRepository {
  BoardRepository(this._api);

  final ApiClient _api;

  Future<BoardPage> list({
    int page = 1,
    int perPage = AppConfig.defaultPerPage,
  }) async {
    final res = await _api.get('/boards', query: {
      'page': page,
      'per_page': perPage,
    },);
    final meta = res.meta ?? const {};
    return BoardPage(
      items: res.dataAsList
          .map((e) => Board.fromJson((e as Map).cast<String, dynamic>()))
          .toList(),
      page: (meta['page'] as num?)?.toInt() ?? page,
      lastPage: (meta['last_page'] as num?)?.toInt() ?? page,
    );
  }

  Future<BoardDetail> detail(int id) async {
    final res = await _api.get('/boards/$id');
    final data = res.dataAsMap;
    final comments = (data['comments'] as List?)
            ?.map((e) => BoardComment.fromJson((e as Map).cast<String, dynamic>()))
            .toList() ??
        <BoardComment>[];
    return BoardDetail(board: Board.fromJson(data), comments: comments);
  }

  /// 좋아요 토글 — 로그인 필요. liked 반환.
  Future<bool> toggleLike(int id) async {
    final res = await _api.post('/boards/$id/like');
    return (res.dataAsMap['liked'] as bool?) ?? false;
  }

  /// 댓글 작성 — 로그인 필요. 생성된 댓글 반환.
  Future<BoardComment> addComment(int id, String contents) async {
    final res =
        await _api.post('/boards/$id/comments', body: {'contents': contents});
    return BoardComment.fromJson(res.dataAsMap);
  }

  /// 후기 작성 — 로그인 필요. type: 1=이벤트, 2=병원.
  Future<Board> create({
    required int type,
    required int targetId,
    required String subject,
    required String contents,
    double? rating,
  }) async {
    final res = await _api.post('/boards', body: {
      'type': type,
      'target_id': targetId,
      'subject': subject,
      'contents': contents,
      if (rating != null) 'rating': rating,
    },);
    return Board.fromJson(res.dataAsMap);
  }
}
