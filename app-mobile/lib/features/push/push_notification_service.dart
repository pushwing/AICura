import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

/// FCM 알림 표시 (Android).
///
/// - 포그라운드: FCM 이 시스템 트레이에 자동 표시하지 않으므로 직접 배너를 띄운다.
/// - 백그라운드/종료: 시스템이 자동 표시한다(AndroidManifest 의 기본 채널 메타데이터 사용).
///   단, 종료 상태에서 메시지를 받는 별도 isolate 에서도 Firebase 가 초기화돼 있어야 하므로
///   [firebaseMessagingBackgroundHandler] 를 `main()`에서 등록해야 한다.
class PushNotificationService {
  PushNotificationService({FlutterLocalNotificationsPlugin? plugin})
      : _plugin = plugin ?? FlutterLocalNotificationsPlugin();

  static const _channel = AndroidNotificationChannel(
    'push_default_channel', // AndroidManifest.xml 의 default_notification_channel_id 와 일치
    '일반 알림',
    description: '이벤트·예약 등 일반 푸시 알림',
    importance: Importance.high,
  );

  final FlutterLocalNotificationsPlugin _plugin;

  /// 알림 채널 생성 + 포그라운드 메시지 구독. 앱 시작 시 1회 호출.
  Future<void> init() async {
    await _plugin
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_channel);

    await _plugin.initialize(
      settings: const InitializationSettings(
        android: AndroidInitializationSettings('@mipmap/ic_launcher'),
      ),
    );

    FirebaseMessaging.onMessage.listen(_showForegroundNotification);
  }

  Future<void> _showForegroundNotification(RemoteMessage message) async {
    final notification = message.notification;
    if (notification == null) return;

    await _plugin.show(
      id: message.hashCode,
      title: notification.title,
      body: notification.body,
      notificationDetails: NotificationDetails(
        android: AndroidNotificationDetails(
          _channel.id,
          _channel.name,
          channelDescription: _channel.description,
          importance: Importance.high,
          priority: Priority.high,
        ),
      ),
    );
  }
}

/// 앱이 백그라운드/종료 상태일 때 FCM 메시지를 받는 진입점.
///
/// 별도 isolate 에서 실행되므로 Firebase 를 다시 초기화해야 한다.
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}
