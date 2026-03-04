import 'package:flutter_local_notifications/flutter_local_notifications.dart';

class NotificationService {
  static final FlutterLocalNotificationsPlugin _notifications =
      FlutterLocalNotificationsPlugin();

  static bool _initialized = false;

  /// Initialize the notification plugin. Call once at app startup.
  static Future<void> initialize() async {
    if (_initialized) return;

    const androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    const initSettings = InitializationSettings(android: androidSettings);

    await _notifications.initialize(initSettings);
    _initialized = true;
  }

  /// Show a payment success notification on the Android notification bar.
  static Future<void> showPaymentNotification({
    required String customerName,
    required bool waSent,
    String? waError,
  }) async {
    final waInfo = waSent ? '✅ WhatsApp terkirim' : '⚠️ WA: ${waError ?? "Gagal kirim"}';
    
    const androidDetails = AndroidNotificationDetails(
      'billing_channel',
      'Billing Notifications',
      channelDescription: 'Notifikasi pembayaran tagihan',
      importance: Importance.high,
      priority: Priority.high,
      icon: '@mipmap/ic_launcher',
    );

    const details = NotificationDetails(android: androidDetails);

    await _notifications.show(
      DateTime.now().millisecondsSinceEpoch ~/ 1000,
      '💰 Pembayaran Berhasil',
      '$customerName telah lunas. $waInfo',
      details,
    );
  }

  /// Show a rollback/cancel notification on the Android notification bar.
  static Future<void> showRollbackNotification({
    required String customerName,
    required bool waSent,
    String? waError,
  }) async {
    final waInfo = waSent ? '✅ WhatsApp terkirim' : '⚠️ WA: ${waError ?? "Gagal kirim"}';

    const androidDetails = AndroidNotificationDetails(
      'billing_channel',
      'Billing Notifications',
      channelDescription: 'Notifikasi pembayaran tagihan',
      importance: Importance.high,
      priority: Priority.high,
      icon: '@mipmap/ic_launcher',
    );

    const details = NotificationDetails(android: androidDetails);

    await _notifications.show(
      DateTime.now().millisecondsSinceEpoch ~/ 1000,
      '🔄 Pembayaran Dibatalkan',
      '$customerName dikembalikan ke belum lunas. $waInfo',
      details,
    );
  }
}
