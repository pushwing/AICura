import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import '../auth/login_screen.dart';
import '../booking/booking_repository.dart';
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

  /// 예약하기 — 로그인 필요. 로그인 후 예약 폼(바텀시트) 노출.
  Future<void> _book() async {
    final h = _hospital;
    if (h == null) return;
    if (!await requireLogin(context) || !mounted) return;
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      builder: (_) => _BookingSheet(hospital: h),
    );
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
      bottomNavigationBar: _hospital == null
          ? null
          : SafeArea(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: FilledButton(
                  onPressed: _book,
                  child: const Text('예약하기'),
                ),
              ),
            ),
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

/// 병원 예약 입력 시트.
class _BookingSheet extends StatefulWidget {
  const _BookingSheet({required this.hospital});

  final Hospital hospital;

  @override
  State<_BookingSheet> createState() => _BookingSheetState();
}

class _BookingSheetState extends State<_BookingSheet> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _phone = TextEditingController();
  bool _busy = false;

  @override
  void dispose() {
    _name.dispose();
    _phone.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _busy = true);
    try {
      await context.read<BookingRepository>().create(
            hospitalId: widget.hospital.id,
            name: _name.text.trim(),
            phone: _phone.text.trim(),
          );
      if (!mounted) return;
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('예약이 접수되었습니다')),
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
            Text(
              '${widget.hospital.name} 예약 신청',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _name,
              decoration: const InputDecoration(labelText: '이름 *'),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? '이름을 입력해주세요' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _phone,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: '연락처 *'),
              validator: (v) =>
                  (v == null || v.trim().length < 8) ? '연락처를 확인해주세요' : null,
            ),
            const SizedBox(height: 8),
            Text(
              '예약 접수 후 병원에서 일정 확정을 위해 연락드립니다.',
              style: TextStyle(color: Colors.black.withValues(alpha: 0.5), fontSize: 12),
            ),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: _busy ? null : _submit,
              child: _busy
                  ? const SizedBox(
                      height: 22,
                      width: 22,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Text('예약 접수'),
            ),
          ],
        ),
      ),
    );
  }
}
