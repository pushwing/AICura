import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../auth/auth_provider.dart';
import '../auth/login_screen.dart';
import 'event_detail_screen.dart';
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
    if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 300) {
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
            fontSize: 22,
            fontWeight: FontWeight.w800,
            color: AppColors.accent,
            letterSpacing: -0.5,
          ),
        ),
        actions: [
          if (context.watch<AuthProvider>().isAuthenticated)
            IconButton(
              tooltip: '로그아웃',
              onPressed: () => context.read<AuthProvider>().logout(),
              icon: const Icon(Icons.logout),
            )
          else
            TextButton(
              onPressed: _login,
              child: const Text('로그인'),
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
          SliverToBoxAdapter(
            child: _SearchField(onSubmit: provider.search),
          ),
          SliverToBoxAdapter(
            child: _CategoryBar(
              categories: provider.categories,
              selectedId: provider.selectedCategoryId,
              onSelect: provider.selectCategory,
            ),
          ),
          const SliverToBoxAdapter(child: _PromoBanner()),
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
              separatorBuilder: (_, __) => const SizedBox(
                  height: 7, child: ColoredBox(color: AppColors.band)),
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
                  onToggleLike: () => _toggleLike(event),
                );
              },
            ),
        ],
      ),
    );
  }

  /// 상세 화면으로 이동 (비로그인도 열람 가능)
  void _openDetail(Event event) {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => EventDetailScreen(eventId: event.id),
      ),
    );
  }

  /// 찜 토글 — 로그인 필요. 비로그인 시 로그인 유도 후 진행.
  Future<void> _toggleLike(Event event) async {
    final ok = await requireLogin(context);
    if (!ok || !mounted) return;
    await context.read<EventProvider>().toggleLike(event);
  }

  /// 앱바 로그인 — 성공 시 목록을 새로고침해 찜 상태를 반영한다.
  Future<void> _login() async {
    final ok = await requireLogin(context);
    if (ok && mounted) await context.read<EventProvider>().refresh();
  }
}

/// 검색 필드
class _SearchField extends StatelessWidget {
  const _SearchField({required this.onSubmit});

  final void Function(String) onSubmit;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 6, 18, 10),
      child: TextField(
        onSubmitted: onSubmit,
        textInputAction: TextInputAction.search,
        decoration: InputDecoration(
          isDense: true,
          filled: true,
          fillColor: AppColors.field,
          hintText: '시술, 병원, 이벤트를 검색해 보세요',
          hintStyle: const TextStyle(color: AppColors.muted, fontSize: 14),
          prefixIcon:
              const Icon(Icons.search, color: AppColors.muted, size: 22),
          contentPadding: const EdgeInsets.symmetric(vertical: 12),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(13),
            borderSide: BorderSide.none,
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(13),
            borderSide: BorderSide.none,
          ),
        ),
      ),
    );
  }
}

/// 프로모 배너
class _PromoBanner extends StatelessWidget {
  const _PromoBanner();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 4, 18, 8),
      child: Container(
        height: 96,
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          gradient: AppColors.bannerGradient,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Stack(
          children: [
            Positioned(
              right: -10,
              top: -20,
              child: Container(
                width: 110,
                height: 110,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
              ),
            ),
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  '최대 15만원 상담 혜택',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 19,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  '지금 인기 이벤트를 둘러보세요',
                  style: TextStyle(color: Colors.white70, fontSize: 12.5),
                ),
              ],
            ),
          ],
        ),
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
      height: 46,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 14),
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
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          alignment: Alignment.center,
          padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 9),
          decoration: BoxDecoration(
            color: selected ? AppColors.accent : Colors.white,
            borderRadius: BorderRadius.circular(999),
            border: Border.all(
              color: selected ? AppColors.accent : AppColors.lineStrong,
            ),
          ),
          child: Text(
            label,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: selected ? Colors.white : const Color(0xFF5A5A66),
            ),
          ),
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
      padding: const EdgeInsets.fromLTRB(18, 8, 10, 4),
      child: Row(
        children: [
          Text(
            '이벤트 $total개',
            style: const TextStyle(
              fontSize: 13.5,
              fontWeight: FontWeight.w700,
              color: AppColors.ink2,
            ),
          ),
          const Spacer(),
          DropdownButton<EventSort>(
            value: sort,
            underline: const SizedBox.shrink(),
            isDense: true,
            style: const TextStyle(fontSize: 13, color: AppColors.ink3),
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
