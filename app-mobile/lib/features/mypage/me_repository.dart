import '../../core/network/api_client.dart';
import '../booking/models/booking.dart';
import 'models/call_request_item.dart';
import 'models/like_item.dart';
import 'models/user_profile.dart';

/// 마이페이지 관련 API (로그인 필요).
class MeRepository {
  MeRepository(this._api);

  final ApiClient _api;

  /// 내 정보
  Future<UserProfile> profile() async {
    final res = await _api.get('/me');
    return UserProfile.fromJson(res.dataAsMap);
  }

  /// 내 찜 목록
  Future<List<LikeItem>> likes({int perPage = 20}) async {
    final res = await _api.get('/me/likes', query: {'per_page': perPage});
    return res.dataAsList
        .map((e) => LikeItem.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }

  /// 내 상담 신청 내역
  Future<List<CallRequestItem>> callRequests({int perPage = 20}) async {
    final res =
        await _api.get('/me/call-requests', query: {'per_page': perPage});
    return res.dataAsList
        .map((e) => CallRequestItem.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }

  /// 내 예약 내역
  Future<List<Booking>> bookings({int perPage = 20}) async {
    final res = await _api.get('/me/bookings', query: {'per_page': perPage});
    return res.dataAsList
        .map((e) => Booking.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }
}
