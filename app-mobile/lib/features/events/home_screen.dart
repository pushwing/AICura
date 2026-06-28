import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../auth/auth_provider.dart';
import 'event_provider.dart';
import 'models/event.dart';
import 'widgets/event_card.dart';

/// 앱 최초 실행 화면 — 이벤트 리스트(홈).
///
/// 상단 배너 + 카테고리 칩 + 정렬 + 본문 목록(무한 스크롤)으로 구성된다.
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _scroll = ScrollController();

  @override
  void initState() {
    super.initState();
    // 첫 프레임 후 데이터 로드
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<EventProvider>().bootstrap();
    });
    _scroll.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scroll
      ..removeListener(_onScroll)
      ..dispose();
    super.dispose();
  }

  void _onScroll() {
    // 하단 300px 근처에서 다음 페이지 적재
    if (_scroll.position.pixels >=
        _scroll.position.maxScrollExtent - 300) {
      context.read<EventProvider>().loadMore();
    }
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<EventProvider>();
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'AICura',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            color: Color(0xFF0F6E56),
          ),
        ),
        actions: [
          IconButton(
            tooltip: '로그아웃',
            onPressed: () => context.read<AuthProvider>().logout(),
            icon: const Icon(Icons.logout),
          ),
        ],
      ),
      body: _buildBody(provider),
    );
  }

  Widget _buildBody(EventProvider provider) {
    if (provider.initialLoading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (provider.error != null && provider.items.isEmpty) {
      return _ErrorView(
        message: provider.error!,
        onRetry: provider.refresh,
      );
    }

    return RefreshIndicator(
      onRefresh: provider.refresh,
      child: CustomScrollView(
        controller: _scroll,
        slivers: [
          if (provider.banners.isNotEmpty)
            SliverToBoxAdapter(child: _BannerCarousel(events: provider.banners)),
          SliverToBoxAdapter(
            child: _CategoryBar(
              categories: provider.categories,
              selectedId: provider.selectedCategoryId,
              onSelect: provider.selectCategory,
            ),
          ),
          SliverToBoxAdapter(
            child: _SortBar(
              sort: provider.sort,
              total: provider.items.length,
              onChange: provider.changeSort,
            ),
          ),
          if (provider.items.isEmpty)
            const SliverFillRemaining(
              hasScrollBody: false,
              child: Center(child: Text('표시할 이벤트가 없습니다')),
            )
          else
            SliverList.separated(
              itemCount: provider.items.length + (provider.hasMore ? 1 : 0),
              separatorBuilder: (_, __) =>
                  const Divider(height: 1, indent: 16, endIndent: 16),
              itemBuilder: (context, index) {
                if (index >= provider.items.length) {
                  return const Padding(
                    padding: EdgeInsets.all(16),
                    child: Center(child: CircularProgressIndicator()),
                  );
                }
                final event = provider.items[index];
                return EventCard(
                  event: event,
                  onTap: () => _openDetail(event),
                  onToggleLike: () => provider.toggleLike(event),
                );
              },
            ),
        ],
      ),
    );
  }

  void _openDetail(Event event) {
    // 상세 화면은 이슈 #117의 다음 증분에서 연결 (GET /campaigns/{id})
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('상세 화면 준비 중: ${event.adTitle}')),
    );
  }
}

/// 상단 배너 캐러셀 (메인 노출 이벤트)
class _BannerCarousel extends StatelessWidget {
  const _BannerCarousel({required this.events});

  final List<Event> events;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 160,
      child: PageView.builder(
        controller: PageController(viewportFraction: 0.9),
        itemCount: events.length,
        itemBuilder: (context, i) {
          final e = events[i];
          return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Stack(
                fit: StackFit.expand,
                children: [
                  if (e.thumbnailUrl != null)
                    Image.network(
                      e.thumbnailUrl!,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) =>
                          Container(color: const Color(0xFF0F6E56)),
                    )
                  else
                    Container(color: const Color(0xFF0F6E56)),
                  // 가독성용 그라데이션
                  const DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [Colors.transparent, Colors.black54],
                      ),
                    ),
                  ),
                  Positioned(
                    left: 16,
                    bottom: 16,
                    right: 16,
                    child: Text(
                      e.adTitle,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}

/// 카테고리 칩 가로 스크롤
class _CategoryBar extends StatelessWidget {
  const _CategoryBar({
    required this.categories,
    required this.selectedId,
    required this.onSelect,
  });

  final List categories;
  final int selectedId;
  final void Function(int) onSelect;

  @override
  Widget build(BuildContext context) {
    if (categories.isEmpty) return const SizedBox.shrink();
    return SizedBox(
      height: 48,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12),
        children: [
          _chip('전체', selectedId == 0, () => onSelect(0)),
          for (final c in categories)
            _chip(c.title as String, selectedId == c.id, () => onSelect(c.id)),
        ],
      ),
    );
  }

  Widget _chip(String label, bool selected, VoidCallback onTap) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 6),
      child: ChoiceChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) => onTap(),
        selectedColor: const Color(0xFF1D9E75),
        labelStyle: TextStyle(
          color: selected ? Colors.white : Colors.black87,
        ),
      ),
    );
  }
}

/// 정렬 선택 바
class _SortBar extends StatelessWidget {
  const _SortBar({
    required this.sort,
    required this.total,
    required this.onChange,
  });

  final EventSort sort;
  final int total;
  final void Function(EventSort) onChange;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 4, 8, 4),
      child: Row(
        children: [
          Text('이벤트 $total', style: const TextStyle(color: Colors.black54)),
          const Spacer(),
          DropdownButton<EventSort>(
            value: sort,
            underline: const SizedBox.shrink(),
            items: [
              for (final s in EventSort.values)
                DropdownMenuItem(value: s, child: Text(s.label)),
            ],
            onChanged: (v) => v != null ? onChange(v) : null,
          ),
        ],
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  const _ErrorView({required this.message, required this.onRetry});

  final String message;
  final Future<void> Function() onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.error_outline, size: 48, color: Colors.black26),
          const SizedBox(height: 12),
          Text(message, textAlign: TextAlign.center),
          const SizedBox(height: 16),
          OutlinedButton(onPressed: onRetry, child: const Text('다시 시도')),
        ],
      ),
    );
  }
}
