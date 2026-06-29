import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/network/api_client.dart';
import 'core/storage/token_storage.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/auth_provider.dart';
import 'features/auth/auth_repository.dart';
import 'features/call_request/call_request_repository.dart';
import 'features/events/event_provider.dart';
import 'features/events/event_repository.dart';
import 'features/events/home_screen.dart';

/// 앱 루트 — 의존성 조립(DI) 및 라우팅(인증 게이트).
class AicuraApp extends StatefulWidget {
  const AicuraApp({super.key});

  @override
  State<AicuraApp> createState() => _AicuraAppState();
}

class _AicuraAppState extends State<AicuraApp> {
  late final TokenStorage _storage;
  late final ApiClient _api;
  late final AuthProvider _auth;
  late final EventRepository _eventRepo;
  late final CallRequestRepository _callRepo;
  late final EventProvider _events;

  @override
  void initState() {
    super.initState();
    _storage = TokenStorage();
    // ApiClient 는 세션 만료 시 AuthProvider 에 알린다(순환 의존 회피용 콜백).
    _api = ApiClient(
      storage: _storage,
      onSessionExpired: () => _auth.onSessionExpired(),
    );
    _auth = AuthProvider(
      repository: AuthRepository(_api),
      storage: _storage,
    );
    _eventRepo = EventRepository(_api);
    _callRepo = CallRequestRepository(_api);
    _events = EventProvider(_eventRepo);
    _auth.bootstrap();
  }

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider.value(value: _auth),
        ChangeNotifierProvider.value(value: _events),
        Provider<EventRepository>.value(value: _eventRepo),
        Provider<CallRequestRepository>.value(value: _callRepo),
      ],
      child: MaterialApp(
        title: 'AICura',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light(),
        // 첫 화면은 항상 이벤트 리스트(홈). 로그인은 신청·찜 시점에만 요구한다.
        home: const HomeScreen(),
      ),
    );
  }
}
