import 'package:flutter/foundation.dart' hide Category;

import '../core/api_client.dart';
import '../models/models.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  AuthProvider({this.onSignedOut}) {
    ApiClient.instance.onUnauthorized = _handleUnauthorized;
  }

  /// Dipanggil saat sesi berakhir, misalnya untuk menutup halaman yang sedang terbuka.
  final VoidCallback? onSignedOut;

  User? _user;
  bool _isRestoring = true;

  User? get user => _user;
  bool get isRestoring => _isRestoring;
  bool get isAuthenticated => _user != null;

  /// Memulihkan sesi dari token yang tersimpan saat aplikasi dibuka.
  Future<void> restoreSession() async {
    if (ApiClient.instance.hasToken) {
      try {
        _user = await ApiService.fetchProfile();
      } on ApiException {
        _user = null;
      }
    }

    _isRestoring = false;
    notifyListeners();
  }

  Future<void> login({required String email, required String password}) async {
    final (token, user) = await ApiService.login(email: email, password: password);
    await _startSession(token, user);
  }

  Future<void> register({
    required String name,
    required String email,
    required String phone,
    required String address,
    required String password,
    required String passwordConfirmation,
  }) async {
    final (token, user) = await ApiService.register(
      name: name,
      email: email,
      phone: phone,
      address: address,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );
    await _startSession(token, user);
  }

  Future<void> updateProfile({required String name, required String phone, required String address}) async {
    _user = await ApiService.updateProfile(name: name, phone: phone, address: address);
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await ApiService.logout();
    } on ApiException {
      // Token tetap dihapus dari perangkat walaupun server tidak dapat dihubungi.
    }

    await ApiClient.instance.setToken(null);
    _user = null;
    notifyListeners();
  }

  Future<void> _startSession(String token, User user) async {
    await ApiClient.instance.setToken(token);
    _user = user;
    notifyListeners();
  }

  void _handleUnauthorized() {
    _user = null;
    notifyListeners();
    onSignedOut?.call();
  }
}
