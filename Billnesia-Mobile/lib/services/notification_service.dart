import 'package:flutter_local_notifications/flutter_local_notifications.dart'
    as fln;

class NotificationService {
  static final fln.FlutterLocalNotificationsPlugin _notifications =
      fln.FlutterLocalNotificationsPlugin();

  static bool _initialized = false;

  /// Initialize the notification plugin. Call once at app startup.
  static Future<void> init() async {
    if (_initialized) return;

    const androidSettings = fln.AndroidInitializationSettings(
      '@mipmap/ic_launcher',
    );

    const initSettings = fln.InitializationSettings(android: androidSettings);

    // v20.x REQUIRES named argument 'settings'
    await _notifications.initialize(
      settings: initSettings,
      onDidReceiveNotificationResponse: (details) {
        // Handle notification tap
      },
    );
    _initialized = true;
  }

  /// Show a payment success notification on the Android notification bar.
  static Future<void> showPaymentNotification({
    required String customerName,
    required bool waSent,
    String? waError,
  }) async {
    final waInfo = waSent
        ? '✅ WhatsApp terkirim'
        : '⚠️ WA: ${waError ?? "Gagal kirim"}';

    const androidDetails = fln.AndroidNotificationDetails(
      'billing_channel',
      'Billing Notifications',
      channelDescription: 'Notifikasi pembayaran tagihan',
      importance: fln.Importance.high,
      priority: fln.Priority.high,
      icon: '@mipmap/ic_launcher',
    );

    const details = fln.NotificationDetails(android: androidDetails);

    // Using named arguments for 'show' to ensure compatibility with v20 API
    await _notifications.show(
      id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
      title: '💰 Pembayaran Berhasil',
      body: '$customerName telah lunas. $waInfo',
      notificationDetails: details,
    );
  }

  /// Show a rollback/cancel notification on the Android notification bar.
  static Future<void> showRollbackNotification({
    required String customerName,
    required bool waSent,
    String? waError,
  }) async {
    final waInfo = waSent
        ? '✅ WhatsApp terkirim'
        : '⚠️ WA: ${waError ?? "Gagal kirim"}';

    const androidDetails = fln.AndroidNotificationDetails(
      'billing_channel',
      'Billing Notifications',
      channelDescription: 'Notifikasi pembayaran tagihan',
      importance: fln.Importance.high,
      priority: fln.Priority.high,
      icon: '@mipmap/ic_launcher',
    );

    const details = fln.NotificationDetails(android: androidDetails);

    await _notifications.show(
      id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
      title: '🔄 Pembayaran Dibatalkan',
      body: '$customerName dikembalikan ke belum lunas. $waInfo',
      notificationDetails: details,
    );
  }
}
