import '../../core/network/api_client.dart';
import 'models/auth_tokens.dart';

/// 소셜 로그인 제공자 — 서버 전송 시 [Enum.name] 값('naver'|'kakao')을 그대로 사용한다.
enum SocialProvider { naver, kakao }

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
  ///
  /// 보안(이슈 #187): 서버는 클라이언트가 보낸 uid 를 더 이상 신뢰하지 않는다.
  /// 소셜 SDK(카카오/네이버) 로그인으로 발급받은 [accessToken] 을 전송하면
  /// 서버가 제공자 API 로 검증해 계정을 식별한다. 검증 실패 시 401 `SOCIAL_AUTH_FAILED`.
  Future<AuthTokens> social({
    required SocialProvider provider,
    required String accessToken,
  }) async {
    final res = await _api.post(
      '/auth/social',
      body: {'provider': provider.name, 'access_token': accessToken},
    );
    return AuthTokens.fromJson(res.dataAsMap);
  }

  Future<void> logout() async {
    await _api.post('/auth/logout');
  }
}
