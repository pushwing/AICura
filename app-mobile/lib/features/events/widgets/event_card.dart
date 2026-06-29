import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../../core/theme/app_colors.dart';
import '../models/event.dart';

/// 이벤트 카드 (세로형, 홈) — 뷰니 디자인.
///
/// 파스텔 그라데이션 썸네일(16:10) + 부위 배지 + 찜 하트 오버레이,
/// 아래에 병원·지역 / 제목 / 가격 블록.
class EventCard extends StatelessWidget {
  const EventCard({
    super.key,
    required this.event,
    required this.onTap,
    required this.onToggleLike,
  });

  final Event event;
  final VoidCallback onTap;
  final VoidCallback onToggleLike;

  static final _won = NumberFormat('#,###');

  @override
  Widget build(BuildContext context) {
    final rate = event.discountRate;
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(18, 10, 18, 14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _Thumbnail(event: event, onToggleLike: onToggleLike),
            const SizedBox(height: 10),
            Text(
              '${event.hospitalName}${event.region.isNotEmpty ? ' · ${event.region}' : ''}',
              style: const TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w500,
                color: AppColors.muted,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              event.adTitle,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                height: 1.35,
                letterSpacing: -0.3,
                color: AppColors.ink,
              ),
            ),
            if (event.categoryTitle.isNotEmpty) ...[
              const SizedBox(height: 8),
              _Tag('#${event.categoryTitle}'),
            ],
            const SizedBox(height: 10),
            Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                if (rate != null) ...[
                  Text(
                    '$rate%',
                    style: const TextStyle(
                      fontSize: 19,
                      fontWeight: FontWeight.w800,
                      color: AppColors.accent,
                    ),
                  ),
                  const SizedBox(width: 8),
                ],
                Text(
                  '${_won.format(event.discountCost)}원',
                  style: const TextStyle(
                    fontSize: 19,
                    fontWeight: FontWeight.w800,
                    color: AppColors.ink,
                  ),
                ),
                if (rate != null) ...[
                  const SizedBox(width: 8),
                  Text(
                    '${_won.format(event.generalCost)}원',
                    style: const TextStyle(
                      fontSize: 13,
                      color: AppColors.faint,
                      decoration: TextDecoration.lineThrough,
                    ),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _Thumbnail extends StatelessWidget {
  const _Thumbnail({required this.event, required this.onToggleLike});

  final Event event;
  final VoidCallback onToggleLike;

  @override
  Widget build(BuildContext context) {
    return AspectRatio(
      aspectRatio: 16 / 10,
      child: ClipRRect(
        borderRadius: BorderRadius.circular(14),
        child: Stack(
          fit: StackFit.expand,
          children: [
            // 파스텔 그라데이션 배경 (이미지 없거나 실패해도 디자인 유지)
            DecoratedBox(
              decoration: BoxDecoration(
                gradient: AppColors.thumbGradient(event.categoryId + event.id),
              ),
            ),
            if (event.thumbnailUrl != null && event.thumbnailUrl!.isNotEmpty)
              Image.network(
                event.thumbnailUrl!,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => const SizedBox.shrink(),
              ),
            // 부위(카테고리) 배지
            if (event.categoryTitle.isNotEmpty)
              Positioned(
                left: 10,
                top: 10,
                child: Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.85),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    event.categoryTitle,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: AppColors.ink2,
                    ),
                  ),
                ),
              ),
            // 찜 하트
            Positioned(
              right: 8,
              top: 8,
              child: Material(
                color: Colors.white.withValues(alpha: 0.55),
                shape: const CircleBorder(),
                child: InkWell(
                  customBorder: const CircleBorder(),
                  onTap: onToggleLike,
                  child: SizedBox(
                    width: 34,
                    height: 34,
                    child: Icon(
                      event.isLiked ? Icons.favorite : Icons.favorite_border,
                      size: 19,
                      color: event.isLiked ? AppColors.accent : Colors.white,
                    ),
                  ),
                ),
              ),
            ),
          ],
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
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: AppColors.band,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        text,
        style: const TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w500,
          color: AppColors.ink3,
        ),
      ),
    );
  }
}
