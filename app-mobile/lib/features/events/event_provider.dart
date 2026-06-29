import 'package:flutter/foundation.dart';

import '../../core/network/api_exception.dart';
import 'event_repository.dart';
import 'models/event.dart';
import 'models/event_category.dart';

/// 정렬 옵션.
enum EventSort { latest, priceAsc, priceDesc, popular }

extension EventSortApi on EventSort {
  String get value => switch (this) {
        EventSort.latest => 'latest',
        EventSort.priceAsc => 'price_asc',
        EventSort.priceDesc => 'price_desc',
        EventSort.popular => 'popular',
      };

  String get label => switch (this) {
        EventSort.latest => '최신순',
        EventSort.priceAsc => '낮은가격순',
        EventSort.priceDesc => '높은가격순',
        EventSort.popular => '인기순',
      };
}

/// 홈(이벤트 리스트) 화면 상태.
///
/// 배너(main)·추천(recommend)·카테고리·본문 목록을 함께 관리하고,
/// 무한 스크롤·필터·정렬·찜 토글을 처리한다.
class EventProvider extends ChangeNotifier {
  EventProvider(this._repo);

  final EventRepository _repo;

  // --- 상단 피드 -------------------------------------------------------
  List<Event> banners = [];
  List<Event> recommended = [];
  List<EventCategory> categories = [];

  // --- 본문 목록 -------------------------------------------------------
  final List<Event> items = [];
  int _page = 0;
  int _lastPage = 1;
  bool _loadingMore = false;

  // --- 필터 상태 -------------------------------------------------------
  int _categoryId = 0;
  int get selectedCategoryId => _categoryId;
  EventSort _sort = EventSort.latest;
  EventSort get sort => _sort;
  String _keyword = '';

  // --- 화면 상태 -------------------------------------------------------
  bool _initialLoading = false;
  bool get initialLoading => _initialLoading;
  String? _error;
  String? get error => _error;
  bool get hasMore => _page < _lastPage;

  /// 첫 진입 — 배너·추천·카테고리·1페이지를 병렬로 로드.
  Future<void> bootstrap() async {
    _initialLoading = true;
    _error = null;
    notifyListeners();
    try {
      // 상단 피드는 병렬, 본문 목록은 그 뒤에 로드한다.
      final feeds = await (
        _repo.main(),
        _repo.recommend(),
        _repo.categories(),
      ).wait;
      banners = feeds.$1;
      recommended = feeds.$2;
      categories = feeds.$3;
      await _loadFirstPage();
    } on ApiException catch (e) {
      _error = e.message;
    } catch (e) {
      // 파싱 오류 등 비정상 응답도 빈 화면 대신 에러로 노출
      _error = '데이터를 불러오지 못했습니다.';
    } finally {
      _initialLoading = false;
      notifyListeners();
    }
  }

  /// 당겨서 새로고침
  Future<void> refresh() async {
    await bootstrap();
  }

  /// 카테고리 변경 → 목록만 다시 로드
  Future<void> selectCategory(int categoryId) async {
    _categoryId = categoryId;
    await _reloadList();
  }

  /// 정렬 변경 → 목록만 다시 로드
  Future<void> changeSort(EventSort sort) async {
    _sort = sort;
    await _reloadList();
  }

  /// 키워드 검색 → 목록만 다시 로드
  Future<void> search(String keyword) async {
    _keyword = keyword.trim();
    await _reloadList();
  }

  Future<void> _reloadList() async {
    items.clear();
    _page = 0;
    _lastPage = 1;
    _error = null;
    notifyListeners();
    try {
      await _loadFirstPage();
    } on ApiException catch (e) {
      _error = e.message;
    } catch (e) {
      _error = '데이터를 불러오지 못했습니다.';
    }
    notifyListeners();
  }

  Future<void> _loadFirstPage() async {
    final pageData = await _repo.list(
      categoryId: _categoryId,
      keyword: _keyword,
      sort: _sort.value,
      page: 1,
    );
    items
      ..clear()
      ..addAll(pageData.items);
    _page = pageData.page;
    _lastPage = pageData.lastPage;
  }

  /// 무한 스크롤 — 다음 페이지 적재
  Future<void> loadMore() async {
    if (_loadingMore || !hasMore) return;
    _loadingMore = true;
    notifyListeners();
    try {
      final pageData = await _repo.list(
        categoryId: _categoryId,
        keyword: _keyword,
        sort: _sort.value,
        page: _page + 1,
      );
      items.addAll(pageData.items);
      _page = pageData.page;
      _lastPage = pageData.lastPage;
    } catch (_) {
      // 다음 페이지 실패는 조용히 무시 (다시 스크롤하면 재시도)
    } finally {
      _loadingMore = false;
      notifyListeners();
    }
  }

  /// 찜 토글 — 낙관적 업데이트 후 서버 반영, 실패 시 롤백
  Future<void> toggleLike(Event event) async {
    final idx = items.indexWhere((e) => e.id == event.id);
    if (idx == -1) return;
    final before = items[idx];
    items[idx] = before.copyWith(isLiked: !before.isLiked);
    notifyListeners();
    try {
      final liked = await _repo.toggleLike(event.id);
      items[idx] = items[idx].copyWith(isLiked: liked);
    } on ApiException {
      items[idx] = before; // 롤백
    }
    notifyListeners();
  }
}
