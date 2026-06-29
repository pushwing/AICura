import '../../core/network/api_client.dart';
import 'models/booking.dart';

/// 예약 API (로그인 필요).
class BookingRepository {
  BookingRepository(this._api);

  final ApiClient _api;

  /// 병원 예약 생성. hospital_id 필수, name/phone 선택.
  Future<Booking> create({
    required int hospitalId,
    String? name,
    String? phone,
  }) async {
    final res = await _api.post('/bookings', body: {
      'hospital_id': hospitalId,
      if (name != null && name.isNotEmpty) 'name': name,
      if (phone != null && phone.isNotEmpty) 'phone': phone,
    },);
    return Booking.fromJson(res.dataAsMap);
  }
}
