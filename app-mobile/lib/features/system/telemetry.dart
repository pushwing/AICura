import 'system_repository.dart';

/// 앱 액션 텔레메트리 (이슈 #120)
///
/// 이벤트 키는 백엔드 `App\Enums\AppLogEvent` 와 1:1로 맞춘다.
/// 모든 전송은 fire-and-forget — 실패해도 화면 흐름에 영향을 주지 않는다.
class Telemetry {
  Telemetry(this._repo);

  final SystemRepository _repo;

  void _send(String event, [Map<String, dynamic>? extra]) {
    _repo.log({
      'event': event,
      'occurred_at': DateTime.now().toIso8601String(),
      ...?extra,
    });
  }

  /// 이벤트(캠페인) 목록 조회
  void eventListView() => _send('event_list_view');

  /// 이벤트 상세 조회
  void eventDetailView(int campaignId) =>
      _send('event_detail_view', {'campaign_id': campaignId});

  /// 신청폼 진입
  void applyFormView(int campaignId) =>
      _send('apply_form_view', {'campaign_id': campaignId});

  /// 신청 제출(클릭)
  void applySubmit(int campaignId) =>
      _send('apply_submit', {'campaign_id': campaignId});

  /// 이벤트 검색
  void eventSearch(String keyword) =>
      _send('event_search', {'keyword': keyword});

  /// 이벤트 찜
  void eventLike(int campaignId) =>
      _send('event_like', {'campaign_id': campaignId});

  /// 병원 상세 조회
  void hospitalDetailView(int hospitalId) =>
      _send('hospital_detail_view', {'hospital_id': hospitalId});
}
