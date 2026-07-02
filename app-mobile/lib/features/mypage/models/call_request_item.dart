import '../../../core/util/json_parse.dart';

/// 내 상담 신청 항목 (GET /me/call-requests).
class CallRequestItem {
  CallRequestItem({
    required this.id,
    required this.campaignId,
    required this.campaignTitle,
    required this.status,
    required this.statusLabel,
    this.createdAt,
  });

  final int id;
  final int campaignId;
  final String campaignTitle;
  final int status;
  final String statusLabel;
  final String? createdAt;

  factory CallRequestItem.fromJson(Map<String, dynamic> j) {
    return CallRequestItem(
      id: parseInt(j['id']),
      campaignId: parseInt(j['campaign_id']),
      campaignTitle: (j['campaign_title'] ?? '').toString(),
      status: parseInt(j['status']),
      statusLabel: (j['status_label'] ?? '').toString(),
      createdAt: j['created_at'] as String?,
    );
  }
}
