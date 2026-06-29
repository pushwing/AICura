import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import '../../core/theme/app_colors.dart';
import '../auth/login_screen.dart';
import '../call_request/apply_screen.dart';
import '../community/board_repository.dart';
import '../hospital/hospital_detail_screen.dart';
import 'event_repository.dart';
import 'models/event.dart';

/// 이벤트 상세 — 디자인 B(몰입 히어로 + sticky 세그먼트 탭).
/// 열람은 비로그인, 찜·예약·후기작성은 로그인 필요.
class EventDetailScreen extends StatefulWidget {
  const EventDetailScreen({super.key, required this.eventId});

  final int eventId;

  @override
  State<EventDetailScreen> createState() => _EventDetailScreenState();
}

class _EventDetailScreenState extends State<EventDetailScreen> {
  static final _won = NumberFormat('#,###');

  Event? _event;
  bool _loading = true;
  String? _error;
  int _segment = 0; // 0 정보 / 1 후기 / 2 병원정보

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final event = await context.read<EventRepository>().detail(widget.eventId);
      if (!mounted) return;
      setState(() {
        _event = event;
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

  Future<void> _toggleLike() async {
    final event = _event;
    if (event == null) return;
    if (!await requireLogin(context) || !mounted) return;
    try {
      final liked = await context.read<EventRepository>().toggleLike(event.id);
      if (mounted) setState(() => _event = event.copyWith(isLiked: liked));
    } on ApiException catch (e) {
      _snack(e.message);
    }
  }

  Future<void> _apply() async {
    final event = _event;
    if (event == null) return;
    if (!await requireLogin(context) || !mounted) return;
    await Navigator.of(context).push(
      MaterialPageRoute<void>(builder: (_) => ApplyScreen(event: event)),
    );
  }

  Future<void> _writeReview() async {
    final event = _event;
    if (event == null) return;
    if (!await requireLogin(context) || !mounted) return;
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      builder: (_) => _ReviewWriteSheet(event: event),
    );
  }

  void _snack(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }
    if (_error != null || _event == null) {
      return Scaffold(
        appBar: AppBar(),
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(_error ?? '이벤트를 찾을 수 없습니다'),
              const SizedBox(height: 12),
              OutlinedButton(onPressed: _load, child: const Text('다시 시도')),
            ],
          ),
        ),
      );
    }

    final e = _event!;
    return Scaffold(
      body: Column(
        children: [
          Expanded(
            child: ListView(
              padding: EdgeInsets.zero,
              children: [
                _Hero(event: e),
                _SegmentBar(
                  segment: _segment,
                  onSelect: (i) => setState(() => _segment = i),
                ),
                _buildSegmentBody(e),
              ],
            ),
          ),
          _buildCta(e),
        ],
      ),
    );
  }

  Widget _buildSegmentBody(Event e) {
    switch (_segment) {
      case 1:
        return _ReviewSegment(onWrite: _writeReview);
      case 2:
        return _HospitalSegment(event: e);
      default:
        return _InfoSegment(event: e);
    }
  }

  Widget _buildCta(Event e) {
    final rate = e.discountRate;
    return SafeArea(
      child: Container(
        padding: const EdgeInsets.fromLTRB(14, 10, 14, 10),
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: AppColors.line)),
        ),
        child: Row(
          children: [
            IconButton(
              onPressed: _toggleLike,
              icon: Icon(
                e.isLiked ? Icons.favorite : Icons.favorite_border,
                color: e.isLiked ? AppColors.accent : AppColors.faint,
              ),
            ),
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (rate != null)
                  Text(
                    '$rate%',
                    style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: AppColors.accent,),
                  ),
                Text(
                  '${_won.format(e.discountCost)}원',
                  style: const TextStyle(
                      fontSize: 16, fontWeight: FontWeight.w800,),
                ),
              ],
            ),
            const SizedBox(width: 14),
            Expanded(
              child: FilledButton(
                onPressed: _apply,
                child: const Text('예약 신청하기'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// 정보 세그먼트 — 제목·가격카드·이벤트 정보(HTML)
class _InfoSegment extends StatelessWidget {
  const _InfoSegment({required this.event});

  final Event event;
  static final _won = NumberFormat('#,###');

  @override
  Widget build(BuildContext context) {
    final e = event;
    final rate = e.discountRate;
    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 16, 18, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            '${e.hospitalName} · ${e.region}',
            style: const TextStyle(fontSize: 13, color: AppColors.muted),
          ),
          const SizedBox(height: 6),
          Text(
            e.adTitle,
            style: const TextStyle(
                fontSize: 21,
                fontWeight: FontWeight.w800,
                height: 1.3,
                letterSpacing: -0.4,),
          ),
          const SizedBox(height: 14),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.accentTint,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    if (rate != null) ...[
                      Text(
                        '$rate%',
                        style: const TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.w800,
                            color: AppColors.accent,),
                      ),
                      const SizedBox(width: 10),
                    ],
                    Text(
                      '${_won.format(e.discountCost)}원',
                      style: const TextStyle(
                          fontSize: 24, fontWeight: FontWeight.w800,),
                    ),
                    if (rate != null) ...[
                      const SizedBox(width: 8),
                      Text(
                        '${_won.format(e.generalCost)}원',
                        style: const TextStyle(
                            fontSize: 14,
                            color: AppColors.faint,
                            decoration: TextDecoration.lineThrough,),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 6),
                const Text(
                  'VAT 포함 · 결제 전 상담 후 확정',
                  style: TextStyle(fontSize: 12, color: AppColors.ink3),
                ),
              ],
            ),
          ),
          if (e.adDetailInfo != null && e.adDetailInfo!.trim().isNotEmpty) ...[
            const SizedBox(height: 22),
            const Text('이벤트 안내',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),),
            const SizedBox(height: 4),
            Html(
              data: e.adDetailInfo!,
              style: {
                'body': Style(
                  margin: Margins.zero,
                  padding: HtmlPaddings.zero,
                  fontSize: FontSize(15),
                ),
              },
            ),
          ],
          for (final url in e.detailImages)
            Padding(
              padding: const EdgeInsets.only(top: 8),
              child: Image.network(url,
                  errorBuilder: (_, __, ___) => const SizedBox.shrink(),),
            ),
        ],
      ),
    );
  }
}

