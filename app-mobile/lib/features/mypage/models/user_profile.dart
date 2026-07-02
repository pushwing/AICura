import '../../../core/util/json_parse.dart';

/// 내 정보 (GET /me).
class UserProfile {
  UserProfile({
    required this.id,
    required this.email,
    required this.username,
    this.picture,
    this.phone,
    this.healthPoint = 0,
    this.provider,
  });

  final int id;
  final String email;
  final String username;
  final String? picture;
  final String? phone;
  final int healthPoint;
  final String? provider;

  factory UserProfile.fromJson(Map<String, dynamic> j) {
    return UserProfile(
      id: parseInt(j['id']),
      email: (j['email'] ?? '').toString(),
      username: (j['username'] ?? '').toString(),
      picture: j['picture'] as String?,
      phone: j['phone'] as String?,
      healthPoint: parseInt(j['health_point']),
      provider: j['provider'] as String?,
    );
  }
}
