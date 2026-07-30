import 'dart:async';

import '../../core/network/api_exception.dart';
import 'device_repository.dart';
import 'push_token_provider.dart';

/// 로그인 사용자의 디바이스 푸시 토큰을 서버에 등록한다.
///
/// 토큰 공급자가 토큰을 주지 못하면(미설정) 조용히 건너뛴다.
/// 토큰이 재발급되면(FCM onTokenRefresh 등) 등록 여부와 무관하게 즉시 재등록한다.
class DeviceRegistrar {
  DeviceRegistrar({
    required PushTokenProvider tokenProvider,
    required DeviceRepository repository,
  })  : _tokenProvider = tokenProvider,
        _repo = repository {
    _refreshSub = _tokenProvider.onTokenRefresh.listen(_registerToken);
  }

  final PushTokenProvider _tokenProvider;
  final DeviceRepository _repo;
  late final StreamSubscription<String> _refreshSub;

  bool _registered = false;

  /// 토큰이 있으면 등록(중복 호출 방지). 실패해도 앱 흐름에 영향 없음.
  Future<void> registerIfAvailable() async {
    if (_registered) return;
    final token = await _tokenProvider.getToken();
    if (token == null || token.isEmpty) return;
    final ok = await _registerToken(token);
    if (ok) _registered = true;
  }

  Future<bool> _registerToken(String token) async {
    try {
      await _repo.register(pushToken: token, platform: _tokenProvider.platform);
      return true;
    } on ApiException {
      // 등록 실패는 무시 (다음 로그인/실행 때 재시도)
      return false;
    }
  }

  /// 로그아웃 시 재등록 가능하도록 상태 초기화.
  void reset() => _registered = false;

  /// 화면/앱 소멸 시 구독 해제.
  void dispose() => _refreshSub.cancel();
}
