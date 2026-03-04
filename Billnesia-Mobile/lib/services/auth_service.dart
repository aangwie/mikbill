import 'package:flutter/material.dart';
import 'api_service.dart';
import 'storage_service.dart';

class AuthService extends ChangeNotifier {
  bool _isLoggedIn = false;
  bool _isLoading = false;
  String? _userName;
  String? _userEmail;
  String? _userRole;

  bool get isLoggedIn => _isLoggedIn;
  bool get isLoading => _isLoading;
  String? get userName => _userName;
  String? get userEmail => _userEmail;
  String? get userRole => _userRole;

  /// Check if user is already logged in (has stored token).
  Future<void> checkLoginStatus() async {
    final token = await StorageService.getToken();
    if (token != null) {
      final info = await StorageService.getUserInfo();
      _userName = info['name'];
      _userEmail = info['email'];
      _userRole = info['role'];
      _isLoggedIn = true;
    }
    notifyListeners();
  }

  /// Login with email and password.
  Future<String?> login(String email, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      final result = await ApiService.post(
        '/login',
        body: {'email': email, 'password': password},
      );

      if (result['success'] == true) {
        final data = result['data'];
        await StorageService.saveToken(data['token']);
        await StorageService.saveUserInfo(
          name: data['user']['name'],
          email: data['user']['email'],
          role: data['user']['role'],
        );

        _userName = data['user']['name'];
        _userEmail = data['user']['email'];
        _userRole = data['user']['role'];
        _isLoggedIn = true;
        _isLoading = false;
        notifyListeners();
        return null; // Success
      } else {
        _isLoading = false;
        notifyListeners();
        return result['message'] ?? 'Login gagal.';
      }
    } on ApiException catch (e) {
      _isLoading = false;
      notifyListeners();
      return e.message;
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      return 'Tidak dapat terhubung ke server. Periksa URL dan koneksi Anda.';
    }
  }

  /// Logout and revoke token.
  Future<void> logout() async {
    try {
      await ApiService.post('/logout');
    } catch (_) {}

    await StorageService.clearAll();
    _isLoggedIn = false;
    _userName = null;
    _userEmail = null;
    _userRole = null;
    notifyListeners();
  }
}
