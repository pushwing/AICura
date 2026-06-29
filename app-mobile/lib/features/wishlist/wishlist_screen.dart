import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import '../../core/theme/app_colors.dart';
import '../auth/auth_provider.dart';
import '../auth/login_screen.dart';
import '../events/event_detail_screen.dart';
import '../mypage/me_repository.dart';
import '../mypage/models/like_item.dart';
import '../shell/main_shell.dart';

/// 찜 화면 — 내가 찜한 이벤트(GET /me/likes). 로그인 필요.
class WishlistScreen extends StatefulWidget {
  const WishlistScreen({super.key});

  @override
  State<WishlistScreen> createState() => _WishlistScreenState();
}

class _WishlistScreenState extends State<WishlistScreen> {
  /// MainShell 탭 순서상 찜 탭 인덱스 (홈/검색/후기/찜/마이)
  static const _tabIndex = 3;

  List<LikeItem> _items = [];
  bool _loading = false;
  bool _loaded = false;
  bool _wasActive = false;
  String? _error;

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final items = await context.read<MeRepository>().likes();
      if (!mounted) return;
      setState(() {
        _items = items;
        _loaded = true;
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

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    // 찜 탭이 현재 활성인지 (홈에서 찜 후 탭 전환 시 최신화하기 위함)
    final active = context.watch<ShellController>().index == _tabIndex;

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      if (!auth.isAuthenticated) {
        if (_loaded || _items.isNotEmpty) {
          setState(() {
            _items = [];
            _loaded = false;
          });
        }
        _wasActive = active;
        return;
      }
      // 최초 진입 + 찜 탭으로 새로 전환될 때마다 서버에서 다시 불러온다.
      final justEntered = active && !_wasActive;
      if (!_loading && ((active && !_loaded) || justEntered)) {
        _load();
      }
      _wasActive = active;
    });

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const Text('찜 '),
            Text('${_items.length}',
                style: const TextStyle(color: AppColors.accent),),
          ],
        ),
      ),
      body: auth.isAuthenticated ? _buildAuthed() : const _LoginPrompt(),
    );
  }

  Widget _buildAuthed() {
    if (_loading && !_loaded) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_error != null && !_loaded) {
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
    if (_items.isEmpty) {
      return const _EmptyWish();
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(vertical: 8),
        itemCount: _items.length,
        separatorBuilder: (_, __) =>
            const Divider(height: 1, indent: 18, endIndent: 18),
        itemBuilder: (context, i) => _WishTile(item: _items[i]),
      ),
    );
  }
}

class _WishTile extends StatelessWidget {
  const _WishTile({required this.item});

  final LikeItem item;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute<void>(
          builder: (_) => EventDetailScreen(eventId: item.id),
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
        child: Row(
          children: [
            Container(
              width: 96,
              height: 96,
              decoration: BoxDecoration(
                gradient: AppColors.thumbGradient(item.id),
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(item.hospitalName,
                      style: const TextStyle(
                          fontSize: 12.5, color: AppColors.muted,),),
                  const SizedBox(height: 4),
                  Text(
                    item.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 15.5,
                      fontWeight: FontWeight.w700,
                      color: AppColors.ink,
                    ),
                  ),
                ],
              ),
            ),
            const Icon(Icons.favorite, color: AppColors.accent),
          ],
        ),
      ),
    );
  }
}

class _EmptyWish extends StatelessWidget {
  const _EmptyWish();

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.favorite_border, size: 64, color: AppColors.faint),
          SizedBox(height: 14),
          Text('아직 찜한 이벤트가 없어요',
              style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: AppColors.ink2,),),
          SizedBox(height: 6),
          Text('마음에 드는 시술을 담아보세요',
              style: TextStyle(color: AppColors.muted),),
        ],
      ),
    );
  }
}

class _LoginPrompt extends StatelessWidget {
  const _LoginPrompt();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.favorite_border, size: 64, color: AppColors.faint),
            const SizedBox(height: 12),
            const Text('로그인하고 찜 목록을 확인하세요',
                style: TextStyle(color: AppColors.muted),),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: () => requireLogin(context),
              child: const Text('로그인'),
            ),
          ],
        ),
      ),
    );
  }
}
