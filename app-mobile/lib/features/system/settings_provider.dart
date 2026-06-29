import 'package:flutter/foundation.dart';

import '../../core/network/api_exception.dart';
import 'models/app_settings.dart';
import 'system_repository.dart';

/// 앱 공개 설정을 부팅 시 로드해 전역에 제공한다.
class SettingsProvider extends ChangeNotifier {
  SettingsProvider(this._repo);

  final SystemRepository _repo;

  AppSettings _settings = AppSettings();
  AppSettings get settings => _settings;

  Future<void> load() async {
    try {
      _settings = await _repo.settings();
      notifyListeners();
    } on ApiException {
      // 설정 로드 실패 시 기본값 유지
    }
  }
}
