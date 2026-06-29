import '../../core/network/api_client.dart';

/// 디바이스(푸시 토큰) 등록 API (로그인 필요).
class DeviceRepository {
  DeviceRepository(this._api);

  final ApiClient _api;

  /// 푸시 토큰 등록. platform: 2=Android, 3=iOS.
  Future<void> register({
    required String pushToken,
    required int platform,
  }) async {
    await _api.post('/me/device', body: {
      'push_token': pushToken,
      'platform': platform,
    },);
  }
}
