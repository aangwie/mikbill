/// Centralized API configuration constants for the Billnesia Mobile app.
class ApiConfig {
  /// Default API base URL (Android emulator points to host machine's localhost).
  static const String defaultBaseUrl = 'http://localhost:8000';

  /// API route prefix appended after the base URL.
  static const String apiPrefix = '/api/mobile';

  /// HTTP request timeout duration.
  static const Duration timeout = Duration(seconds: 30);

  /// App name displayed across the app.
  static const String appName = 'Billnesia Mobile';

  /// Current app version.
  static const String appVersion = '1.0.0';
}
