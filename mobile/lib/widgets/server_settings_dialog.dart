import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../services/api_service.dart';

/// Dialog interaktif untuk memeriksa dan mengganti konfigurasi server & gateway API.
class ServerSettingsDialog extends StatefulWidget {
  const ServerSettingsDialog({super.key});

  static Future<void> show(BuildContext context) {
    return showDialog<void>(context: context, builder: (_) => const ServerSettingsDialog());
  }

  @override
  State<ServerSettingsDialog> createState() => _ServerSettingsDialogState();
}

class _ServerSettingsDialogState extends State<ServerSettingsDialog> {
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderSoft = Color(0xFFE5EAF2);

  late final _controller = TextEditingController(text: ApiClient.instance.baseUrl);
  String? _result;
  bool? _isConnected;
  int? _latencyMs;
  bool _isTesting = false;

  @override
  void initState() {
    super.initState();
    // Auto test current connection when opened
    _testConnection();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _testConnection() async {
    setState(() {
      _isTesting = true;
      _result = null;
    });

    await ApiClient.instance.setBaseUrl(_controller.text.trim());

    final sw = Stopwatch()..start();
    try {
      final categories = await ApiService.fetchCategories();
      sw.stop();
      if (mounted) {
        setState(() {
          _isTesting = false;
          _isConnected = true;
          _latencyMs = sw.elapsedMilliseconds;
          _result = 'Koneksi Berhasil (${categories.length} kategori aktif)';
        });
      }
    } on ApiException catch (error) {
      sw.stop();
      if (mounted) {
        setState(() {
          _isTesting = false;
          _isConnected = false;
          _latencyMs = null;
          _result = error.message;
        });
      }
    } catch (e) {
      sw.stop();
      if (mounted) {
        setState(() {
          _isTesting = false;
          _isConnected = false;
          _latencyMs = null;
          _result = 'Tidak dapat menghubungi server: $e';
        });
      }
    }
  }

  void _applyPreset(String url) {
    setState(() {
      _controller.text = url;
    });
    _testConnection();
  }

  Future<void> _save() async {
    await ApiClient.instance.setBaseUrl(_controller.text.trim());
    if (mounted) {
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
            side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.2),
          ),
          content: Text(
            'Alamat server diperbarui ke: ${_controller.text.trim()}',
            style: const TextStyle(color: _textDark, fontWeight: FontWeight.w700, fontSize: 13),
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      backgroundColor: Colors.white,
      surfaceTintColor: Colors.transparent,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(22),
        side: const BorderSide(color: _borderSoft, width: 1.5),
      ),
      contentPadding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
      titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
      title: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFBFDBFE)),
            ),
            child: const Icon(Icons.show_chart_rounded, color: _primaryBlue, size: 20),
          ),
          const SizedBox(width: 12),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Status Server & Jaringan',
                  style: TextStyle(color: _textDark, fontWeight: FontWeight.w800, fontSize: 16),
                ),
                Text(
                  'Diagnostik koneksi web service & API',
                  style: TextStyle(color: _textMuted, fontSize: 11),
                ),
              ],
            ),
          ),
        ],
      ),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 8),

            // Live Status Card
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _isConnected == true
                    ? const Color(0xFFECFDF5)
                    : (_isConnected == false ? const Color(0xFFFEF2F2) : const Color(0xFFF9FAFB)),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: _isConnected == true
                      ? const Color(0xFFA7F3D0)
                      : (_isConnected == false ? const Color(0xFFFECACA) : _borderSoft),
                ),
              ),
              child: Row(
                children: [
                  Container(
                    width: 10,
                    height: 10,
                    decoration: BoxDecoration(
                      color: _isTesting
                          ? const Color(0xFFF59E0B)
                          : (_isConnected == true
                              ? const Color(0xFF10B981)
                              : (_isConnected == false ? const Color(0xFFEF4444) : _textMuted)),
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _isTesting
                              ? 'Menguji Koneksi...'
                              : (_isConnected == true ? 'SERVER TERHUBUNG (ONLINE)' : 'SERVER TIDAK TERHUBUNG'),
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                            color: _isConnected == true
                                ? const Color(0xFF059669)
                                : (_isConnected == false ? const Color(0xFFDC2626) : _textMuted),
                          ),
                        ),
                        if (_result != null) ...[
                          const SizedBox(height: 2),
                          Text(
                            _result!,
                            style: const TextStyle(fontSize: 11, color: _textMuted),
                          ),
                        ],
                      ],
                    ),
                  ),
                  if (_latencyMs != null)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: const Color(0xFFA7F3D0)),
                      ),
                      child: Text(
                        '${_latencyMs}ms',
                        style: const TextStyle(
                          color: Color(0xFF059669),
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                          fontFamily: 'monospace',
                        ),
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Input Field
            const Text(
              'ALAMAT ENDPOINT API',
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w800,
                color: _textMuted,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 6),
            TextField(
              controller: _controller,
              keyboardType: TextInputType.url,
              style: const TextStyle(color: _textDark, fontSize: 13, fontFamily: 'monospace', fontWeight: FontWeight.w600),
              decoration: InputDecoration(
                filled: true,
                fillColor: const Color(0xFFF9FAFB),
                prefixIcon: const Icon(Icons.link_rounded, color: _primaryBlue, size: 18),
                hintText: 'http://10.0.2.2:8000',
                hintStyle: const TextStyle(color: Color(0xFF9CA3AF), fontSize: 13),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: _borderSoft),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: _primaryBlue, width: 1.5),
                ),
              ),
            ),
            const SizedBox(height: 12),

            // Quick Preset Chips
            const Text(
              'PRESET CEPAT',
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w800,
                color: _textMuted,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 6),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                _buildPresetChip('10.0.2.2:8000 (Emulator)', 'http://10.0.2.2:8000'),
                _buildPresetChip('127.0.0.1:8000 (Local)', 'http://127.0.0.1:8000'),
                _buildPresetChip('Laragon Dev', 'http://itselling.test'),
              ],
            ),
          ],
        ),
      ),
      actionsPadding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
      actions: [
        OutlinedButton.icon(
          style: OutlinedButton.styleFrom(
            foregroundColor: _textDark,
            side: const BorderSide(color: _borderSoft),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
          onPressed: _isTesting ? null : _testConnection,
          icon: _isTesting
              ? const SizedBox.square(
                  dimension: 14,
                  child: CircularProgressIndicator(strokeWidth: 2, color: _primaryBlue),
                )
              : const Icon(Icons.refresh_rounded, size: 16),
          label: const Text('Tes Ulang', style: TextStyle(fontWeight: FontWeight.w700)),
        ),
        FilledButton(
          style: FilledButton.styleFrom(
            backgroundColor: _primaryBlue,
            foregroundColor: Colors.white,
            textStyle: const TextStyle(fontWeight: FontWeight.w800),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
          onPressed: _save,
          child: const Text('Simpan & Terapkan'),
        ),
      ],
    );
  }

  Widget _buildPresetChip(String label, String url) {
    final isSelected = _controller.text.trim() == url;
    return InkWell(
      borderRadius: BorderRadius.circular(8),
      onTap: () => _applyPreset(url),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFFEFF6FF) : const Color(0xFFF9FAFB),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isSelected ? _primaryBlue : _borderSoft,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 10,
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            color: isSelected ? _primaryBlue : _textMuted,
          ),
        ),
      ),
    );
  }
}
