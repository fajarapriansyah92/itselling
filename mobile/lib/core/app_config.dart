import 'package:flutter/foundation.dart';

/// Konfigurasi statis aplikasi. Sesuaikan data rekening dengan instansi.
class AppConfig {
  AppConfig._();

  static const appName = 'Digital Solusi Cerdas';

  /// Alamat server default.
  /// - Web / Chrome: otomatis http://localhost:8000
  /// - Android Emulator: otomatis http://10.0.2.2:8000
  /// - Bisa diganti saat build atau lewat menu "Alamat Server" di aplikasi.
  static String get defaultBaseUrl {
    const fromEnv = String.fromEnvironment('API_BASE_URL');
    if (fromEnv.isNotEmpty) {
      return fromEnv;
    }
    return kIsWeb ? 'http://localhost:8000' : 'http://10.0.2.2:8000';
  }

  static const bankName = 'Bank BCA';
  static const bankAccountNumber = '123 456 7890';
  static const bankAccountHolder = 'PT Digital Solusi Cerdas';
}
