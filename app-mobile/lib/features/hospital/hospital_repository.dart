import '../../core/config/app_config.dart';
import '../../core/network/api_client.dart';
import '../events/models/event.dart';
import 'models/hospital.dart';
import 'models/hospital_review.dart';

/// 병원 목록 + 페이지네이션.
class HospitalPage {
  HospitalPage(
      {required this.items, required this.page, required this.lastPage});

  final List<Hospital> items;
  final int page;
  final int lastPage;

  bool get hasMore => page < lastPage;
}

/// 병원 조회 API. 조회는 비로그인 허용, 찜은 로그인 필요.
class HospitalRepository {
  HospitalRepository(this._api);

  final ApiClient _api;

  Future<HospitalPage> list({
    String? keyword,
    int page = 1,
    int perPage = AppConfig.defaultPerPage,
  }) async {
    final res = await _api.get(
      '/hospitals',
      query: {
        'page': page,
        'per_page': perPage,
        if (keyword != null && keyword.isNotEmpty) 'keyword': keyword,
      },
    );
    final meta = res.meta ?? const {};
    return HospitalPage(
      items: res.dataAsList
          .map((e) => Hospital.fromJson((e as Map).cast<String, dynamic>()))
          .toList(),
      page: (meta['page'] as num?)?.toInt() ?? page,
      lastPage: (meta['last_page'] as num?)?.toInt() ?? page,
    );
  }

  Future<Hospital> detail(int id) async {
    final res = await _api.get('/hospitals/$id');
    return Hospital.fromJson(res.dataAsMap);
  }

  /// 병원 진행 이벤트 (Event 모델 재사용)
  Future<List<Event>> campaigns(int id) async {
    final res = await _api.get('/hospitals/$id/campaigns');
    return res.dataAsList
        .map((e) => Event.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }

  Future<List<HospitalReview>> reviews(int id) async {
    final res = await _api.get('/hospitals/$id/reviews');
    return res.dataAsList
        .map((e) => HospitalReview.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }

  /// 찜 토글 — 로그인 필요. 토글 후 liked 반환.
  Future<bool> toggleLike(int id) async {
    final res = await _api.post('/hospitals/$id/like');
    return (res.dataAsMap['liked'] as bool?) ?? false;
  }
}
