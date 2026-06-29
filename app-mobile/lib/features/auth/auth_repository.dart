import '../../core/network/api_client.dart';
import 'models/auth_tokens.dart';

/// 인증 관련 API 호출 모음.
class AuthRepository {
  AuthRepository(this._api);

  final ApiClient _api;

  /// 이메일 로그인
  Future<AuthTokens> login({
    required String email,
    required String password,
  }) async {
    final res = await _api.post(
      '/auth/login',
      body: {'email': email, 'password': password},
    );
    return AuthTokens.fromJson(res.dataAsMap);
  }

  /// 이메일 회원가입 (성공 시 토큰 발급 = 즉시 로그인)
  Future<AuthTokens> register({
    required String email,
    required String password,
    String? username,
    String? phone,
    int? age,
    String? sex,
  }) async {
    final body = <String, dynamic>{
      'email': email,
      'password': password,
      if (username != null && username.isNotEmpty) 'username': username,
      if (phone != null && phone.isNotEmpty) 'phone': phone,
      if (age != null) 'age': age,
      if (sex != null && sex.isNotEmpty) 'sex': sex,
    };
    final res = await _api.post('/auth/register', body: body);
    return AuthTokens.fromJson(res.dataAsMap);
  }

  /// 이메일 중복 확인 — 사용 가능하면 true
  Future<bool> isEmailAvailable(String email) async {
    final res = await _api.post('/auth/check-email', body: {'email': email});
    final data = res.dataAsMap;
    // 서버 응답 키 호환 처리 (available / is_available)
    return (data['available'] ?? data['is_available'] ?? false) == true;
  }

  /// 소셜 로그인 (미가입 시 자동 가입)
  Future<AuthTokens> social({
    required String provider,
    required String uid,
  }) async {
    final res = await _api.post(
      '/auth/social',
      body: {'provider': provider, 'uid': uid},
    );
    return AuthTokens.fromJson(res.dataAsMap);
  }

  Future<void> logout() async {
    await _api.post('/auth/logout');
  }
}
