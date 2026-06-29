import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/event.dart';

/// 이벤트 리스트 한 줄(카드).
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
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 썸네일
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: _Thumbnail(url: event.thumbnailUrl),
            ),
            const SizedBox(width: 12),
            // 본문
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '${event.hospitalName} · ${event.region}',
                    style: const TextStyle(
                      fontSize: 12,
                      color: Colors.black54,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    event.adTitle,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      if (rate != null) ...[
                        Text(
                          '$rate%',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF1D9E75),
                          ),
                        ),
                        const SizedBox(width: 6),
                      ],
                      Text(
                        '${_won.format(event.discountCost)}원',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            // 찜 하트
            IconButton(
              onPressed: onToggleLike,
              icon: Icon(
                event.isLiked ? Icons.favorite : Icons.favorite_border,
                color: event.isLiked ? Colors.redAccent : Colors.black38,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Thumbnail extends StatelessWidget {
  const _Thumbnail({this.url});

  final String? url;

  @override
  Widget build(BuildContext context) {
    const size = 96.0;
    if (url == null || url!.isEmpty) {
      return Container(
        width: size,
        height: size,
        color: const Color(0xFFEDEFEF),
        child: const Icon(Icons.image_outlined, color: Colors.black26),
      );
    }
    return Image.network(
      url!,
      width: size,
      height: size,
      fit: BoxFit.cover,
      errorBuilder: (_, __, ___) => Container(
        width: size,
        height: size,
        color: const Color(0xFFEDEFEF),
        child: const Icon(Icons.broken_image_outlined, color: Colors.black26),
      ),
    );
  }
}
