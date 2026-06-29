import '../../core/network/api_client.dart';

/// 상담(전화) 신청 API.
class CallRequestRepository {
  CallRequestRepository(this._api);

  final ApiClient _api;

  /// 이벤트 상담 신청. 로그인 필요(서버 jwt_auth).
  ///
  /// 서버 필수: campaign_id·name·phone·privacy_agree
  Future<void> apply({
    required int campaignId,
    required String name,
    required String phone,
    String? content,
  }) async {
    await _api.post('/call-requests', body: {
      'campaign_id': campaignId,
      'name': name,
      'phone': phone,
      if (content != null && content.isNotEmpty) 'content': content,
      'privacy_agree': true,
    },);
  }
}
