import '../../../core/util/json_parse.dart';

/// 예약 (생성/상세/목록 공용).
class Booking {
  Booking({
    required this.id,
    required this.hospitalId,
    required this.hospitalName,
    required this.status,
    this.statusLabel,
    this.bookDate,
    this.createdAt,
  });

  final int id;
  final int hospitalId;
  final String hospitalName;
  final int status; // 0=대기, 1=확정, 2=취소
  final String? statusLabel;
  final String? bookDate;
  final String? createdAt;

  /// status_label 이 없는 목록 응답을 위한 보강 라벨
  String get label => statusLabel ?? _statusLabels[status] ?? '상태 $status';

  static const _statusLabels = {0: '대기', 1: '확정', 2: '취소'};

  factory Booking.fromJson(Map<String, dynamic> j) {
    return Booking(
      id: parseInt(j['id']),
      hospitalId: parseInt(j['hospital_id']),
      hospitalName: (j['hospital_name'] ?? '').toString(),
      status: parseInt(j['status']),
      statusLabel: j['status_label'] as String?,
      bookDate: j['book_date'] as String?,
      createdAt: j['created_at'] as String?,
    );
  }
}
