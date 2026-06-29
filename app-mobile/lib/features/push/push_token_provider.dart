import 'dart:io';

import 'package:flutter/foundation.dart';

/// 푸시 토큰 공급자 추상화.
///
/// 실제 FCM 연동(firebase_messaging) 시 [FcmPushTokenProvider] 등으로 교체한다.
/// 서버 platform 코드: 2 = Android, 3 = iOS.
abstract class PushTokenProvider {
  /// 디바이스 푸시 토큰. 사용 불가(미설정)면 null.
  Future<String?> getToken();

  /// 서버 전송용 platform 코드 (2=Android, 3=iOS).
  int get platform => Platform.isIOS ? 3 : 2;
}

/// FCM 미연동 상태의 임시 공급자.
///
/// - 릴리스 빌드: null 반환 → 디바이스 등록 건너뜀(안전)
/// - 디버그 빌드: 합성 토큰 반환 → 등록 배선 검증용
///
/// TODO(push): firebase_messaging 연동 후 FcmPushTokenProvider 로 교체.
class DebugPushTokenProvider extends PushTokenProvider {
  @override
  Future<String?> getToken() async {
    if (!kDebugMode) return null;
    return 'debug-fcm-token-${platform == 3 ? 'ios' : 'android'}';
  }
}
