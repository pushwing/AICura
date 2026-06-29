import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import '../../core/theme/app_colors.dart';
import 'board_detail_screen.dart';
import 'board_repository.dart';
import 'models/board.dart';

/// 후기 리스트 화면 (비로그인 열람) — 카드형 후기 + 유형 필터 칩.
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
  int _filterType = 0; // 0 전체, 1 이벤트, 2 병원

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

  List<Board> get _visible =>
      _filterType == 0 ? _items : _items.where((b) => b.type == _filterType).toList();

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
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        backgroundColor: AppColors.bg,
        title: const Text('후기'),
      ),
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

    final visible = _visible;
    return RefreshIndicator(
      onRefresh: _loadFirst,
      child: ListView.builder(
        controller: _scroll,
        padding: const EdgeInsets.only(bottom: 12),
        itemCount: visible.length + 2, // 0: 필터칩, 마지막: 로더/여백
        itemBuilder: (context, i) {
          if (i == 0) {
            return _FilterBar(
              selected: _filterType,
              onSelect: (t) => setState(() => _filterType = t),
            );
          }
          final idx = i - 1;
          if (idx >= visible.length) {
            return (_page < _lastPage && _filterType == 0)
                ? const Padding(
                    padding: EdgeInsets.all(16),
                    child: Center(child: CircularProgressIndicator()),
                  )
                : const SizedBox(height: 8);
          }
          return _ReviewCard(board: visible[idx]);
        },
      ),
    );
  }
}

/// 유형 필터 칩 (전체/이벤트/병원)
class _FilterBar extends StatelessWidget {
  const _FilterBar({required this.selected, required this.onSelect});

  final int selected;
  final void Function(int) onSelect;

  static const _labels = {0: '전체', 1: '이벤트', 2: '병원'};

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 54,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        children: [
          for (final e in _labels.entries)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 4),
              child: GestureDetector(
                onTap: () => onSelect(e.key),
                child: Container(
                  alignment: Alignment.center,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 9),
                  decoration: BoxDecoration(
                    color: selected == e.key ? AppColors.accent : Colors.white,
                    borderRadius: BorderRadius.circular(999),
                    border: Border.all(
                      color: selected == e.key
                          ? AppColors.accent
                          : AppColors.lineStrong,
                    ),
                  ),
                  child: Text(
                    e.value,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: selected == e.key
                          ? Colors.white
                          : const Color(0xFF5A5A66),
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

/// 후기 카드
class _ReviewCard extends StatelessWidget {
  const _ReviewCard({required this.board});

  final Board board;

  @override
  Widget build(BuildContext context) {
    final b = board;
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: () => Navigator.of(context).push(
            MaterialPageRoute<void>(
              builder: (_) => BoardDetailScreen(boardId: b.id),
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 작성자 행
                Row(
                  children: [
                    CircleAvatar(
                      radius: 16,
                      backgroundColor: AppColors.accentTint,
                      child: Text(
                        b.userName.isNotEmpty ? b.userName.characters.first : '?',
                        style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: AppColors.accent,),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(b.userName,
                              style: const TextStyle(
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.ink2,),),
                          if (b.createdAt != null)
                            Text(b.createdAt!,
                                style: const TextStyle(
                                    fontSize: 11.5, color: AppColors.muted,),),
                        ],
                      ),
                    ),
                    if (b.rating > 0) _RatingPill(rating: b.rating),
                  ],
                ),
                const SizedBox(height: 12),
                if (b.typeLabel != null) ...[
                  _Tag(b.typeLabel!),
                  const SizedBox(height: 8),
                ],
                Text(b.subject,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                        fontSize: 15.5,
                        fontWeight: FontWeight.w700,
                        color: AppColors.ink,),),
                if (b.excerpt != null && b.excerpt!.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(b.excerpt!,
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                          fontSize: 14, height: 1.5, color: AppColors.ink3,),),
                ],
                const SizedBox(height: 12),
                Row(
                  children: [
                    Icon(
                      b.isLiked ? Icons.favorite : Icons.favorite_border,
                      size: 15,
                      color: b.isLiked ? AppColors.accent : AppColors.muted,
                    ),
                    const SizedBox(width: 4),
                    Text('도움돼요 ${b.likeCount}',
                        style: const TextStyle(
                            fontSize: 12.5, color: AppColors.muted,),),
                    const SizedBox(width: 14),
                    const Icon(Icons.mode_comment_outlined,
                        size: 15, color: AppColors.muted,),
                    const SizedBox(width: 4),
                    Text('${b.commentCount}',
                        style: const TextStyle(
                            fontSize: 12.5, color: AppColors.muted,),),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _RatingPill extends StatelessWidget {
  const _RatingPill({required this.rating});

  final double rating;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        const Icon(Icons.star, size: 15, color: AppColors.star),
        const SizedBox(width: 2),
        Text(rating.toStringAsFixed(1),
            style: const TextStyle(
                fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink2,),),
      ],
    );
  }
}

class _Tag extends StatelessWidget {
  const _Tag(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: AppColors.accentTint,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(text,
          style: const TextStyle(
              fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.accent,),),
    );
  }
}