/// 후기 세그먼트 (간략) — 후기 작성 진입
class _ReviewSegment extends StatelessWidget {
  const _ReviewSegment({required this.onWrite});

  final VoidCallback onWrite;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 28, 18, 28),
      child: Column(
        children: [
          const Icon(Icons.rate_review_outlined,
              size: 48, color: AppColors.faint,),
          const SizedBox(height: 12),
          const Text('이 이벤트의 후기를 남겨보세요',
              style: TextStyle(color: AppColors.muted),),
          const SizedBox(height: 16),
          OutlinedButton.icon(
            onPressed: onWrite,
            icon: const Icon(Icons.edit_outlined, size: 18),
            label: const Text('후기 작성'),
          ),
        ],
      ),
    );
  }
}

/// 병원정보 세그먼트
class _HospitalSegment extends StatelessWidget {
  const _HospitalSegment({required this.event});

  final Event event;

  @override
  Widget build(BuildContext context) {
    final e = event;
    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 18, 18, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(e.hospitalName,
              style:
                  const TextStyle(fontSize: 17, fontWeight: FontWeight.w700),),
          if (e.hospitalAddress != null && e.hospitalAddress!.isNotEmpty) ...[
            const SizedBox(height: 10),
            Row(children: [
              const Icon(Icons.place_outlined,
                  size: 18, color: AppColors.muted,),
              const SizedBox(width: 6),
              Expanded(child: Text(e.hospitalAddress!)),
            ],),
          ],
          if (e.hospitalPhone != null && e.hospitalPhone!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Row(children: [
              const Icon(Icons.call_outlined, size: 18, color: AppColors.muted),
              const SizedBox(width: 6),
              Text(e.hospitalPhone!),
            ],),
          ],
          const SizedBox(height: 16),
          OutlinedButton(
            onPressed: () => Navigator.of(context).push(
              MaterialPageRoute<void>(
                builder: (_) => HospitalDetailScreen(hospitalId: e.hospitalId),
              ),
            ),
            child: const Text('병원 상세 보기'),
          ),
        ],
      ),
    );
  }
}

/// 몰입 히어로 (그라데이션 + 부위 배지 + 뒤로/공유)
class _Hero extends StatelessWidget {
  const _Hero({required this.event});

  final Event event;

