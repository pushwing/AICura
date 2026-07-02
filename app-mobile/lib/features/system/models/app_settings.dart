/// 앱 공개 설정 (GET /settings).
class AppSettings {
  AppSettings({
    this.siteName = '',
    this.minVersionIos = '',
    this.minVersionAndroid = '',
    this.termsUrl = '',
    this.privacyUrl = '',
  });

  final String siteName;
  final String minVersionIos;
  final String minVersionAndroid;
  final String termsUrl;
  final String privacyUrl;

  factory AppSettings.fromJson(Map<String, dynamic> j) {
    return AppSettings(
      siteName: (j['site_name'] ?? '').toString(),
      minVersionIos: (j['app_min_version_ios'] ?? '').toString(),
      minVersionAndroid: (j['app_min_version_android'] ?? '').toString(),
      termsUrl: (j['terms_url'] ?? '').toString(),
      privacyUrl: (j['privacy_url'] ?? '').toString(),
    );
  }
}
