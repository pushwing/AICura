import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import '../auth/login_screen.dart';
import 'board_repository.dart';
import 'models/board.dart';
import 'models/board_comment.dart';

/// 커뮤니티 게시글 상세 — 본문 + 댓글. 열람 비로그인, 좋아요·댓글은 로그인 필요.
class BoardDetailScreen extends StatefulWidget {
  const BoardDetailScreen({super.key, required this.boardId});

  final int boardId;

  @override
  State<BoardDetailScreen> createState() => _BoardDetailScreenState();
}

class _BoardDetailScreenState extends State<BoardDetailScreen> {
  final _comment = TextEditingController();

  Board? _board;
  List<BoardComment> _comments = [];
  bool _loading = true;
  bool _sending = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _comment.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final detail = await context.read<BoardRepository>().detail(widget.boardId);
      if (!mounted) return;
      setState(() {
        _board = detail.board;
        _comments = detail.comments;
        _loading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.message;
        _loading = false;
      });
    }
  }

  /// 좋아요 토글 — 로그인 필요
  Future<void> _toggleLike() async {
    final b = _board;
    if (b == null) return;
    if (!await requireLogin(context) || !mounted) return;
    try {
      final liked = await context.read<BoardRepository>().toggleLike(b.id);
      if (mounted) {
        setState(() {
          final delta = liked && !b.isLiked
              ? 1
              : (!liked && b.isLiked ? -1 : 0);
          _board = b.copyWith(isLiked: liked, likeCount: b.likeCount + delta);
        });
      }
    } on ApiException catch (e) {
      _snack(e.message);
    }
  }

  /// 댓글 작성 — 로그인 필요
  Future<void> _submitComment() async {
    final text = _comment.text.trim();
    if (text.isEmpty) return;
    if (!await requireLogin(context) || !mounted) return;
    setState(() => _sending = true);
    try {
      final created =
          await context.read<BoardRepository>().addComment(widget.boardId, text);
      if (!mounted) return;
      setState(() {
        _comments = [..._comments, created];
        _comment.clear();
        _sending = false;
      });
      FocusScope.of(context).unfocus();
    } on ApiException catch (e) {
      if (mounted) setState(() => _sending = false);
      _snack(e.message);
    }
  }

  void _snack(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('후기'),
        actions: [
          if (_board != null)
            IconButton(
              onPressed: _toggleLike,
              icon: Icon(
                _board!.isLiked ? Icons.favorite : Icons.favorite_border,
                color: _board!.isLiked ? Colors.redAccent : null,
              ),
            ),
        ],
      ),
      body: _buildBody(),
      bottomNavigationBar: _board == null ? null : _buildCommentBar(),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(_error!),
            const SizedBox(height: 12),
            OutlinedButton(onPressed: _load, child: const Text('다시 시도')),
          ],
        ),
      );
    }

    final b = _board!;
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Text(b.subject,
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),),
        const SizedBox(height: 6),
        Row(
          children: [
            Text(b.userName,
                style: const TextStyle(color: Colors.black54),),
            if (b.rating > 0) ...[
              const SizedBox(width: 8),
              const Icon(Icons.star, size: 15, color: Colors.amber),
              const SizedBox(width: 2),
              Text(b.rating.toStringAsFixed(1),
                  style: const TextStyle(color: Colors.black54),),
            ],
            const Spacer(),
            Text(b.createdAt ?? '',
                style: const TextStyle(color: Colors.black38, fontSize: 12),),
          ],
        ),
        const Divider(height: 24),
        Text(b.contents ?? '', style: const TextStyle(fontSize: 15, height: 1.5)),
        const Divider(height: 32),
        Text('댓글 ${_comments.length}',
            style: const TextStyle(fontWeight: FontWeight.bold),),
        const SizedBox(height: 8),
        if (_comments.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 12),
            child: Text('첫 댓글을 남겨보세요',
                style: TextStyle(color: Colors.black45),),
          )
        else
          ..._comments.map((c) => _CommentTile(comment: c)),
        const SizedBox(height: 16),
      ],
    );
  }

  Widget _buildCommentBar() {
    return SafeArea(
      child: Padding(
        padding: EdgeInsets.fromLTRB(
          12,
          8,
          12,
          8 + MediaQuery.of(context).viewInsets.bottom,
        ),
        child: Row(
          children: [
            Expanded(
              child: TextField(
                controller: _comment,
                decoration: const InputDecoration(
                  hintText: '댓글을 입력하세요',
                  isDense: true,
                ),
                onSubmitted: (_) => _submitComment(),
              ),
            ),
            const SizedBox(width: 8),
            IconButton(
              onPressed: _sending ? null : _submitComment,
              icon: _sending
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.send, color: Color(0xFFFB2D6F)),
            ),
          ],
        ),
      ),
    );
  }
}

class _CommentTile extends StatelessWidget {
  const _CommentTile({required this.comment});

  final BoardComment comment;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(comment.userName,
                  style: const TextStyle(fontWeight: FontWeight.w600),),
              const Spacer(),
              Text(comment.createdAt ?? '',
                  style: const TextStyle(color: Colors.black38, fontSize: 12),),
            ],
          ),
          const SizedBox(height: 2),
          Text(comment.contents),
        ],
      ),
    );
  }
}
