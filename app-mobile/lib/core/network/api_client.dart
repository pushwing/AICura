import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../storage/token_storage.dart';
import 'api_exception.dart';

/// 서버 통신 단일 진입점.
///
/// - 요청마다 access_token 을 Authorization 헤더로 자동 첨부
/// - 401 발생 시 refresh_token 으로 1회 자동 갱신 후 재시도
/// - 갱신 실패 시 토큰 폐기 + [onSessionExpired] 콜백 호출 (로그인 화면 유도)
/// - `{status, data, meta}` 표준 봉투를 [ApiResponse] 로 풀어 반환
class ApiClient {
  ApiClient({
    required TokenStorage storage,
    void Function()? onSessionExpired,
  })  : _storage = storage,
        _onSessionExpired = onSessionExpired {
    _dio = Dio(
      BaseOptions(
        baseUrl: AppConfig.apiBaseUrl,
        connectTimeout: AppConfig.connectTimeout,
        receiveTimeout: AppConfig.receiveTimeout,
        headers: {'Accept': 'application/json'},
      ),
    );
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: _onRequest,
        onError: _onError,
      ),
    );
  }

  final TokenStorage _storage;
  final void Function()? _onSessionExpired;
  late final Dio _dio;

  bool _refreshing = false;

  Future<void> _onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _storage.readAccess();
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  Future<void> _onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    final response = err.response;
    final isRetry = err.requestOptions.extra['__retried'] == true;

    // 401 이면서 아직 재시도 전이면 토큰 갱신을 시도한다.
    if (response?.statusCode == 401 && !isRetry && !_refreshing) {
      final refreshed = await _tryRefresh();
      if (refreshed) {
        try {
          final retried = await _retry(err.requestOptions);
          return handler.resolve(retried);
        } on DioException catch (e) {
          return handler.next(e);
        }
      }
      // 갱신 실패 → 세션 만료 처리
      await _storage.clear();
      _onSessionExpired?.call();
    }
    handler.next(err);
  }

  /// refresh_token 으로 새 토큰을 발급받아 저장한다. 성공 시 true.
  Future<bool> _tryRefresh() async {
    final refresh = await _storage.readRefresh();
    if (refresh == null || refresh.isEmpty) return false;

    _refreshing = true;
    try {
      // 인터셉터가 없는 별도 Dio 로 호출해 무한 루프를 막는다.
      final plain = Dio(BaseOptions(baseUrl: AppConfig.apiBaseUrl));
      final res = await plain.post<Map<String, dynamic>>(
        '/auth/refresh',
        data: {'refresh_token': refresh},
      );
      final data = (res.data?['data'] as Map?)?.cast<String, dynamic>();
      if (data == null) return false;

      await _storage.save(
        accessToken: data['access_token'] as String,
        refreshToken: (data['refresh_token'] as String?) ?? refresh,
      );
      return true;
    } on DioException {
      return false;
    } finally {
      _refreshing = false;
    }
  }

  Future<Response<dynamic>> _retry(RequestOptions options) {
    return _dio.request<dynamic>(
      options.path,
      data: options.data,
      queryParameters: options.queryParameters,
      options: Options(
        method: options.method,
        headers: options.headers,
        extra: {...options.extra, '__retried': true},
      ),
    );
  }

  // --- 공개 메서드 (봉투 언래핑) ---------------------------------------

  Future<ApiResponse> get(
    String path, {
    Map<String, dynamic>? query,
  }) =>
      _send(() => _dio.get<Map<String, dynamic>>(path, queryParameters: query));

  Future<ApiResponse> post(
    String path, {
    Object? body,
  }) =>
      _send(() => _dio.post<Map<String, dynamic>>(path, data: body));

  Future<ApiResponse> patch(String path, {Object? body}) =>
      _send(() => _dio.patch<Map<String, dynamic>>(path, data: body));

  Future<ApiResponse> delete(String path, {Object? body}) =>
      _send(() => _dio.delete<Map<String, dynamic>>(path, data: body));

  Future<ApiResponse> _send(
    Future<Response<Map<String, dynamic>>> Function() call,
  ) async {
    try {
      final res = await call();
      final body = res.data ?? const {};
      return ApiResponse(
        data: body['data'],
        meta: (body['meta'] as Map?)?.cast<String, dynamic>(),
      );
    } on DioException catch (e) {
      throw _toApiException(e);
    }
  }

  ApiException _toApiException(DioException e) {
    final res = e.response;
    final body = res?.data;
    if (body is Map && body['status'] == 'error') {
      return ApiException(
        code: (body['code'] as String?) ?? 'UNKNOWN',
        message: (body['message'] as String?) ?? '요청을 처리하지 못했습니다.',
        statusCode: res?.statusCode ?? 0,
      );
    }
    // 네트워크/타임아웃 등 비표준 오류
    return ApiException(
      code: 'NETWORK_ERROR',
      message: '네트워크 연결을 확인해주세요.',
      statusCode: res?.statusCode ?? 0,
    );
  }
}

/// 표준 봉투에서 풀어낸 응답.
class ApiResponse {
  ApiResponse({required this.data, this.meta});

  /// `data` 필드 — 단건은 Map, 목록은 List
  final dynamic data;

  /// `meta` 필드 — 페이지네이션 정보 (page, per_page, total, last_page)
  final Map<String, dynamic>? meta;

  Map<String, dynamic> get dataAsMap => (data as Map).cast<String, dynamic>();

  List<dynamic> get dataAsList => (data as List?) ?? const [];
}
