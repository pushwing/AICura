import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import '../auth/login_screen.dart';
import '../events/event_detail_screen.dart';
import '../events/event_repository.dart';
import '../events/models/event.dart';
import '../events/widgets/event_card.dart';
import 'hospital_repository.dart';
import 'models/hospital.dart';
import 'models/hospital_review.dart';

/// 병원 상세 — 정보 + 진행 이벤트 + 후기. 열람은 비로그인 허용, 찜은 로그인 필요.
class HospitalDetailScreen extends StatefulWidget {
  const HospitalDetailScreen({super.key, required this.hospitalId});

  final int hospitalId;

  @override
  State<HospitalDetailScreen> createState() => _HospitalDetailScreenState();
}

class _HospitalDetailScreenState extends State<HospitalDetailScreen> {
  Hospital? _hospital;
  List<Event> _events = [];
  List<HospitalReview> _reviews = [];
  bool _loading = true;
  String? _error;

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
    final repo = context.read<HospitalRepository>();
    try {
      final results = await (
        repo.detail(widget.hospitalId),
        repo.campaigns(widget.hospitalId),
        repo.reviews(widget.hospitalId),
      ).wait;
      if (!mounted) return;
      setState(() {
        _hospital = results.$1;
        _events = results.$2;
        _reviews = results.$3;
        _loading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.message;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = '병원 정보를 불러오지 못했습니다.';
        _loading = false;
      });
    }
  }

  /// 병원 찜 토글 — 로그인 필요
  Future<void> _toggleLike() async {
    final h = _hospital;
    if (h == null) return;
    if (!await requireLogin(context) || !mounted) return;
    try {
      final liked =
          await context.read<HospitalRepository>().toggleLike(h.id);
      if (mounted) setState(() => _hospital = h.copyWith(isLiked: liked));
    } on ApiException catch (e) {
      _snack(e.message);
    }
  }

  /// 이벤트 찜 토글 — 로그인 필요 (병원 상세 내 진행 이벤트)
  Future<void> _toggleEventLike(Event event) async {
    if (!await requireLogin(context) || !mounted) return;
    final idx = _events.indexWhere((e) => e.id == event.id);
    if (idx == -1) return;
    final before = _events[idx];
    setState(() => _events[idx] = before.copyWith(isLiked: !before.isLiked));
    try {
      final liked = await context.read<EventRepository>().toggleLike(event.id);
      if (mounted) setState(() => _events[idx] = before.copyWith(isLiked: liked));
    } on ApiException {
      if (mounted) setState(() => _events[idx] = before);
    }
  }

  void _snack(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('병원'),
        actions: [
          if (_hospital != null)
            IconButton(
              onPressed: _toggleLike,
              icon: Icon(
                _hospital!.isLiked ? Icons.favorite : Icons.favorite_border,
                color: _hospital!.isLiked ? Colors.redAccent : null,
              ),
            ),
        ],
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
            OutlinedButton(onPressed: _load, child: const Text('다시 시도')),
          ],
        ),
      );
    }

    final h = _hospital!;
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        children: [
          _Header(hospital: h),
          const Divider(height: 8, thickness: 8, color: Color(0xFFF0F2F2)),
          const _SectionTitle('진행 이벤트'),
          if (_events.isEmpty)
            const _EmptyHint('진행 중인 이벤트가 없습니다')
          else
            ..._events.map(
              (e) => EventCard(
                event: e,
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute<void>(
                    builder: (_) => EventDetailScreen(eventId: e.id),
                  ),
                ),
                onToggleLike: () => _toggleEventLike(e),
              ),
            ),
          const Divider(height: 8, thickness: 8, color: Color(0xFFF0F2F2)),
          _SectionTitle('후기 ${_reviews.length}'),
          if (_reviews.isEmpty)
            const _EmptyHint('등록된 후기가 없습니다')
          else
            ..._reviews.map((r) => _ReviewTile(review: r)),
          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.hospital});

  final Hospital hospital;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      color: Colors.white,
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(hospital.name,
              style:
                  const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),),
          const SizedBox(height: 6),
          Row(
            children: [
              if (hospital.typeLabel != null)
                Text(hospital.typeLabel!,
                    style: const TextStyle(color: Colors.black54),),
              if (hospital.rating > 0) ...[
                const SizedBox(width: 8),
                const Icon(Icons.star, size: 16, color: Colors.amber),
                const SizedBox(width: 2),
                Text(
                  '${hospital.rating.toStringAsFixed(1)} (${hospital.reviewCount})',
                  style: const TextStyle(color: Colors.black54),
                ),
              ],
            ],
          ),
          if (hospital.address != null && hospital.address!.isNotEmpty) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                const Icon(Icons.place_outlined,
                    size: 18, color: Colors.black45,),
                const SizedBox(width: 6),
                Expanded(child: Text(hospital.address!)),
              ],
            ),
          ],
          if (hospital.phone != null && hospital.phone!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Row(
              children: [
                const Icon(Icons.call_outlined, size: 18, color: Colors.black45),
                const SizedBox(width: 6),
                Text(hospital.phone!),
              ],
            ),
          ],
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
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
      child: Text(text,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),),
    );
  }
}

class _EmptyHint extends StatelessWidget {
  const _EmptyHint(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Text(text, style: const TextStyle(color: Colors.black45)),
    );
  }
}

class _ReviewTile extends StatelessWidget {
  const _ReviewTile({required this.review});

  final HospitalReview review;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(review.userName,
                  style: const TextStyle(fontWeight: FontWeight.w600),),
              const Spacer(),
              const Icon(Icons.star, size: 14, color: Colors.amber),
              const SizedBox(width: 2),
              Text(review.rating.toStringAsFixed(1),
                  style: const TextStyle(fontSize: 12),),
            ],
          ),
          if (review.subject.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(review.subject,
                style: const TextStyle(fontWeight: FontWeight.w500),),
          ],
          if (review.contents.isNotEmpty) ...[
            const SizedBox(height: 2),
            Text(review.contents,
                maxLines: 3, overflow: TextOverflow.ellipsis,),
          ],
        ],
      ),
    );
  }
}
