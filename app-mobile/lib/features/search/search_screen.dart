import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../events/event_provider.dart';
import '../shell/main_shell.dart';

/// 검색 화면 — 카테고리 그리드 + 인기 검색어. 선택 시 홈 탭으로 이동해 필터 적용.
class SearchScreen extends StatelessWidget {
  const SearchScreen({super.key});

  static const _popular = [
    '쌍꺼풀',
    '코 성형',
    '지방흡입',
    '보톡스',
    '리프팅',
    '턱 보톡스',
  ];

  void _goHomeWithCategory(BuildContext context, int categoryId) {
    context.read<EventProvider>().selectCategory(categoryId);
    context.read<ShellController>().go(0);
  }

  void _goHomeWithKeyword(BuildContext context, String keyword) {
    context.read<EventProvider>().search(keyword);
    context.read<ShellController>().go(0);
  }

  @override
  Widget build(BuildContext context) {
    final categories = context.watch<EventProvider>().categories;
    return Scaffold(
      appBar: AppBar(title: const Text('검색')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(18, 8, 18, 24),
        children: [
          TextField(
            textInputAction: TextInputAction.search,
            onSubmitted: (v) => _goHomeWithKeyword(context, v),
            decoration: const InputDecoration(
              hintText: '시술, 병원, 이벤트를 검색해 보세요',
              prefixIcon: Icon(Icons.search, color: AppColors.muted),
            ),
          ),
          const SizedBox(height: 24),
          const _SectionTitle('부위별 시술'),
          const SizedBox(height: 12),
          GridView.count(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisCount: 4,
            mainAxisSpacing: 16,
            crossAxisSpacing: 12,
            childAspectRatio: 0.82,
            children: [
              for (var i = 0; i < categories.length; i++)
                _CategoryTile(
                  title: categories[i].title,
                  seed: categories[i].id,
                  onTap: () => _goHomeWithCategory(
                    context,
                    categories[i].id,
                  ),
                ),
            ],
          ),
          const SizedBox(height: 28),
          const _SectionTitle('인기 검색어'),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              for (final kw in _popular)
                GestureDetector(
                  onTap: () => _goHomeWithKeyword(context, kw),
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(999),
                      border: Border.all(color: AppColors.lineStrong),
                    ),
                    child: Text(
                      kw,
                      style: const TextStyle(
                        fontSize: 13,
                        color: Color(0xFF5A5A66),
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

class _CategoryTile extends StatelessWidget {
  const _CategoryTile({
    required this.title,
    required this.seed,
    required this.onTap,
  });

  final String title;
  final int seed;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            width: 58,
            height: 58,
            decoration: BoxDecoration(
              gradient: AppColors.thumbGradient(seed),
              borderRadius: BorderRadius.circular(18),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            title,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w500,
              color: AppColors.ink2,
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: const TextStyle(
        fontSize: 16,
        fontWeight: FontWeight.w800,
        color: AppColors.ink,
        letterSpacing: -0.3,
      ),
    );
  }
}
