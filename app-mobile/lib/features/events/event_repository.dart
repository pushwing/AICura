import '../../core/config/app_config.dart';
import '../../core/network/api_client.dart';
import 'models/event.dart';
import 'models/event_category.dart';

/// 목록 응답 + 페이지네이션 메타.
class EventPage {
  EventPage({
    required this.items,
    required this.page,
    required this.lastPage,
    required this.total,
  });

  final List<Event> items;
  final int page;
  final int lastPage;
  final int total;

  bool get hasMore => page < lastPage;
}

/// 이벤트(캠페인) 조회 API 모음.
class EventRepository {
  EventRepository(this._api);

  final ApiClient _api;

  /// 이벤트 목록 (필터·정렬·페이징)
  Future<EventPage> list({
    int? categoryId,
    String? region,
    String? keyword,
    String sort = 'latest',
    int page = 1,
    int perPage = AppConfig.defaultPerPage,
  }) async {
    final query = <String, dynamic>{
      'sort': sort,
      'page': page,
      'per_page': perPage,
      if (categoryId != null && categoryId > 0) 'filter[category]': categoryId,
      if (region != null && region.isNotEmpty) 'filter[region]': region,
      if (keyword != null && keyword.isNotEmpty) 'keyword': keyword,
    };
    final res = await _api.get('/campaigns', query: query);
    final meta = res.meta ?? const {};
    int metaInt(String key, int fallback) =>
        (meta[key] as num?)?.toInt() ?? fallback;

    return EventPage(
      items: res.dataAsList
          .map((e) => Event.fromJson((e as Map).cast<String, dynamic>()))
          .toList(),
      page: metaInt('page', page),
      lastPage: metaInt('last_page', page),
      total: metaInt('total', 0),
    );
  }

  /// 메인 노출 이벤트 (배너)
  Future<List<Event>> main() => _feed('/campaigns/main');

  /// 추천 이벤트
  Future<List<Event>> recommend() => _feed('/campaigns/recommend');

  Future<List<Event>> _feed(String path) async {
    final res = await _api.get(path);
    return res.dataAsList
        .map((e) => Event.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }

  /// 카테고리 목록
  Future<List<EventCategory>> categories() async {
    final res = await _api.get('/campaigns/categories');
    return res.dataAsList
        .map((e) => EventCategory.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }

  /// 이벤트 상세
  Future<Event> detail(int id) async {
    final res = await _api.get('/campaigns/$id');
    return Event.fromJson(res.dataAsMap);
  }

  /// 찜 토글 — 토글 후의 liked 상태 반환
  Future<bool> toggleLike(int id) async {
    final res = await _api.post('/campaigns/$id/like');
    return (res.dataAsMap['liked'] as bool?) ?? false;
  }
}
