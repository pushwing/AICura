import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../community/community_list_screen.dart';
import '../events/home_screen.dart';
import '../mypage/my_page_screen.dart';
import '../search/search_screen.dart';
import '../wishlist/wishlist_screen.dart';

/// 탭 전환 컨트롤러 — 다른 화면(검색 등)에서 탭 이동을 요청할 때 사용.
class ShellController extends ChangeNotifier {
  int index = 0;

  void go(int i) {
    if (index == i) return;
    index = i;
    notifyListeners();
  }
}

/// 앱 메인 셸 — 하단 탭(홈/검색/후기/찜/마이).
class MainShell extends StatefulWidget {
  const MainShell({super.key});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  final ShellController _controller = ShellController();

  static const _tabs = [
    HomeScreen(),
    SearchScreen(),
    CommunityListScreen(),
    WishlistScreen(),
    MyPageScreen(),
  ];

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider.value(
      value: _controller,
      child: AnimatedBuilder(
        animation: _controller,
        builder: (context, _) {
          return Scaffold(
            body: IndexedStack(index: _controller.index, children: _tabs),
            bottomNavigationBar: NavigationBar(
              selectedIndex: _controller.index,
              onDestinationSelected: _controller.go,
              indicatorColor: AppColors.accentTint,
              destinations: const [
                NavigationDestination(
                  icon: Icon(Icons.home_outlined),
                  selectedIcon: Icon(Icons.home, color: AppColors.accent),
                  label: '홈',
                ),
                NavigationDestination(
                  icon: Icon(Icons.search),
                  selectedIcon: Icon(Icons.search, color: AppColors.accent),
                  label: '검색',
                ),
                NavigationDestination(
                  icon: Icon(Icons.forum_outlined),
                  selectedIcon: Icon(Icons.forum, color: AppColors.accent),
                  label: '후기',
                ),
                NavigationDestination(
                  icon: Icon(Icons.favorite_border),
                  selectedIcon: Icon(Icons.favorite, color: AppColors.accent),
                  label: '찜',
                ),
                NavigationDestination(
                  icon: Icon(Icons.person_outline),
                  selectedIcon: Icon(Icons.person, color: AppColors.accent),
                  label: '마이',
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
