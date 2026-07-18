import 'dart:async';

import 'package:aicura_app/core/network/api_client.dart';
import 'package:aicura_app/core/network/api_exception.dart';
import 'package:aicura_app/core/storage/token_storage.dart';
import 'package:aicura_app/features/push/device_registrar.dart';
import 'package:aicura_app/features/push/device_repository.dart';
import 'package:aicura_app/features/push/push_token_provider.dart';
import 'package:flutter_test/flutter_test.dart';

class _FakePushTokenProvider extends PushTokenProvider {
  _FakePushTokenProvider({String? initialToken}) : _token = initialToken;

  String? _token;
  final _refreshController = StreamController<String>.broadcast();

  @override
  Future<String?> getToken() async => _token;

  @override
  int get platform => 2;

  @override
  Stream<String> get onTokenRefresh => _refreshController.stream;

  void emitRefresh(String token) {
    _token = token;
    _refreshController.add(token);
  }
}

class _FakeDeviceRepository extends DeviceRepository {
  _FakeDeviceRepository() : super(ApiClient(storage: TokenStorage()));

  final calls = <String>[];
  Object? failWith;

  @override
  Future<void> register(
      {required String pushToken, required int platform}) async {
    if (failWith != null) throw failWith!;
    calls.add(pushToken);
  }
}

void main() {
  group('DeviceRegistrar', () {
    test('토큰이 있으면 등록한다', () async {
      final repo = _FakeDeviceRepository();
      final registrar = DeviceRegistrar(
        tokenProvider: _FakePushTokenProvider(initialToken: 'token-a'),
        repository: repo,
      );

      await registrar.registerIfAvailable();

      expect(repo.calls, ['token-a']);
    });

    test('토큰이 없으면 건너뛴다', () async {
      final repo = _FakeDeviceRepository();
      final registrar = DeviceRegistrar(
        tokenProvider: _FakePushTokenProvider(),
        repository: repo,
      );

      await registrar.registerIfAvailable();

      expect(repo.calls, isEmpty);
    });

    test('토큰이 재발급되면 등록 여부와 무관하게 다시 등록한다', () async {
      final repo = _FakeDeviceRepository();
      final provider = _FakePushTokenProvider(initialToken: 'token-a');
      final registrar = DeviceRegistrar(
        tokenProvider: provider,
        repository: repo,
      );
      await registrar.registerIfAvailable();

      provider.emitRefresh('token-b');
      await Future<void>.delayed(Duration.zero);

      expect(repo.calls, ['token-a', 'token-b']);
    });

    test('재발급 등록 중 API 오류가 나도 예외를 전파하지 않는다', () async {
      final repo = _FakeDeviceRepository()
        ..failWith =
            ApiException(code: 'NETWORK_ERROR', message: '실패', statusCode: 0);
      final provider = _FakePushTokenProvider(initialToken: 'token-a');
      DeviceRegistrar(tokenProvider: provider, repository: repo);

      provider.emitRefresh('token-b');
      await Future<void>.delayed(Duration.zero);

      expect(repo.calls, isEmpty);
    });
  });
}
