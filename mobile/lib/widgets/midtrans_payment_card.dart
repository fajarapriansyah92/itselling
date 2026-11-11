import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';

import '../core/api_client.dart';
import '../core/formatters.dart';
import '../models/models.dart';
import '../services/api_service.dart';

class MidtransPaymentCard extends StatefulWidget {
  const MidtransPaymentCard({
    super.key,
    required this.order,
    required this.onPaymentSuccess,
  });

  final Order order;
  final VoidCallback onPaymentSuccess;

  @override
  State<MidtransPaymentCard> createState() => _MidtransPaymentCardState();
}

class _MidtransPaymentCardState extends State<MidtransPaymentCard> {
  bool _isLoadingToken = false;
  bool _isSyncing = false;
  String? _snapToken;
  String? _vaNumber;
  String? _paymentBank;
  Timer? _pollingTimer;

  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  @override
  void initState() {
    super.initState();
    _snapToken = widget.order.snapToken;
    _vaNumber = widget.order.vaNumber;
    _paymentBank = widget.order.paymentBank;

    if (_snapToken == null && widget.order.status == 'pending') {
      _generateSnapToken();
    }

    if (widget.order.status == 'pending') {
      _pollingTimer = Timer.periodic(const Duration(milliseconds: 2500), (_) {
        if (mounted && widget.order.status == 'pending') {
          _silentSyncStatus();
        }
      });
    }
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    super.dispose();
  }