  @override
  Widget build(BuildContext context) {
    final e = event;
    final top = MediaQuery.of(context).padding.top;
    return SizedBox(
      height: 300,
      child: Stack(
        fit: StackFit.expand,
        children: [
          DecoratedBox(
            decoration: BoxDecoration(
              gradient: AppColors.thumbGradient(e.categoryId + e.id),
            ),
          ),
          if (e.thumbnailUrl != null && e.thumbnailUrl!.isNotEmpty)
            Image.network(
              e.thumbnailUrl!,
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => const SizedBox.shrink(),
            ),
          Positioned(
            left: 4,
            top: top + 4,
            child: _CircleBtn(
              icon: Icons.arrow_back_ios_new,
              onTap: () => Navigator.of(context).maybePop(),
            ),
          ),
          Positioned(
            right: 4,
            top: top + 4,
            child: _CircleBtn(icon: Icons.ios_share, onTap: () {}),
          ),
          if (e.categoryTitle.isNotEmpty)
            Positioned(
              left: 18,
              bottom: 16,
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 11, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.85),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  e.categoryTitle,
                  style: const TextStyle(
                      fontSize: 12, fontWeight: FontWeight.w700,),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _CircleBtn extends StatelessWidget {
  const _CircleBtn({required this.icon, required this.onTap});

  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white.withValues(alpha: 0.85),
      shape: const CircleBorder(),
      child: InkWell(
        customBorder: const CircleBorder(),
        onTap: onTap,
        child: SizedBox(
          width: 38,
          height: 38,
          child: Icon(icon, size: 18, color: AppColors.ink),
        ),
      ),
    );
  }
}

/// 세그먼트 탭 바 (정보 / 후기 / 병원정보)
class _SegmentBar extends StatelessWidget {
  const _SegmentBar({required this.segment, required this.onSelect});

  final int segment;
  final void Function(int) onSelect;
  static const _labels = ['정보', '후기', '병원정보'];

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white,
      height: 48,
      child: Row(
        children: [
          for (var i = 0; i < _labels.length; i++)
            Expanded(
              child: GestureDetector(
                onTap: () => onSelect(i),
                behavior: HitTestBehavior.opaque,
                child: Container(
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    border: Border(
                      bottom: BorderSide(
                        color: segment == i ? AppColors.accent : AppColors.line,
                        width: segment == i ? 2 : 1,
                      ),
                    ),
                  ),
                  child: Text(
                    _labels[i],
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight:
                          segment == i ? FontWeight.w700 : FontWeight.w500,
                      color: segment == i ? AppColors.accent : AppColors.muted,
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

/// 이벤트 후기 작성 시트 (type=1, target_id=event.id)
class _ReviewWriteSheet extends StatefulWidget {
  const _ReviewWriteSheet({required this.event});

  final Event event;

  @override
  State<_ReviewWriteSheet> createState() => _ReviewWriteSheetState();
}

class _ReviewWriteSheetState extends State<_ReviewWriteSheet> {
  final _formKey = GlobalKey<FormState>();
  final _subject = TextEditingController();
  final _contents = TextEditingController();
  int _rating = 5;
  bool _busy = false;

  @override
  void dispose() {
    _subject.dispose();
    _contents.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _busy = true);
    try {
      await context.read<BoardRepository>().create(
            type: 1,
            targetId: widget.event.id,
            subject: _subject.text.trim(),
            contents: _contents.text.trim(),
            rating: _rating.toDouble(),
          );
      if (!mounted) return;
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('후기가 등록되었습니다')),
      );
    } on ApiException catch (e) {
      if (mounted) {
        setState(() => _busy = false);
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    return Padding(
      padding: EdgeInsets.fromLTRB(16, 16, 16, bottomInset + 16),
      child: Form(
        key: _formKey,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('${widget.event.adTitle} 후기 작성',
                style:
                    const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),),
            const SizedBox(height: 12),
            Row(
              children: [
                for (var i = 1; i <= 5; i++)
                  IconButton(
                    onPressed: () => setState(() => _rating = i),
                    visualDensity: VisualDensity.compact,
                    icon: Icon(
                      i <= _rating ? Icons.star : Icons.star_border,
                      color: AppColors.star,
                    ),
                  ),
                const SizedBox(width: 4),
                Text('$_rating.0'),
              ],
            ),
            const SizedBox(height: 8),
            TextFormField(
              controller: _subject,
              decoration: const InputDecoration(labelText: '제목 *'),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? '제목을 입력해주세요' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _contents,
              maxLines: 4,
              decoration: const InputDecoration(labelText: '내용 *'),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? '내용을 입력해주세요' : null,
            ),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: _busy ? null : _submit,
              child: _busy
                  ? const SizedBox(
                      height: 22,
                      width: 22,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white,),
                    )
                  : const Text('후기 등록'),
            ),
          ],
        ),
      ),
    );
  }
}
