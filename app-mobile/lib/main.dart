import 'dart:io';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';

import 'app.dart';
import 'features/push/push_notification_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // FCM 은 Android 에만 적용(iOS 는 APNs 인증서 준비 후 별도 이슈에서 연동).
  if (Platform.isAndroid) {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
  }
  runApp(const AicuraApp());
}
