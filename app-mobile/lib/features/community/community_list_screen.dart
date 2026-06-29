import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import 'board_detail_screen.dart';
import 'board_repository.dart';
import 'models/board.dart';

/// 커뮤니티(후기) 목록 화면 (비로그인 열람).
class CommunityListScreen extends StatefulWidget {
  const CommunityListScreen({super.key});

  @override
  State<CommunityListScreen> createState() => _CommunityListScreenState();
}

class _CommunityListScreenState extends State<CommunityListScreen> {
  final _scroll = ScrollController();
  final List<Board> _items = [];
  int _page = 0;
  int _lastPage = 1;
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _scroll.addListener(_onScroll);
    _loadFirst();
  }

  @override
  void dispose() {
    _scroll
      ..removeListener(_onScroll)
      ..dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 300) {
      _loadMore();
    }
  }

  Future<void> _loadFirst() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final page = await context.read<BoardRepository>().list(page: 1);
      if (!mounted) return;
      setState(() {
        _items
          ..clear()
          ..addAll(page.items);
        _page = page.page;
        _lastPage = page.lastPage;
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

  Future<void> _loadMore() async {
    if (_loadingMore || _page >= _lastPage) return;
    _loadingMore = true;
    try {
      final page = await context.read<BoardRepository>().list(page: _page + 1);
      if (!mounted) return;
      setState(() {
        _items.addAll(page.items);
        _page = page.page;
        _lastPage = page.lastPage;
      });
    } on ApiException {
      // 다음 페이지 실패는 무시
    } finally {
      _loadingMore = false;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('커뮤니티')),
      body: _buildBody(),
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
            OutlinedButton(onPressed: _loadFirst, child: const Text('다시 시도')),
          ],
        ),
      );
    }
    if (_items.isEmpty) {
      return const Center(child: Text('등록된 후기가 없습니다'));
    }
    return RefreshIndicator(
      onRefresh: _loadFirst,
      child: ListView.separated(
        controller: _scroll,
        itemCount: _items.length + (_page < _lastPage ? 1 : 0),
        separatorBuilder: (_, __) =>
            const Divider(height: 1, indent: 16, endIndent: 16),
        itemBuilder: (context, i) {
          if (i >= _items.length) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Center(child: CircularProgressIndicator()),
            );
          }
          return _BoardTile(board: _items[i]);
        },
      ),
    );
  }
}

class _BoardTile extends StatelessWidget {
  const _BoardTile({required this.board});

  final Board board;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      title: Text(board.subject,
          maxLines: 1, overflow: TextOverflow.ellipsis,),
      subtitle: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (board.excerpt != null && board.excerpt!.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 2, bottom: 6),
              child: Text(board.excerpt!,
                  maxLines: 2, overflow: TextOverflow.ellipsis,),
            ),
          Row(
            children: [
              if (board.typeLabel != null) ...[
                _Tag(board.typeLabel!),
                const SizedBox(width: 8),
              ],
              Text(board.userName,
                  style:
                      const TextStyle(fontSize: 12, color: Colors.black54),),
              const Spacer(),
              const Icon(Icons.favorite, size: 13, color: Colors.redAccent),
              const SizedBox(width: 2),
              Text('${board.likeCount}',
                  style:
                      const TextStyle(fontSize: 12, color: Colors.black54),),
              const SizedBox(width: 8),
              const Icon(Icons.mode_comment_outlined,
                  size: 13, color: Colors.black45,),
              const SizedBox(width: 2),
              Text('${board.commentCount}',
                  style:
                      const TextStyle(fontSize: 12, color: Colors.black54),),
            ],
          ),
        ],
      ),
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute<void>(
          builder: (_) => BoardDetailScreen(boardId: board.id),
        ),
      ),
    );
  }
}

class _Tag extends StatelessWidget {
  const _Tag(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
      decoration: BoxDecoration(
        color: const Color(0xFFEAF5F0),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(text,
          style: const TextStyle(fontSize: 11, color: Color(0xFF0F6E56)),),
    );
  }
}