  Future<void> _generateSnapToken({bool force = false}) async {
    if (_isLoadingToken) return;

    setState(() => _isLoadingToken = true);

    try {
      final res = await ApiService.createOrderSnapToken(
        widget.order.orderNumber,
        paymentChannel: widget.order.paymentChannel,
        force: force,
      );
      if (mounted) {
        setState(() {
          _snapToken = res['token'] as String?;
          _isLoadingToken = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoadingToken = false);
      }
    }
  }

  Future<void> _silentSyncStatus() async {
    try {
      final res = await ApiService.syncOrderPaymentStatus(widget.order.orderNumber);
      if (!mounted) return;

      final isPaid = res['is_paid'] as bool? ?? false;
      final orderJson = res['order'] as Map<String, dynamic>?;

      if (orderJson != null) {
        final va = orderJson['va_number'] as String?;
        final bank = orderJson['payment_bank'] as String?;
        if (va != null && va != _vaNumber) {
          setState(() {
            _vaNumber = va;
            _paymentBank = bank;
          });
        }
      }

      if (isPaid) {
        _pollingTimer?.cancel();
        widget.onPaymentSuccess();
      }
    } catch (_) {}
  }

  Future<void> _manualSyncStatus() async {
    if (_isSyncing) return;

    setState(() => _isSyncing = true);

    try {
      final res = await ApiService.syncOrderPaymentStatus(widget.order.orderNumber);
      if (!mounted) return;

      final isPaid = res['is_paid'] as bool? ?? false;

      setState(() => _isSyncing = false);

      if (isPaid) {
        _pollingTimer?.cancel();
        widget.onPaymentSuccess();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              behavior: SnackBarBehavior.floating,
              backgroundColor: Colors.white,
              elevation: 6,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
                side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.2),
              ),
              content: const Row(
                children: [
                  Icon(Icons.check_circle_rounded, color: _primaryBlue, size: 20),
                  SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Pembayaran Berhasil Diverifikasi oleh Midtrans!',
                      style: TextStyle(color: _textDark, fontWeight: FontWeight.w700, fontSize: 13),
                    ),
                  ),
                ],
              ),
            ),
          );
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              behavior: SnackBarBehavior.floating,
              backgroundColor: Colors.white,
              elevation: 6,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
                side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.2),
              ),
              content: const Row(
                children: [
                  Icon(Icons.info_outline_rounded, color: _primaryBlue, size: 20),
                  SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Status masih menunggu pembayaran. Silakan selesaikan pembayaran.',
                      style: TextStyle(color: _textDark, fontWeight: FontWeight.w700, fontSize: 13),
                    ),
                  ),
                ],
              ),
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSyncing = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            behavior: SnackBarBehavior.floating,
            backgroundColor: const Color(0xFFFEF2F2),
            content: Text('Gagal mengecek status: $e', style: const TextStyle(color: Color(0xFFB91C1C))),
          ),
        );
      }
    }
  }

  Future<void> _openPaymentWeb() async {
    final copyText = _vaNumber ?? widget.order.orderNumber;
    await Clipboard.setData(ClipboardData(text: copyText));

    final baseUrl = ApiClient.instance.baseUrl.replaceAll(RegExp(r'/+$'), '');
    final uri = Uri.parse('$baseUrl/pay/${widget.order.orderNumber}');

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.white,
          elevation: 6,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
            side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.2),
          ),
          content: Row(
            children: [
              const Icon(Icons.open_in_browser_rounded, color: _primaryBlue, size: 20),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  _vaNumber != null
                      ? 'Nomor VA ($copyText) otomatis disalin! Membuka halaman pembayaran di Chrome...'
                      : 'Membuka halaman pembayaran di Chrome...',
                  style: const TextStyle(color: _textDark, fontWeight: FontWeight.w700, fontSize: 13),
                ),
              ),
            ],
          ),
        ),
      );
    }

    try {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal membuka browser Chrome: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFBFDBFE), width: 1.4),
        boxShadow: [
          BoxShadow(
            color: _primaryBlue.withValues(alpha: 0.08),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFBFDBFE)),
                      ),
                      child: const Icon(Icons.flash_on_rounded, color: _primaryBlue, size: 20),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Midtrans Payment Gateway',
                            style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5, color: _textDark),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          Text(
                            widget.order.paymentChannel != null && widget.order.paymentChannel!.isNotEmpty
                                ? 'Metode Terpilih: ${widget.order.paymentChannel}'
                                : 'Virtual Account & Kartu Kredit',
                            style: const TextStyle(fontSize: 10.5, color: _textMuted),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    SizedBox(
                      width: 6,
                      height: 6,
                      child: DecoratedBox(
                        decoration: BoxDecoration(color: _primaryBlue, shape: BoxShape.circle),
                      ),
                    ),
                    SizedBox(width: 5),
                    Text(
                      'Live Realtime',
                      style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: _primaryBlue),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _borderLight),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Total Tagihan', style: TextStyle(fontSize: 11, color: _textMuted)),
                    Text('Verifikasi Instan', style: TextStyle(fontSize: 9.5, color: _primaryBlue, fontWeight: FontWeight.bold)),
                  ],
                ),
                Text(
                  formatRupiah(widget.order.total),
                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17, color: _primaryBlue),
                ),
              ],
            ),
          ),

          if (widget.order.isCreditCard) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.credit_card, color: _primaryBlue, size: 28),
                  SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Kartu Kredit (Sandbox 3DS)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: _textDark)),
                        SizedBox(height: 2),
                        Text('Midtrans Test Card: 4811-1111-1111-1114', style: TextStyle(fontSize: 10, color: _textMuted, fontFamily: 'monospace')),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ] else if (_vaNumber != null) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          'Nomor Virtual Account (${_paymentBank ?? "Bank"})',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF1E40AF)),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: _primaryBlue,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Text(
                          'Siap Bayar',
                          style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: Colors.white),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: SelectableText(
                          _vaNumber!,
                          style: const TextStyle(
                            fontFamily: 'monospace',
                            fontSize: 18,
                            fontWeight: FontWeight.w900,
                            color: _primaryBlue,
                            letterSpacing: 1.2,
                          ),
                        ),
                      ),
                      IconButton(
                        tooltip: 'Salin Nomor VA',
                        icon: const Icon(Icons.copy_rounded, color: _primaryBlue, size: 18),
                        onPressed: () {
                          Clipboard.setData(ClipboardData(text: _vaNumber!));
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              behavior: SnackBarBehavior.floating,
                              backgroundColor: Colors.white,
                              elevation: 6,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(14),
                                side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.2),
                              ),
                              content: const Row(
                                children: [
                                  Icon(Icons.copy_rounded, color: _primaryBlue, size: 18),
                                  SizedBox(width: 10),
                                  Expanded(
                                    child: Text(
                                      'Nomor Virtual Account berhasil disalin!',
                                      style: TextStyle(color: _textDark, fontWeight: FontWeight.w700, fontSize: 13),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],

          const SizedBox(height: 16),

          if (_isLoadingToken)
            const Center(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 12),
                child: CircularProgressIndicator(color: _primaryBlue),
              ),
            )
          else ...[
            FilledButton.icon(
              onPressed: _openPaymentWeb,
              style: FilledButton.styleFrom(
                backgroundColor: _primaryBlue,
                foregroundColor: Colors.white,
                minimumSize: const Size.fromHeight(50),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                elevation: 0,
              ),
              icon: const Icon(Icons.bolt_rounded, size: 20, color: Colors.white),
              label: const Text(
                'Bayar Sekarang',
                style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
              ),
            ),
            const SizedBox(height: 10),

            Center(
              child: TextButton.icon(
                onPressed: _isSyncing ? null : _manualSyncStatus,
                icon: _isSyncing
                    ? const SizedBox(
                        width: 14,
                        height: 14,
                        child: CircularProgressIndicator(strokeWidth: 2, color: _primaryBlue),
                      )
                    : const Icon(Icons.sync_rounded, size: 16, color: _primaryBlue),
                label: Text(
                  _isSyncing ? 'Mengecek ke server Midtrans...' : 'Cek Status Pembayaran Sekarang',
                  style: const TextStyle(fontSize: 11.5, color: _primaryBlue, fontWeight: FontWeight.w700),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
