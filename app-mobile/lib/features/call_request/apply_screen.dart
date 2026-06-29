import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import '../../core/theme/app_colors.dart';
import '../events/models/event.dart';
import 'call_request_repository.dart';

/// 예약(상담) 신청 폼 — 희망 날짜·시간 칩 + 신청자 정보 + 동의.
class ApplyScreen extends StatefulWidget {
  const ApplyScreen({super.key, required this.event});

  final Event event;

  @override
  State<ApplyScreen> createState() => _ApplyScreenState();
}

class _ApplyScreenState extends State<ApplyScreen> {
  static final _won = NumberFormat('#,###');
  static const _times = [
    '10:00',
    '11:30',
    '14:00',
    '15:30',
    '17:00',
    '18:30',
  ];

  final _name = TextEditingController();
  final _phone = TextEditingController();
  late final List<DateTime> _dates;
  int? _dateIdx;
  int? _timeIdx;
  bool _agree = false;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    final today = DateTime.now();
    _dates = List.generate(6, (i) => today.add(Duration(days: i)));
  }

  @override
  void dispose() {
    _name.dispose();
    _phone.dispose();
    super.dispose();
  }

  String _dateLabel(int i) {
    final d = _dates[i];
    final md = '${d.month}/${d.day}';
    if (i == 0) return '오늘 $md';
    if (i == 1) return '내일 $md';
    const wd = ['월', '화', '수', '목', '금', '토', '일'];
    return '$md (${wd[d.weekday - 1]})';
  }

  Future<void> _submit() async {
    if (_dateIdx == null ||
        _timeIdx == null ||
        _name.text.trim().isEmpty ||
        _phone.text.trim().length < 8 ||
        !_agree) {
      _toast('필수 정보를 모두 입력해 주세요');
      return;
    }
    setState(() => _busy = true);
    final d = _dates[_dateIdx!];
    final callTime =
        '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')} ${_times[_timeIdx!]}';
    try {
      await context.read<CallRequestRepository>().apply(
            campaignId: widget.event.id,
            name: _name.text.trim(),
            phone: _phone.text.trim(),
            callTime: callTime,
          );
      if (!mounted) return;
      Navigator.of(context).pop();
      _toast('예약 신청이 접수되었어요');
    } on ApiException catch (e) {
      if (mounted) {
        setState(() => _busy = false);
        _toast(e.message);
      }
    }
  }

  void _toast(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  @override
  Widget build(BuildContext context) {
    final e = widget.event;
    final rate = e.discountRate;
    return Scaffold(
      appBar: AppBar(title: const Text('예약 신청')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(18, 12, 18, 24),
        children: [
          // 선택 이벤트 미니 요약
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.bg,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Row(
              children: [
                Container(
                  width: 56,
                  height: 56,
                  decoration: BoxDecoration(
                    gradient: AppColors.thumbGradient(e.categoryId + e.id),
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('${e.hospitalName} · ${e.region}',
                          style: const TextStyle(
                              fontSize: 12, color: AppColors.muted,),),
                      const SizedBox(height: 2),
                      Text(e.adTitle,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                              fontSize: 14, fontWeight: FontWeight.w700,),),
                      const SizedBox(height: 2),
                      Text(
                        '${rate != null ? '$rate% ' : ''}${_won.format(e.discountCost)}원',
                        style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w800,
                            color: AppColors.accent,),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          const _Label('희망 날짜'),
          const SizedBox(height: 10),
          _ChipGrid(
            count: _dates.length,
            labelOf: _dateLabel,
            selected: _dateIdx,
            onSelect: (i) => setState(() => _dateIdx = i),
          ),
          const SizedBox(height: 22),
          const _Label('희망 시간'),
          const SizedBox(height: 10),
          _ChipGrid(
            count: _times.length,
            labelOf: (i) => _times[i],
            selected: _timeIdx,
            onSelect: (i) => setState(() => _timeIdx = i),
          ),
          const SizedBox(height: 22),
          const _Label('신청자 정보'),
          const SizedBox(height: 10),
          TextField(
            controller: _name,
            decoration: const InputDecoration(hintText: '이름'),
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _phone,
            keyboardType: TextInputType.phone,
            decoration: const InputDecoration(hintText: '휴대폰 번호'),
          ),
          const SizedBox(height: 14),
          InkWell(
            onTap: () => setState(() => _agree = !_agree),
            child: Row(
              children: [
                _AgreeBox(checked: _agree),
                const SizedBox(width: 10),
                const Expanded(
                  child: Text(
                    '개인정보 수집 및 병원 상담 연결에 동의합니다 (필수)',
                    style: TextStyle(fontSize: 13, color: AppColors.ink3),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: FilledButton(
            onPressed: _busy ? null : _submit,
            child: _busy
                ? const SizedBox(
                    height: 22,
                    width: 22,
                    child: CircularProgressIndicator(
                        strokeWidth: 2, color: Colors.white,),
                  )
                : const Text('신청 완료하기'),
          ),
        ),
      ),
    );
  }
}

class _Label extends StatelessWidget {
  const _Label(this.text);
  final String text;
  @override
  Widget build(BuildContext context) => Text(
        text,
        style: const TextStyle(
            fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink,),
      );
}

/// 3열 칩 그리드 (날짜/시간 공용)
class _ChipGrid extends StatelessWidget {
  const _ChipGrid({
    required this.count,
    required this.labelOf,
    required this.selected,
    required this.onSelect,
  });

  final int count;
  final String Function(int) labelOf;
  final int? selected;
  final void Function(int) onSelect;

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: count,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        mainAxisSpacing: 10,
        crossAxisSpacing: 10,
        childAspectRatio: 2.6,
      ),
      itemBuilder: (context, i) {
        final on = selected == i;
        return GestureDetector(
          onTap: () => onSelect(i),
          child: Container(
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: on ? AppColors.accentTint : Colors.white,
              borderRadius: BorderRadius.circular(11),
              border: Border.all(
                color: on ? AppColors.accent : AppColors.lineStrong,
              ),
            ),
            child: Text(
              labelOf(i),
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: on ? AppColors.accent : AppColors.ink2,
              ),
            ),
          ),
        );
      },
    );
  }
}

class _AgreeBox extends StatelessWidget {
  const _AgreeBox({required this.checked});
  final bool checked;
  @override
  Widget build(BuildContext context) {
    return Container(
      width: 22,
      height: 22,
      decoration: BoxDecoration(
        color: checked ? AppColors.accent : Colors.white,
        borderRadius: BorderRadius.circular(7),
        border: Border.all(
          color: checked ? AppColors.accent : AppColors.lineStrong,
        ),
      ),
      child: checked
          ? const Icon(Icons.check, size: 15, color: Colors.white)
          : null,
    );
  }
}
