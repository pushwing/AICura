import 'package:flutter/foundation.dart';

import '../../core/network/api_exception.dart';
import '../../core/storage/token_storage.dart';
import 'auth_repository.dart';

/// 앱 인증 상태.
enum AuthStatus { unknown, authenticated, unauthenticated }

/// 로그인 여부를 앱 전역에 노출하고, 로그인/회원가입/로그아웃을 수행한다.
class AuthProvider extends ChangeNotifier {
  AuthProvider({
    required AuthRepository repository,
    required TokenStorage storage,
  })  : _repo = repository,
        _storage = storage;

  final AuthRepository _repo;
  final TokenStorage _storage;

  AuthStatus _status = AuthStatus.unknown;
  AuthStatus get status => _status;

  /// 로그인 완료 상태 여부 — 신청·찜 등 보호 동작 게이팅에 사용
  bool get isAuthenticated => _status == AuthStatus.authenticated;

  bool _busy = false;
  bool get busy => _busy;

  /// 앱 부팅 시 저장된 토큰 유무로 초기 상태를 결정한다.
  Future<void> bootstrap() async {
    final token = await _storage.readAccess();
    _status = (token != null && token.isNotEmpty)
        ? AuthStatus.authenticated
        : AuthStatus.unauthenticated;
    notifyListeners();
  }

  /// ApiClient 의 세션 만료 콜백에서 호출 — 강제 로그아웃 상태로 전환.
  void onSessionExpired() {
    _status = AuthStatus.unauthenticated;
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
    await _run(() async {
      final tokens = await _repo.login(email: email, password: password);
      await _storage.save(
        accessToken: tokens.accessToken,
        refreshToken: tokens.refreshToken,
      );
      _status = AuthStatus.authenticated;
    });
  }

  Future<void> register({
    required String email,
    required String password,
    String? username,
    String? phone,
    int? age,
    String? sex,
  }) async {
    await _run(() async {
      final tokens = await _repo.register(
        email: email,
        password: password,
        username: username,
        phone: phone,
        age: age,
        sex: sex,
      );
      await _storage.save(
        accessToken: tokens.accessToken,
        refreshToken: tokens.refreshToken,
      );
      _status = AuthStatus.authenticated;
    });
  }

  /// 소셜 로그인 — 카카오/네이버 SDK 에서 받은 access_token 을 서버에 전달한다.
  ///
  /// [provider] 는 'naver' | 'kakao'. 서버 검증 실패 시 [ApiException]
  /// (code: `SOCIAL_AUTH_FAILED`) 이 호출부로 전달된다.
  Future<void> socialLogin({
    required String provider,
    required String accessToken,
  }) async {
    await _run(() async {
      final tokens = await _repo.social(
        provider: provider,
        accessToken: accessToken,
      );
      await _storage.save(
        accessToken: tokens.accessToken,
        refreshToken: tokens.refreshToken,
      );
      _status = AuthStatus.authenticated;
    });
  }

  Future<bool> checkEmail(String email) => _repo.isEmailAvailable(email);

  Future<void> logout() async {
    try {
      await _repo.logout();
    } on ApiException {
      // 서버 로그아웃 실패해도 로컬 토큰은 폐기한다.
    }
    await _storage.clear();
    _status = AuthStatus.unauthenticated;
    notifyListeners();
  }

  /// 공통 실행 래퍼 — busy 토글 + 알림. 예외는 호출부로 전달한다.
  Future<void> _run(Future<void> Function() action) async {
    _busy = true;
    notifyListeners();
    try {
      await action();
    } finally {
      _busy = false;
      notifyListeners();
    }
  }
}
