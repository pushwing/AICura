import 'package:flutter/foundation.dart';

import '../../core/network/api_exception.dart';
import '../booking/models/booking.dart';
import 'me_repository.dart';
import 'models/call_request_item.dart';
import 'models/like_item.dart';
import 'models/user_profile.dart';

/// 마이페이지 화면 상태 — 프로필·찜·상담신청을 함께 로드한다.
class MyPageProvider extends ChangeNotifier {
  MyPageProvider(this._repo);

  final MeRepository _repo;

  UserProfile? profile;
  List<LikeItem> likes = [];
  List<CallRequestItem> callRequests = [];
  List<Booking> bookings = [];

  bool _loading = false;
  bool get loading => _loading;
  String? _error;
  String? get error => _error;
  bool _loaded = false;
  bool get loaded => _loaded;

  /// 프로필·찜·상담신청을 병렬 로드.
  Future<void> load() async {
    _loading = true;
    _error = null;
    notifyListeners();
    try {
      final results = await (
        _repo.profile(),
        _repo.likes(),
        _repo.callRequests(),
        _repo.bookings(),
      ).wait;
      profile = results.$1;
      likes = results.$2;
      callRequests = results.$3;
      bookings = results.$4;
      _loaded = true;
    } on ApiException catch (e) {
      _error = e.message;
    } catch (e) {
      _error = '정보를 불러오지 못했습니다.';
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  /// 로그아웃 등으로 상태를 비운다.
  void clear() {
    profile = null;
    likes = [];
    callRequests = [];
    _loaded = false;
    _error = null;
    notifyListeners();
  }
}
