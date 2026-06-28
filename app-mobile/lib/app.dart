import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/network/api_client.dart';
import 'core/storage/token_storage.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/auth_provider.dart';
import 'features/auth/auth_repository.dart';
import 'features/auth/login_screen.dart';
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
    _events = EventProvider(EventRepository(_api));
    _auth.bootstrap();
  }

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider.value(value: _auth),
        ChangeNotifierProvider.value(value: _events),
      ],
      child: MaterialApp(
        title: 'AICura',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light(),
        home: const _AuthGate(),
      ),
    );
  }
}

/// 인증 상태에 따라 홈/로그인을 분기한다.
class _AuthGate extends StatelessWidget {
  const _AuthGate();

  @override
  Widget build(BuildContext context) {
    final status = context.watch<AuthProvider>().status;
    return switch (status) {
      AuthStatus.unknown =>
        const Scaffold(body: Center(child: CircularProgressIndicator())),
      AuthStatus.authenticated => const HomeScreen(),
      AuthStatus.unauthenticated => const LoginScreen(),
    };
  }
}
