import 'dart:async';
import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import 'app_config.dart';

/// Kesalahan dari web service, sudah berisi pesan yang siap ditampilkan.
class ApiException implements Exception {
  ApiException(this.message, {this.statusCode, this.errors = const {}});

  final String message;
  final int? statusCode;
  final Map<String, List<String>> errors;

  @override
  String toString() => message;
}

/// Klien HTTP untuk web service Laravel (REST API /api/v1) dengan token Sanctum.
class ApiClient {
  ApiClient._();

  static final ApiClient instance = ApiClient._();

  static const _baseUrlKey = 'api_base_url';
  static const _tokenKey = 'auth_token';
  static const _timeout = Duration(seconds: 20);

  late SharedPreferences _prefs;
  String _baseUrl = AppConfig.defaultBaseUrl;
  String? _token;

  /// Dipanggil ketika server membalas 401 (token tidak berlaku lagi).
  VoidCallback? onUnauthorized;

  String get baseUrl => _baseUrl;
  bool get hasToken => _token != null;

  Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
    _baseUrl = _prefs.getString(_baseUrlKey) ?? AppConfig.defaultBaseUrl;
    _token = _prefs.getString(_tokenKey);
  }

  Future<void> setBaseUrl(String url) async {
    var normalized = url.trim();
    while (normalized.endsWith('/')) {
      normalized = normalized.substring(0, normalized.length - 1);
    }
    if (!normalized.startsWith('http://') && !normalized.startsWith('https://')) {
      normalized = 'http://$normalized';
    }
    _baseUrl = normalized;
    await _prefs.setString(_baseUrlKey, normalized);
  }

  /// Mengubah path atau URL media (misal gambar produk / bukti transfer)
  /// agar selalu mengarah ke server baseUrl yang sedang aktif.
  static String resolveMediaUrl(String? rawUrl) {
    if (rawUrl == null || rawUrl.trim().isEmpty) return '';
    final trimmed = rawUrl.trim();
    final currentBase = instance.baseUrl;
    final baseUri = Uri.tryParse(currentBase);

    // Jika relative path (/storage/... atau storage/...)
    if (trimmed.startsWith('/')) {
      return '$currentBase$trimmed';
    }
    if (!trimmed.startsWith('http://') && !trimmed.startsWith('https://')) {
      return '$currentBase/$trimmed';
    }

    // Jika full URL yang menggunakan localhost, 127.0.0.1, atau *.test
    final uri = Uri.tryParse(trimmed);
    if (uri != null && baseUri != null) {
      final host = uri.host.toLowerCase();
      if (host == 'localhost' || host == '127.0.0.1' || host.endsWith('.test')) {
        return uri.replace(
          scheme: baseUri.scheme,
          host: baseUri.host,
          port: baseUri.hasPort ? baseUri.port : null,
        ).toString();
      }
    }

    return trimmed;
  }

  Future<void> setToken(String? token) async {
    _token = token;
    if (token == null) {
      await _prefs.remove(_tokenKey);
    } else {
      await _prefs.setString(_tokenKey, token);
    }
  }

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'User-Agent': 'Flutter-Android-Client/1.0',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  Uri _uri(String path, [Map<String, String>? query]) {
    final uri = Uri.parse('$_baseUrl/api/v1$path');
    return (query == null || query.isEmpty) ? uri : uri.replace(queryParameters: query);
  }

  Future<Map<String, dynamic>> get(String path, {Map<String, String>? query}) {
    return _send(() => http.get(_uri(path, query), headers: _headers));
  }

  Future<Map<String, dynamic>> post(String path, [Map<String, dynamic>? body]) {
    return _send(() => http.post(
          _uri(path),
          headers: {..._headers, 'Content-Type': 'application/json'},
          body: jsonEncode(body ?? const {}),
        ));
  }

  Future<Map<String, dynamic>> put(String path, Map<String, dynamic> body) {
    return _send(() => http.put(
          _uri(path),
          headers: {..._headers, 'Content-Type': 'application/json'},
          body: jsonEncode(body),
        ));
  }

  Future<Map<String, dynamic>> uploadBytes(
    String path, {
    required String field,
    required List<int> bytes,
    required String filename,
  }) {
    return _send(() async {
      final request = http.MultipartRequest('POST', _uri(path))
        ..headers.addAll(_headers)
        ..files.add(http.MultipartFile.fromBytes(field, bytes, filename: filename));

      return http.Response.fromStream(await request.send());
    });
  }

  Future<Map<String, dynamic>> _send(Future<http.Response> Function() request) async {
    final http.Response response;
    final stopwatch = Stopwatch()..start();

    try {
      response = await request().timeout(_timeout);
      stopwatch.stop();
      debugPrint('🌐 [HTTP REQUEST] ---> ${response.request?.method ?? 'HTTP'} ${response.request?.url}');
      debugPrint('📥 [HTTP RESPONSE] <--- ${response.statusCode} (${stopwatch.elapsedMilliseconds}ms) | Data: ${response.body.length} bytes');
    } on TimeoutException {
      throw ApiException('Server tidak merespons. Periksa koneksi dan alamat server.');
    } catch (e) {
      if (e is ApiException) {
        rethrow;
      }
      throw ApiException('Tidak dapat terhubung ke server ($_baseUrl).');
    }

    var body = <String, dynamic>{};
    if (response.body.isNotEmpty) {
      try {
        final decoded = jsonDecode(response.body);
        if (decoded is Map<String, dynamic>) {
          body = decoded;
        }
      } on FormatException {
        // Respons bukan JSON, gunakan pesan umum di bawah.
      }
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    }

    if (response.statusCode == 401) {
      if (_token != null) {
        await setToken(null);
        onUnauthorized?.call();
      }
      throw ApiException('Sesi Anda telah berakhir. Silakan masuk kembali.', statusCode: 401);
    }

    final errors = <String, List<String>>{};
    final rawErrors = body['errors'];
    if (rawErrors is Map) {
      rawErrors.forEach((key, value) {
        if (value is List && value.isNotEmpty) {
          errors['$key'] = value.map((message) => '$message').toList();
        }
      });
    }

    final message = errors.isNotEmpty
        ? errors.values.first.first
        : (body['message'] as String?) ?? 'Terjadi kesalahan (${response.statusCode}).';

    throw ApiException(message, statusCode: response.statusCode, errors: errors);
  }
}
