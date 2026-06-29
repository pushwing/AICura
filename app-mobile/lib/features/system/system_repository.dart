import '../../core/network/api_client.dart';
import '../../core/network/api_exception.dart';
import 'models/app_settings.dart';

/// 공통·운영 API (인증 불필요): 설정·코드·로그.
class SystemRepository {
  SystemRepository(this._api);

  final ApiClient _api;

  /// 앱 공개 설정
  Future<AppSettings> settings() async {
    final res = await _api.get('/settings');
    return AppSettings.fromJson(res.dataAsMap);
  }

  /// 코드성 데이터 (type 별)
  Future<List<Map<String, dynamic>>> codes(String type) async {
    final res = await _api.get('/codes', query: {'type': type});
    return res.dataAsList
        .map((e) => (e as Map).cast<String, dynamic>())
        .toList();
  }

  /// 앱 로그 전송 — fire-and-forget (실패해도 앱 흐름에 영향 없음).
  Future<void> log(Map<String, dynamic> event) async {
    try {
      await _api.post('/logs', body: event);
    } on ApiException {
      // 로그 전송 실패는 무시
    }
  }
}
