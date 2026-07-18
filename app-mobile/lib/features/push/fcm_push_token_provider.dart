import 'package:firebase_messaging/firebase_messaging.dart';

import 'push_token_provider.dart';

/// FCM 기반 푸시 토큰 공급자 (Android 전용).
///
/// 사용 전 `Firebase.initializeApp()` 이 먼저 호출돼 있어야 한다.
class FcmPushTokenProvider extends PushTokenProvider {
  FcmPushTokenProvider({FirebaseMessaging? messaging})
      : _messaging = messaging ?? FirebaseMessaging.instance;

  final FirebaseMessaging _messaging;

  /// 알림 권한 요청 후 토큰을 발급받는다. 권한 거부/발급 실패 시 null.
  @override
  Future<String?> getToken() async {
    final settings = await _messaging.requestPermission();
    if (settings.authorizationStatus == AuthorizationStatus.denied) {
      return null;
    }
    return _messaging.getToken();
  }

  @override
  Stream<String> get onTokenRefresh => _messaging.onTokenRefresh;
}
