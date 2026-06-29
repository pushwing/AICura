import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import '../auth/login_screen.dart';
import '../call_request/call_request_repository.dart';
import 'event_repository.dart';
import 'models/event.dart';

/// 이벤트 상세 화면.
///
/// 열람은 비로그인도 가능하고, 찜·신청 시점에만 로그인을 요구한다.
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

  /// 찜 토글 — 로그인 필요
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

  /// 신청하기 — 로그인 필요. 로그인 후 상담신청 폼(바텀시트) 노출.
  Future<void> _apply() async {
    final event = _event;
    if (event == null) return;
    if (!await requireLogin(context) || !mounted) return;
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      builder: (_) => _ApplySheet(event: event),
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
        title: const Text('이벤트 상세'),
        actions: [
          if (_event != null)
            IconButton(
              onPressed: _toggleLike,
              icon: Icon(
                _event!.isLiked ? Icons.favorite : Icons.favorite_border,
                color: _event!.isLiked ? Colors.redAccent : null,
              ),
            ),
        ],
      ),
      body: _buildBody(),
      bottomNavigationBar: _event == null ? null : _buildApplyBar(),
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

    final e = _event!;
    final rate = e.discountRate;
    return ListView(
      children: [
        _HeroImage(url: e.thumbnailUrl),
        Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                '${e.hospitalName} · ${e.region}',
                style: const TextStyle(color: Colors.black54),
              ),
              const SizedBox(height: 6),
              Text(
                e.adTitle,
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),
              Row(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  if (rate != null) ...[
                    Text(
                      '$rate%',
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1D9E75),
                      ),
                    ),
                    const SizedBox(width: 8),
                  ],
                  Text(
                    '${_won.format(e.discountCost)}원',
                    style: const TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  if (rate != null) ...[
                    const SizedBox(width: 8),
                    Text(
                      '${_won.format(e.generalCost)}원',
                      style: const TextStyle(
                        color: Colors.black38,
                        decoration: TextDecoration.lineThrough,
                      ),
                    ),
                  ],
                ],
              ),
              const Divider(height: 32),
              if (e.adDetailInfo != null && e.adDetailInfo!.trim().isNotEmpty) ...[
                const Text('이벤트 정보',
                    style: TextStyle(fontWeight: FontWeight.bold),),
                const SizedBox(height: 4),
                // 저장된 HTML 을 리치 렌더링 (본문 여백 제거로 섹션 정렬)
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
                const SizedBox(height: 16),
              ],
              for (final url in e.detailImages)
                Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Image.network(
                    url,
                    errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                  ),
                ),
              const Divider(height: 32),
              const Text('병원 정보',
                  style: TextStyle(fontWeight: FontWeight.bold),),
              const SizedBox(height: 8),
              Text(e.hospitalName),
              if (e.hospitalAddress != null && e.hospitalAddress!.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(top: 4),
                  child: Text(e.hospitalAddress!,
                      style: const TextStyle(color: Colors.black54),),
                ),
              if (e.hospitalPhone != null && e.hospitalPhone!.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(top: 4),
                  child: Text(e.hospitalPhone!,
                      style: const TextStyle(color: Colors.black54),),
                ),
              const SizedBox(height: 80),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildApplyBar() {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: FilledButton(
          onPressed: _apply,
          child: const Text('신청하기'),
        ),
      ),
    );
  }
}

class _HeroImage extends StatelessWidget {
  const _HeroImage({this.url});

  final String? url;

  @override
  Widget build(BuildContext context) {
    if (url == null || url!.isEmpty) {
      return Container(
        height: 220,
        color: const Color(0xFFEDEFEF),
        child: const Icon(Icons.image_outlined,
            size: 48, color: Colors.black26,),
      );
    }
    return Image.network(
      url!,
      height: 220,
      width: double.infinity,
      fit: BoxFit.cover,
      errorBuilder: (_, __, ___) => Container(
        height: 220,
        color: const Color(0xFFEDEFEF),
        child: const Icon(Icons.broken_image_outlined,
            size: 48, color: Colors.black26,),
      ),
    );
  }
}

/// 상담 신청 입력 시트.
class _ApplySheet extends StatefulWidget {
  const _ApplySheet({required this.event});

  final Event event;

  @override
  State<_ApplySheet> createState() => _ApplySheetState();
}

class _ApplySheetState extends State<_ApplySheet> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _content = TextEditingController();
  bool _agree = false;
  bool _busy = false;

  @override
  void dispose() {
    _name.dispose();
    _phone.dispose();
    _content.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (!_agree) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('개인정보 수집·이용에 동의해주세요')),
      );
      return;
    }
    setState(() => _busy = true);
    try {
      await context.read<CallRequestRepository>().apply(
            campaignId: widget.event.id,
            name: _name.text.trim(),
            phone: _phone.text.trim(),
            content: _content.text.trim(),
          );
      if (!mounted) return;
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('상담 신청이 접수되었습니다')),
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
              '${widget.event.adTitle} 상담 신청',
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
            const SizedBox(height: 12),
            TextFormField(
              controller: _content,
              maxLines: 3,
              decoration: const InputDecoration(labelText: '상담 내용 (선택)'),
            ),
            const SizedBox(height: 8),
            CheckboxListTile(
              value: _agree,
              onChanged: (v) => setState(() => _agree = v ?? false),
              contentPadding: EdgeInsets.zero,
              controlAffinity: ListTileControlAffinity.leading,
              title: const Text('개인정보 수집·이용에 동의합니다 (필수)'),
            ),
            const SizedBox(height: 8),
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
                  : const Text('신청 접수'),
            ),
          ],
        ),
      ),
    );
  }
}
