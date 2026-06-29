import 'dart:io';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/network/api_client.dart';
import 'core/storage/token_storage.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/auth_provider.dart';
import 'features/auth/auth_repository.dart';
import 'features/booking/booking_repository.dart';
import 'features/call_request/call_request_repository.dart';
import 'features/community/board_repository.dart';
import 'features/events/event_provider.dart';
import 'features/events/event_repository.dart';
import 'features/hospital/hospital_repository.dart';
import 'features/mypage/me_repository.dart';
import 'features/mypage/my_page_provider.dart';
import 'features/push/device_registrar.dart';
import 'features/push/device_repository.dart';
import 'features/push/push_token_provider.dart';
import 'features/shell/main_shell.dart';
import 'features/system/settings_provider.dart';
import 'features/system/system_repository.dart';

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
  late final MeRepository _meRepo;
  late final HospitalRepository _hospitalRepo;
  late final BoardRepository _boardRepo;
  late final BookingRepository _bookingRepo;
  late final SystemRepository _systemRepo;
  late final DeviceRegistrar _deviceRegistrar;
  late final EventProvider _events;
  late final MyPageProvider _myPage;
  late final SettingsProvider _settings;

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
    _meRepo = MeRepository(_api);
    _hospitalRepo = HospitalRepository(_api);
    _boardRepo = BoardRepository(_api);
    _bookingRepo = BookingRepository(_api);
    _systemRepo = SystemRepository(_api);
    _events = EventProvider(_eventRepo);
    _myPage = MyPageProvider(_meRepo);
    _settings = SettingsProvider(_systemRepo);
    _deviceRegistrar = DeviceRegistrar(
      tokenProvider: DebugPushTokenProvider(),
      repository: DeviceRepository(_api),
    );
    // 로그인 상태가 되면 디바이스 푸시 토큰을 등록한다.
    _auth.addListener(_onAuthChanged);
    _auth.bootstrap();
    // 부팅 시 공개 설정 로드 + 앱 실행 로그 전송(fire-and-forget)
    _settings.load();
    _systemRepo.log({
      'event': 'app_open',
      'platform': Platform.operatingSystem,
      'occurred_at': DateTime.now().toIso8601String(),
    });
  }

  /// 인증 상태 변화 → 로그인 시 디바이스 등록, 로그아웃 시 상태 초기화.
  void _onAuthChanged() {
    if (_auth.isAuthenticated) {
      _deviceRegistrar.registerIfAvailable();
    } else {
      _deviceRegistrar.reset();
    }
  }

  @override
  void dispose() {
    _auth.removeListener(_onAuthChanged);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider.value(value: _auth),
        ChangeNotifierProvider.value(value: _events),
        ChangeNotifierProvider.value(value: _myPage),
        ChangeNotifierProvider.value(value: _settings),
        Provider<EventRepository>.value(value: _eventRepo),
        Provider<CallRequestRepository>.value(value: _callRepo),
        Provider<MeRepository>.value(value: _meRepo),
        Provider<HospitalRepository>.value(value: _hospitalRepo),
        Provider<BoardRepository>.value(value: _boardRepo),
        Provider<BookingRepository>.value(value: _bookingRepo),
      ],
      child: MaterialApp(
        title: 'AICura',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light(),
        // 첫 화면은 항상 메인 셸(홈 탭). 로그인은 신청·찜·마이페이지에서만 요구한다.
        home: const MainShell(),
      ),
    );
  }
}
