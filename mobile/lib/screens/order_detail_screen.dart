import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../core/api_client.dart';
import '../core/formatters.dart';
import '../models/models.dart';
import '../services/api_service.dart';
import '../widgets/error_banner.dart';
import '../widgets/midtrans_payment_card.dart';
import '../widgets/status_chip.dart';
import 'order_chat_screen.dart';

class OrderDetailScreen extends StatefulWidget {
  const OrderDetailScreen({super.key, required this.orderNumber, this.justPlaced = false});

  final String orderNumber;
  final bool justPlaced;

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  Order? _order;
  String? _error;
  bool _isBusy = false;
  bool _isTrackingExpanded = true;

  static const Color _bgPage = Color(0xFFF3F6FC);
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _error = null);

    try {
      final order = await ApiService.fetchOrder(widget.orderNumber);
      if (mounted) {
        setState(() => _order = order);
      }
    } on ApiException catch (error) {
      if (mounted) {
        setState(() => _error = error.message);
      }
    }
  }

  Future<void> _runAction(Future<Order> Function() action, String successMessage) async {
    final messenger = ScaffoldMessenger.of(context);
    setState(() => _isBusy = true);

    try {
      final order = await action();
      if (mounted) {
        setState(() => _order = order);
      }
      messenger.showSnackBar(
        SnackBar(
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.white,
          elevation: 6,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
            side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.2),
          ),
          content: Row(
            children: [
              const Icon(Icons.check_circle_rounded, color: _primaryBlue, size: 20),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  successMessage,
                  style: const TextStyle(color: _textDark, fontWeight: FontWeight.w700, fontSize: 13),
                ),
              ),
            ],
          ),
        ),
      );
    } on ApiException catch (error) {
      messenger.showSnackBar(
        SnackBar(
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.white,
          elevation: 6,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
            side: const BorderSide(color: Color(0xFFFECACA), width: 1.2),
          ),
          content: Row(
            children: [
              const Icon(Icons.error_outline, color: Color(0xFFDC2626), size: 20),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  error.message,
                  style: const TextStyle(color: _textDark, fontWeight: FontWeight.w700, fontSize: 13),
                ),
              ),
            ],
          ),
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isBusy = false);
      }
    }
  }

  Future<void> _cancelOrder() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: const BorderSide(color: _borderLight),
        ),
        title: const Text('Batalkan Pesanan?', style: TextStyle(color: _textDark, fontWeight: FontWeight.w800)),
        content: const Text(
          'Pesanan yang dibatalkan tidak dapat diproses lagi dan stok barang akan dikembalikan.',
          style: TextStyle(color: _textMuted, fontSize: 13.5),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Kembali', style: TextStyle(color: _textMuted, fontWeight: FontWeight.w700)),
          ),
          FilledButton(
            style: FilledButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              foregroundColor: Colors.white,
            ),
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('Ya, Batalkan', style: TextStyle(fontWeight: FontWeight.w800)),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await _runAction(() => ApiService.cancelOrder(widget.orderNumber), 'Pesanan berhasil dibatalkan.');
    }
  }

  Future<void> _confirmReceiveHardwareOrder() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: const BorderSide(color: _borderLight),
        ),
        title: const Row(
          children: [
            Icon(Icons.inventory_2_outlined, color: _primaryBlue, size: 22),
            SizedBox(width: 10),
            Expanded(
              child: Text('Konfirmasi Penerimaan', style: TextStyle(color: _textDark, fontWeight: FontWeight.w800, fontSize: 16)),
            ),
          ],
        ),
        content: const Text(
          'Pastikan barang hardware telah tiba di alamat Anda dan telah dicek dengan baik sebelum menyelesaikan pesanan.',
          style: TextStyle(color: _textMuted, fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Batal', style: TextStyle(color: _textMuted, fontWeight: FontWeight.w700)),
          ),
          FilledButton(
            style: FilledButton.styleFrom(
              backgroundColor: _primaryBlue,
              foregroundColor: Colors.white,
              textStyle: const TextStyle(fontWeight: FontWeight.w800),
            ),
            onPressed: () => Navigator.of(ctx).pop(true),
            child: const Text('Ya, Sudah Diterima'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await _runAction(
        () => ApiService.receiveOrder(widget.orderNumber),
        'Pesanan hardware telah selesai diterima. Terima kasih!',
      );
    }
  }

  void _copyToClipboard(String text, String label) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        behavior: SnackBarBehavior.floating,
        backgroundColor: Colors.white,
        elevation: 6,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.2),
        ),
        content: Row(
          children: [
            const Icon(Icons.copy_rounded, color: _primaryBlue, size: 18),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                '$label berhasil disalin.',
                style: const TextStyle(color: _textDark, fontWeight: FontWeight.w700, fontSize: 13),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showReviewModal(OrderItem item) {
    if (_order == null) return;
    int selectedRating = item.review?.rating ?? 5;
    final commentCtrl = TextEditingController(text: item.review?.comment ?? '');
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            final labels = {
              1: 'Sangat Kecewa ⭐',
              2: 'Kurang Puas ⭐⭐',
              3: 'Cukup ⭐⭐⭐',
              4: 'Puas & Bagus ⭐⭐⭐⭐',
              5: 'Sangat Puas & Rekomendasi! ⭐⭐⭐⭐⭐',
            };

            return Padding(
              padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
              child: Container(
                decoration: const BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
                ),
                padding: const EdgeInsets.fromLTRB(20, 14, 20, 24),
                child: SafeArea(
                  top: false,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Center(
                        child: Container(
                          width: 40,
                          height: 4,
                          decoration: BoxDecoration(
                            color: const Color(0xFFD1D5DB),
                            borderRadius: BorderRadius.circular(2),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFEF3C7),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.star_rounded, color: Color(0xFFF59E0B), size: 22),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item.isReviewed ? 'Ubah Ulasan Produk' : 'Beri Ulasan Produk',
                                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: _textDark),
                                ),
                                Text(
                                  item.productName,
                                  style: const TextStyle(fontSize: 12, color: _textMuted),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.close_rounded, color: _textMuted),
                            onPressed: () => Navigator.pop(ctx),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),
                      Center(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: List.generate(5, (index) {
                            final star = index + 1;
                            return IconButton(
                              iconSize: 38,
                              padding: const EdgeInsets.symmetric(horizontal: 2),
                              onPressed: isSubmitting
                                  ? null
                                  : () {
                                      setModalState(() => selectedRating = star);
                                    },
                              icon: Icon(
                                star <= selectedRating ? Icons.star_rounded : Icons.star_outline_rounded,
                                color: star <= selectedRating ? const Color(0xFFF59E0B) : const Color(0xFFD1D5DB),
                              ),
                            );
                          }),
                        ),
                      ),
                      Center(
                        child: Text(
                          labels[selectedRating] ?? '',
                          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFFD97706)),
                        ),
                      ),
                      const SizedBox(height: 18),
                      TextField(
                        controller: commentCtrl,
                        maxLines: 4,
                        style: const TextStyle(color: _textDark, fontSize: 13.5),
                        decoration: InputDecoration(
                          hintText: 'Tuliskan pengalaman Anda, kualitas barang, pelayanan, dll...',
                          hintStyle: const TextStyle(color: Color(0xFF9CA3AF), fontSize: 13),
                          filled: true,
                          fillColor: const Color(0xFFF8FAFC),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: _borderLight),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: _borderLight),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: _primaryBlue, width: 1.5),
                          ),
                        ),
                      ),
                      const SizedBox(height: 18),
                      FilledButton(
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                final text = commentCtrl.text.trim();
                                if (text.length < 3) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('Ulasan minimal 3 karakter.')),
                                  );
                                  return;
                                }

                                final messenger = ScaffoldMessenger.of(context);
                                setModalState(() => isSubmitting = true);
                                try {
                                  await ApiService.submitOrderReview(
                                    orderNumber: _order!.orderNumber,
                                    orderItemId: item.id,
                                    rating: selectedRating,
                                    comment: text,
                                  );
                                  if (ctx.mounted) {
                                    Navigator.pop(ctx);
                                  }
                                  if (mounted) {
                                    messenger.showSnackBar(
                                      const SnackBar(
                                        behavior: SnackBarBehavior.floating,
                                        content: Text('Terima kasih! Ulasan Anda berhasil disimpan.'),
                                      ),
                                    );
                                    _load();
                                  }
                                } catch (e) {
                                  if (ctx.mounted) {
                                    setModalState(() => isSubmitting = false);
                                  }
                                  if (mounted) {
                                    messenger.showSnackBar(
                                      SnackBar(content: Text('Gagal menyimpan ulasan: $e')),
                                    );
                                  }
                                }
                              },
                        style: FilledButton.styleFrom(
                          backgroundColor: _primaryBlue,
                          foregroundColor: Colors.white,
                          minimumSize: const Size.fromHeight(48),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: isSubmitting
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                              )
                            : Text(
                                item.isReviewed ? 'Perbarui Ulasan' : 'Kirim Ulasan',
                                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
                              ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showAllLicensesModal(OrderItem item) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
          child: SafeArea(
            top: false,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: const Color(0xFFD1D5DB),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.vpn_key_rounded, color: _primaryBlue, size: 22),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item.productName,
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: _textDark),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          Text(
                            'Total ${item.licenseKeys.length} Kode Lisensi Digital',
                            style: const TextStyle(fontSize: 12, color: _textMuted),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, color: _textMuted),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                FilledButton.icon(
                  onPressed: () {
                    final allKeys = item.licenseKeys.join('\n');
                    _copyToClipboard(allKeys, 'Semua ${item.licenseKeys.length} Kode Lisensi');
                  },
                  style: FilledButton.styleFrom(
                    backgroundColor: _primaryBlue,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    textStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
                  ),
                  icon: const Icon(Icons.copy_all_rounded, size: 18),
                  label: Text('Salin Semua Lisensi (${item.licenseKeys.length})'),
                ),
                const SizedBox(height: 14),
                ConstrainedBox(
                  constraints: BoxConstraints(
                    maxHeight: MediaQuery.of(context).size.height * 0.45,
                  ),
                  child: ListView.separated(
                    shrinkWrap: true,
                    itemCount: item.licenseKeys.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (_, index) {
                      final key = item.licenseKeys[index];
                      return Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFF93C5FD), width: 1.2),
                        ),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                              decoration: BoxDecoration(
                                color: _primaryBlue,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                '#${index + 1}',
                                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.white),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: SelectableText(
                                key,
                                style: const TextStyle(
                                  fontFamily: 'monospace',
                                  fontWeight: FontWeight.w900,
                                  fontSize: 14,
                                  color: _primaryBlue,
                                  letterSpacing: 1.1,
                                ),
                              ),
                            ),
                            IconButton(
                              visualDensity: VisualDensity.compact,
                              icon: const Icon(Icons.copy_rounded, size: 18, color: _primaryBlue),
                              tooltip: 'Salin lisensi ini',
                              onPressed: () => _copyToClipboard(key, 'Lisensi #${index + 1}'),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final order = _order;

    return Scaffold(
      backgroundColor: _bgPage,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: _textDark,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        title: const Text(
          'Detail & Tracking Pesanan',
          style: TextStyle(color: _textDark, fontWeight: FontWeight.w800, fontSize: 16),
        ),
        iconTheme: const IconThemeData(color: _textDark),
        actions: [
          IconButton(
            tooltip: 'Chat Admin / Teknisi',
            icon: const Icon(Icons.forum_outlined, color: _primaryBlue),
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => OrderChatScreen(
                    orderNumber: widget.orderNumber,
                    orderStatusLabel: order?.statusLabel,
                  ),
                ),
              );
            },
          ),
          IconButton(
            tooltip: 'Perbarui',
            icon: const Icon(Icons.refresh_rounded, color: _primaryBlue),
            onPressed: _load,
          ),
        ],
      ),
      body: order == null
          ? (_error != null
              ? ErrorView(message: _error!, onRetry: _load)
              : const Center(child: CircularProgressIndicator(color: _primaryBlue)))
          : RefreshIndicator(
              onRefresh: _load,
              color: _primaryBlue,
              backgroundColor: Colors.white,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  if (widget.justPlaced) ...[
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: const Color(0xFFECFDF5),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFA7F3D0)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 28),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  order.isCreditCard || order.status == 'paid'
                                      ? 'Pembayaran Berhasil Dikonfirmasi!'
                                      : 'Pesanan Berhasil Dibuat!',
                                  style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF065F46), fontSize: 14),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  order.isCreditCard || order.status == 'paid'
                                      ? 'Pembayaran kartu kredit Anda telah terverifikasi lunas secara otomatis.'
                                      : 'Silakan selesaikan pembayaran melalui Midtrans Payment Gateway di bawah.',
                                  style: const TextStyle(fontSize: 12, color: Color(0xFF047857)),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                  ],

                  // Order Header & Status Card
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: _borderLight),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Nomor Invoice', style: TextStyle(fontSize: 11, color: _textMuted)),
                                  const SizedBox(height: 2),
                                  Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Flexible(
                                        child: Text(
                                          order.orderNumber,
                                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: _textDark),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                      const SizedBox(width: 6),
                                      InkWell(
                                        onTap: () => _copyToClipboard(order.orderNumber, 'Nomor Invoice'),
                                        child: const Icon(Icons.copy_rounded, size: 15, color: _primaryBlue),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 8),
                            StatusChip(status: order.status, label: order.statusLabel),
                          ],
                        ),
                        const SizedBox(height: 12),
                        const Divider(height: 1, color: _borderLight),
                        const SizedBox(height: 12),
                        Text('Waktu Pemesanan: ${formatDateTime(order.createdAt)}', style: const TextStyle(fontSize: 12, color: _textMuted)),
                      ],
                    ),
                  ),

                  const SizedBox(height: 14),

                  // Visual Status Timeline Tracker
                  if (order.isDigitalOrder)
                    _buildDigitalStatusTracker(order.status)
                  else if (order.isServiceOrder)
                    _buildServiceStatusTracker(order.status)
                  else
                    _buildStatusTracker(order.status),

                  const SizedBox(height: 14),

                  // Dedicated Hardware Tracking Card
                  if (order.isHardwareOrder && (order.status == 'shipped' || order.status == 'delivered' || order.status == 'completed')) ...[
                    _buildHardwareTrackingCard(order),
                    const SizedBox(height: 14),
                  ],

                  // Midtrans Online Payment Gateway
                  if (order.status == 'pending') ...[
                    MidtransPaymentCard(
                      order: order,
                      onPaymentSuccess: _load,
                    ),
                    const SizedBox(height: 14),
                  ] else if (order.isCreditCard || order.status == 'paid') ...[
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: const Color(0xFFBFDBFE)),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            order.isCreditCard ? Icons.credit_card_rounded : Icons.verified_rounded,
                            color: _primaryBlue,
                            size: 24,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  order.isCreditCard ? 'Pembayaran Kartu Kredit' : 'Pembayaran Terverifikasi',
                                  style: const TextStyle(fontWeight: FontWeight.w800, color: _textDark, fontSize: 13),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  order.paymentChannel != null && order.paymentChannel!.isNotEmpty
                                      ? 'Metode: ${order.paymentChannel} • Diverifikasi sistem'
                                      : 'Pembayaran telah diverifikasi lunas oleh sistem.',
                                  style: const TextStyle(fontSize: 11.5, color: _textMuted),
                                ),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: const Color(0xFFD1FAE5),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: const Color(0xFF34D399)),
                            ),
                            child: const Text('LUNAS', style: TextStyle(color: Color(0xFF047857), fontWeight: FontWeight.w900, fontSize: 10.5)),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                  ],

                  // Payment Proof Preview if uploaded
                  if (order.paymentProofUrl != null) ...[
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: _borderLight),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.image_outlined, color: _primaryBlue, size: 20),
                              SizedBox(width: 8),
                              Text('Bukti Pembayaran Terunggah', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: _textDark)),
                            ],
                          ),
                          const SizedBox(height: 12),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(10),
                            child: Image.network(
                              ApiClient.resolveMediaUrl(order.paymentProofUrl!),
                              fit: BoxFit.cover,
                              height: 180,
                              width: double.infinity,
                              errorBuilder: (_, __, ___) => Container(
                                height: 100,
                                color: const Color(0xFFF8FAFC),
                                alignment: Alignment.center,
                                child: const Text('Bukti pembayaran telah disimpan di server.', style: TextStyle(color: _textMuted)),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                  ],

                  // Order Items Card
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: _borderLight),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Rincian Layanan / Produk IT', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: _textDark)),
                        const SizedBox(height: 12),
                        for (final item in order.items) ...[
                          Padding(
                            padding: const EdgeInsets.symmetric(vertical: 6),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    if (item.imageUrl != null && item.imageUrl!.isNotEmpty)
                                      ClipRRect(
                                        borderRadius: BorderRadius.circular(6),
                                        child: SizedBox(
                                          width: 38,
                                          height: 38,
                                          child: Image.network(
                                            ApiClient.resolveMediaUrl(item.imageUrl),
                                            fit: BoxFit.cover,
                                            errorBuilder: (_, __, ___) => Container(
                                              padding: const EdgeInsets.all(6),
                                              decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                              child: const Icon(Icons.check_circle_outline, size: 16, color: _primaryBlue),
                                            ),
                                          ),
                                        ),
                                      )
                                    else
                                      Container(
                                        padding: const EdgeInsets.all(6),
                                        decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                        child: const Icon(Icons.check_circle_outline, size: 16, color: _primaryBlue),
                                      ),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(item.productName, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: _textDark)),
                                          Text('${item.quantity} × ${formatRupiah(item.price)}', style: const TextStyle(fontSize: 12, color: _textMuted)),
                                        ],
                                      ),
                                    ),
                                    Text(
                                      formatRupiah(item.subtotal),
                                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: _primaryBlue),
                                    ),
                                  ],
                                ),
                                if (item.licenseKeys.isNotEmpty) ...[
                                  const SizedBox(height: 8),
                                  if (item.licenseKeys.length == 1)
                                    Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFEFF6FF),
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(color: const Color(0xFF93C5FD), width: 1.2),
                                      ),
                                      child: Row(
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.all(7),
                                            decoration: BoxDecoration(
                                              color: _primaryBlue,
                                              borderRadius: BorderRadius.circular(8),
                                            ),
                                            child: const Icon(Icons.key_rounded, size: 18, color: Colors.white),
                                          ),
                                          const SizedBox(width: 10),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                const Text(
                                                  'KODE LISENSI RESMI',
                                                  style: TextStyle(
                                                    fontSize: 10,
                                                    fontWeight: FontWeight.w800,
                                                    color: Color(0xFF1E40AF),
                                                    letterSpacing: 0.8,
                                                  ),
                                                ),
                                                const SizedBox(height: 3),
                                                SelectableText(
                                                  item.licenseKeys.first,
                                                  style: const TextStyle(
                                                    fontFamily: 'monospace',
                                                    fontWeight: FontWeight.w900,
                                                    fontSize: 14,
                                                    color: _primaryBlue,
                                                    letterSpacing: 1.1,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                          IconButton(
                                            visualDensity: VisualDensity.compact,
                                            icon: const Icon(Icons.copy_rounded, size: 18, color: _primaryBlue),
                                            tooltip: 'Salin lisensi',
                                            onPressed: () => _copyToClipboard(item.licenseKeys.first, 'Kode Lisensi'),
                                          ),
                                        ],
                                      ),
                                    )
                                  else
                                    Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFEFF6FF),
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(color: const Color(0xFF93C5FD), width: 1.2),
                                      ),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            children: [
                                              Container(
                                                padding: const EdgeInsets.all(6),
                                                decoration: BoxDecoration(
                                                  color: _primaryBlue,
                                                  borderRadius: BorderRadius.circular(8),
                                                ),
                                                child: const Icon(Icons.vpn_key_rounded, size: 16, color: Colors.white),
                                              ),
                                              const SizedBox(width: 8),
                                              Expanded(
                                                child: Text(
                                                  '${item.licenseKeys.length} KODE LISENSI RESMI AKTIF',
                                                  style: const TextStyle(
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.w800,
                                                    color: _primaryBlue,
                                                    letterSpacing: 0.5,
                                                  ),
                                                ),
                                              ),
                                              InkWell(
                                                onTap: () => _copyToClipboard(item.licenseKeys.join('\n'), 'Semua Lisensi'),
                                                borderRadius: BorderRadius.circular(6),
                                                child: Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                                  decoration: BoxDecoration(
                                                    color: Colors.white,
                                                    borderRadius: BorderRadius.circular(6),
                                                    border: Border.all(color: const Color(0xFF93C5FD)),
                                                  ),
                                                  child: const Row(
                                                    children: [
                                                      Icon(Icons.copy_all_rounded, size: 13, color: _primaryBlue),
                                                      SizedBox(width: 4),
                                                      Text('Salin Semua', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: _primaryBlue)),
                                                    ],
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 10),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                            decoration: BoxDecoration(
                                              color: Colors.white,
                                              borderRadius: BorderRadius.circular(8),
                                              border: Border.all(color: _borderLight),
                                            ),
                                            child: Row(
                                              children: [
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                  decoration: BoxDecoration(
                                                    color: _primaryBlue,
                                                    borderRadius: BorderRadius.circular(4),
                                                  ),
                                                  child: const Text('#1', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w900, color: Colors.white)),
                                                ),
                                                const SizedBox(width: 8),
                                                Expanded(
                                                  child: SelectableText(
                                                    item.licenseKeys.first,
                                                    style: const TextStyle(
                                                      fontFamily: 'monospace',
                                                      fontWeight: FontWeight.w900,
                                                      fontSize: 13,
                                                      color: _primaryBlue,
                                                      letterSpacing: 1.0,
                                                    ),
                                                  ),
                                                ),
                                                IconButton(
                                                  visualDensity: VisualDensity.compact,
                                                  padding: EdgeInsets.zero,
                                                  constraints: const BoxConstraints(),
                                                  icon: const Icon(Icons.copy_rounded, size: 16, color: _primaryBlue),
                                                  tooltip: 'Salin lisensi #1',
                                                  onPressed: () => _copyToClipboard(item.licenseKeys.first, 'Lisensi #1'),
                                                ),
                                              ],
                                            ),
                                          ),
                                          const SizedBox(height: 10),
                                          OutlinedButton.icon(
                                            onPressed: () => _showAllLicensesModal(item),
                                            style: OutlinedButton.styleFrom(
                                              foregroundColor: _primaryBlue,
                                              side: const BorderSide(color: _primaryBlue, width: 1.2),
                                              backgroundColor: Colors.white,
                                              padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
                                              minimumSize: const Size.fromHeight(38),
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                            ),
                                            icon: const Icon(Icons.list_alt_rounded, size: 16),
                                            label: Text(
                                              'Lihat Semua Lisensi (${item.licenseKeys.length})',
                                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                ],
                                if (order.status == 'completed') ...[
                                  const SizedBox(height: 8),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.end,
                                    children: [
                                      if (item.isReviewed)
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFFECFDF5),
                                            borderRadius: BorderRadius.circular(8),
                                            border: Border.all(color: const Color(0xFFA7F3D0)),
                                          ),
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              const Icon(Icons.star_rounded, size: 15, color: Color(0xFFF59E0B)),
                                              const SizedBox(width: 4),
                                              Text(
                                                'Rating: ${item.review?.rating ?? 5} ⭐',
                                                style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: Color(0xFF059669)),
                                              ),
                                              const SizedBox(width: 8),
                                              InkWell(
                                                onTap: () => _showReviewModal(item),
                                                child: const Text(
                                                  'Ubah',
                                                  style: TextStyle(
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.w800,
                                                    color: _primaryBlue,
                                                    decoration: TextDecoration.underline,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                        )
                                      else
                                        InkWell(
                                          onTap: () => _showReviewModal(item),
                                          borderRadius: BorderRadius.circular(8),
                                          child: Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFFEF3C7),
                                              borderRadius: BorderRadius.circular(8),
                                              border: Border.all(color: const Color(0xFFFCD34D)),
                                            ),
                                            child: const Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Icon(Icons.star_rate_rounded, size: 15, color: Color(0xFFD97706)),
                                                SizedBox(width: 5),
                                                Text(
                                                  'Beri Ulasan Produk',
                                                  style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Color(0xFFB45309)),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),
                                    ],
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ],
                        const SizedBox(height: 10),
                        const Divider(height: 1, color: _borderLight),
                        const SizedBox(height: 10),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Total Pembayaran', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: _textDark)),
                            Text(
                              formatRupiah(order.total),
                              style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: _primaryBlue),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 14),

                  // Shipping / Project Location Info
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: _borderLight),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          order.isDigitalOrder
                              ? 'Informasi Pemesan & Lisensi Digital'
                              : (order.isServiceOrder
                                  ? 'Informasi Klien & Lokasi Proyek'
                                  : 'Informasi Pemesan & Alamat Pengiriman'),
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: _textDark),
                        ),
                        const SizedBox(height: 10),
                        _buildInfoRow(
                          order.isDigitalOrder
                              ? 'Nama Pemesan / PIC'
                              : (order.isServiceOrder ? 'Nama Klien / Instansi' : 'Nama Penerima'),
                          order.recipientName,
                        ),
                        _buildInfoRow('No. Telepon / WhatsApp', order.recipientPhone),
                        _buildInfoRow(
                          order.isDigitalOrder
                              ? 'Tujuan Lisensi / Akun'
                              : (order.isServiceOrder ? 'Lokasi Pemasangan / Proyek' : 'Alamat Pengiriman'),
                          order.shippingAddress,
                        ),
                        if (order.trackingNumber != null)
                          Padding(
                            padding: const EdgeInsets.only(top: 8.0),
                            child: Row(
                              children: [
                                const Icon(Icons.local_shipping_outlined, color: _primaryBlue, size: 18),
                                const SizedBox(width: 6),
                                const Text('No. Resi: ', style: TextStyle(fontSize: 12, color: _textMuted)),
                                Expanded(
                                  child: Text(
                                    order.trackingNumber!,
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: _primaryBlue),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        Builder(
                          builder: (context) {
                            final cleanNotes = order.notes != null
                                ? order.notes!.replaceAll(RegExp(r'^\[(CC|MB|EW)\][^\n\|]*(\|)?\s*'), '').trim()
                                : '';
                            if (cleanNotes.isNotEmpty) {
                              return _buildInfoRow('Catatan Tambahan', cleanNotes);
                            }
                            return const SizedBox.shrink();
                          },
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 14),

                  // User Actions (Pending state)
                  if (order.status == 'pending')
                    Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: OutlinedButton.icon(
                        onPressed: _isBusy ? null : _cancelOrder,
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFFDC2626),
                          side: const BorderSide(color: Color(0xFFFCA5A5)),
                          backgroundColor: const Color(0xFFFEF2F2),
                          minimumSize: const Size.fromHeight(46),
                        ),
                        icon: const Icon(Icons.cancel_outlined),
                        label: const Text('Batalkan Pesanan Ini', style: TextStyle(fontWeight: FontWeight.w700)),
                      ),
                    ),

                  // Complete Digital Order Button
                  if (order.isDigitalOrder && order.status == 'paid')
                    FilledButton.icon(
                      onPressed: _isBusy ? null : () => _runAction(() => ApiService.completeDigitalOrder(order.orderNumber), 'Pesanan digital berhasil diselesaikan!'),
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF059669),
                        foregroundColor: Colors.white,
                        minimumSize: const Size.fromHeight(46),
                      ),
                      icon: const Icon(Icons.task_alt_rounded),
                      label: const Text('Selesaikan Pesanan', style: TextStyle(fontWeight: FontWeight.w800)),
                    ),

                  // Complete Service Order Button
                  if (order.isServiceOrder && order.status == 'processing')
                    FilledButton.icon(
                      onPressed: _isBusy ? null : () => _runAction(() => ApiService.completeServiceOrder(order.orderNumber), 'Pesanan jasa berhasil diselesaikan!'),
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF059669),
                        foregroundColor: Colors.white,
                        minimumSize: const Size.fromHeight(46),
                      ),
                      icon: const Icon(Icons.task_alt_rounded),
                      label: const Text('Selesaikan Pesanan', style: TextStyle(fontWeight: FontWeight.w800)),
                    ),

                  // Chat Admin & Helpdesk Action Card
                  Container(
                    margin: const EdgeInsets.only(top: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFBFDBFE)),
                    ),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        borderRadius: BorderRadius.circular(16),
                        onTap: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => OrderChatScreen(
                                orderNumber: order.orderNumber,
                                orderStatusLabel: order.statusLabel,
                              ),
                            ),
                          );
                        },
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Row(
                            children: [
                              Container(
                                width: 44,
                                height: 44,
                                decoration: BoxDecoration(
                                  color: _primaryBlue,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: const Icon(Icons.chat_bubble_rounded, color: Colors.white, size: 22),
                              ),
                              const SizedBox(width: 14),
                              const Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Chat Admin / Teknisi Pesanan',
                                      style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: _textDark),
                                    ),
                                    SizedBox(height: 3),
                                    Text(
                                      'Tanya jadwal kirim, konfirmasi resi, atau kedatangan teknisi',
                                      style: TextStyle(fontSize: 11, color: _textMuted),
                                    ),
                                  ],
                                ),
                              ),
                              const Icon(Icons.chevron_right_rounded, color: _primaryBlue, size: 22),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(height: 40),
                ],
              ),
            ),
    );
  }

  Widget _buildStatusTracker(String status) {
    int currentStep = switch (status) {
      'pending' => 1,
      'paid' => 2,
      'processing' => 3,
      'shipped' => 4,
      'delivered' => 5,
      'completed' => 6,
      'cancelled' => -1,
      _ => 1,
    };

    if (currentStep == -1) {
      return Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: const Color(0xFFFEF2F2),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: const Color(0xFFFECACA)),
        ),
        child: const Row(
          children: [
            Icon(Icons.error_outline, color: Color(0xFFDC2626)),
            SizedBox(width: 10),
            Text('Pesanan ini telah dibatalkan.', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFFDC2626))),
          ],
        ),
      );
    }

    final steps = [
      {'title': 'Dibuat', 'icon': Icons.description_outlined},
      {'title': 'Dibayar', 'icon': Icons.payment_outlined},
      {'title': 'Diproses', 'icon': Icons.build_outlined},
      {'title': 'Dikirim', 'icon': Icons.local_shipping_outlined},
      {'title': 'Diterima', 'icon': Icons.inventory_2_outlined},
      {'title': 'Selesai', 'icon': Icons.task_alt_rounded},
    ];

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _borderLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Status Pengerjaan & Pengiriman', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: _textDark)),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              for (int i = 0; i < steps.length; i++) ...[
                Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: i + 1 <= currentStep ? _primaryBlue : const Color(0xFFF3F4F6),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        steps[i]['icon'] as IconData,
                        size: 18,
                        color: i + 1 <= currentStep ? Colors.white : const Color(0xFF9CA3AF),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      steps[i]['title'] as String,
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: i + 1 <= currentStep ? FontWeight.w800 : FontWeight.w500,
                        color: i + 1 <= currentStep ? _primaryBlue : _textMuted,
                      ),
                    ),
                  ],
                ),
                if (i < steps.length - 1)
                  Expanded(
                    child: Container(
                      height: 2,
                      margin: const EdgeInsets.only(bottom: 16),
                      color: i + 1 < currentStep ? _primaryBlue : _borderLight,
                    ),
                  ),
              ],
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildDigitalStatusTracker(String status) {
    int currentStep = switch (status) {
      'pending' => 1,
      'paid' => 2,
      'completed' => 3,
      _ => 1,
    };

    final steps = [
      {'title': 'Dibuat', 'icon': Icons.description_outlined},
      {'title': 'Dibayar', 'icon': Icons.payment_outlined},
      {'title': 'Selesai', 'icon': Icons.task_alt_rounded},
    ];

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _borderLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Status Pemesanan (Digital)', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: _textDark)),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              for (int i = 0; i < steps.length; i++) ...[
                Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: i + 1 <= currentStep ? _primaryBlue : const Color(0xFFF3F4F6),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        steps[i]['icon'] as IconData,
                        size: 18,
                        color: i + 1 <= currentStep ? Colors.white : const Color(0xFF9CA3AF),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      steps[i]['title'] as String,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: i + 1 <= currentStep ? FontWeight.w800 : FontWeight.w500,
                        color: i + 1 <= currentStep ? _primaryBlue : _textMuted,
                      ),
                    ),
                  ],
                ),
                if (i < steps.length - 1)
                  Expanded(
                    child: Container(
                      height: 2,
                      margin: const EdgeInsets.only(bottom: 16),
                      color: i + 1 < currentStep ? _primaryBlue : _borderLight,
                    ),
                  ),
              ],
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildServiceStatusTracker(String status) {
    int currentStep = switch (status) {
      'pending' => 1,
      'paid' => 2,
      'processing' => 3,
      'completed' => 4,
      _ => 1,
    };

    final steps = [
      {'title': 'Dibuat', 'icon': Icons.description_outlined},
      {'title': 'Dibayar', 'icon': Icons.payment_outlined},
      {'title': 'Dikerjakan', 'icon': Icons.build_outlined},
      {'title': 'Selesai', 'icon': Icons.task_alt_rounded},
    ];

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _borderLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Status Pengerjaan (Jasa)', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: _textDark)),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              for (int i = 0; i < steps.length; i++) ...[
                Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: i + 1 <= currentStep ? _primaryBlue : const Color(0xFFF3F4F6),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        steps[i]['icon'] as IconData,
                        size: 18,
                        color: i + 1 <= currentStep ? Colors.white : const Color(0xFF9CA3AF),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      steps[i]['title'] as String,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: i + 1 <= currentStep ? FontWeight.w800 : FontWeight.w500,
                        color: i + 1 <= currentStep ? _primaryBlue : _textMuted,
                      ),
                    ),
                  ],
                ),
                if (i < steps.length - 1)
                  Expanded(
                    child: Container(
                      height: 2,
                      margin: const EdgeInsets.only(bottom: 16),
                      color: i + 1 < currentStep ? _primaryBlue : _borderLight,
                    ),
                  ),
              ],
            ],
          ),
        ],
      ),
    );
  }

  String _getCourierName(String trackingNo) {
    final upper = trackingNo.toUpperCase();
    if (upper.startsWith('J&T') || upper.startsWith('JNT')) {
      return 'J&T Express (EZ)';
    } else if (upper.startsWith('SICEPAT')) {
      return 'SiCepat Ekspres (REG)';
    } else if (upper.startsWith('ANTERAJA')) {
      return 'AnterAja (Regular)';
    } else if (upper.startsWith('TIKI')) {
      return 'TIKI (Reguler Service)';
    } else if (upper.startsWith('POS')) {
      return 'Pos Indonesia (Kilat Khusus)';
    } else {
      return 'JNE Express (Reguler)';
    }
  }

  String _getCourierShort(String trackingNo) {
    final upper = trackingNo.toUpperCase();
    if (upper.startsWith('J&T') || upper.startsWith('JNT')) {
      return 'J&T';
    } else if (upper.startsWith('SICEPAT')) {
      return 'SiCepat';
    } else if (upper.startsWith('ANTERAJA')) {
      return 'AnterAja';
    } else if (upper.startsWith('TIKI')) {
      return 'TIKI';
    } else if (upper.startsWith('POS')) {
      return 'Pos Indonesia';
    } else {
      return 'JNE';
    }
  }

  Widget _buildHardwareTrackingCard(Order order) {
    final trackingNo = order.trackingNumber ?? '';
    final hasTracking = trackingNo.isNotEmpty;
    final isShippedOrDelivered = order.status == 'shipped' || order.status == 'delivered';
    final isCompleted = order.status == 'completed';
    final courierName = _getCourierName(trackingNo);
    final courierShort = _getCourierShort(trackingNo);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isShippedOrDelivered ? const Color(0xFF93C5FD) : _borderLight,
          width: isShippedOrDelivered ? 1.4 : 1.0,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: const Icon(Icons.local_shipping_rounded, color: _primaryBlue, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Pengiriman & Pelacakan Hardware',
                      style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5, color: _textDark),
                    ),
                    Text(
                      'Ekspedisi $courierName',
                      style: const TextStyle(fontSize: 11, color: _textMuted),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: isCompleted ? const Color(0xFFECFDF5) : const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: isCompleted ? const Color(0xFFA7F3D0) : const Color(0xFFBFDBFE),
                  ),
                ),
                child: Text(
                  isCompleted
                      ? 'Diterima'
                      : (isShippedOrDelivered ? 'Dalam Perjalanan' : (order.status == 'processing' ? 'Dikemas' : 'Menunggu')),
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w800,
                    color: isCompleted ? const Color(0xFF059669) : _primaryBlue,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Resi Display Box
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _borderLight),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'NOMOR RESI PENGIRIMAN',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                          color: _textMuted,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 3),
                      SelectableText(
                        hasTracking ? trackingNo : 'Menunggu Pengiriman',
                        style: TextStyle(
                          fontFamily: 'monospace',
                          fontWeight: FontWeight.w900,
                          fontSize: 14,
                          color: hasTracking ? _primaryBlue : _textMuted,
                          letterSpacing: 1.0,
                        ),
                      ),
                    ],
                  ),
                ),
                if (hasTracking)
                  FilledButton.icon(
                    onPressed: () => _copyToClipboard(order.trackingNumber!, 'Nomor Resi'),
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFFEFF6FF),
                      foregroundColor: _primaryBlue,
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                        side: const BorderSide(color: Color(0xFFBFDBFE)),
                      ),
                    ),
                    icon: const Icon(Icons.copy_rounded, size: 14),
                    label: const Text('Salin Resi', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                  ),
              ],
            ),
          ),

          if (hasTracking || isShippedOrDelivered || isCompleted) ...[
            const SizedBox(height: 14),
            InkWell(
              onTap: () => setState(() => _isTrackingExpanded = !_isTrackingExpanded),
              borderRadius: BorderRadius.circular(8),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.route_outlined, size: 16, color: _primaryBlue),
                        SizedBox(width: 6),
                        Text(
                          'Linimasa Lacak Ekspedisi',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: _textDark),
                        ),
                      ],
                    ),
                    Icon(
                      _isTrackingExpanded ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                      size: 18,
                      color: _textMuted,
                    ),
                  ],
                ),
              ),
            ),

            if (_isTrackingExpanded) ...[
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: _borderLight),
                ),
                child: Column(
                  children: [
                    _buildTrackingStep(
                      isPassed: true,
                      isLatest: !isCompleted && isShippedOrDelivered,
                      title: isCompleted ? 'Paket Telah Sampai & Diterima' : 'Pesanan Dalam Perjalanan',
                      description: isCompleted
                          ? 'Paket telah diterima di alamat ${order.recipientName}.'
                          : 'Kurir $courierShort sedang membawa paket menuju alamat tujuan pengiriman.',
                      time: isCompleted
                          ? (order.completedAt != null ? formatDateTime(order.completedAt!) : 'Hari ini')
                          : 'Hari ini, sedang diantar',
                    ),
                    _buildTrackingStep(
                      isPassed: true,
                      isLatest: false,
                      title: 'Tiba di Fasilitas Sortir Kota Tujuan',
                      description: 'Paket telah tiba di gudang hub logistik $courierShort dan siap diserahkan ke kurir.',
                      time: 'Kemarin, 21:40 WIB',
                    ),
                    _buildTrackingStep(
                      isPassed: true,
                      isLatest: false,
                      title: 'Diberangkatkan dari Pusat Logistik $courierShort',
                      description: 'Paket diberangkatkan menuju kota destinasi pengiriman oleh armada $courierShort.',
                      time: 'Kemarin, 14:15 WIB',
                    ),
                    _buildTrackingStep(
                      isPassed: true,
                      isLatest: false,
                      isLast: true,
                      title: 'Paket Diserahkan oleh Penjual (IT Selling)',
                      description: 'Nomor resi ${order.trackingNumber ?? ''} diterbitkan dan paket masuk drop point $courierShort.',
                      time: order.paidAt != null ? formatDateTime(order.paidAt!) : formatDateTime(order.createdAt),
                    ),
                  ],
                ),
              ),
            ],
          ],

          if (isShippedOrDelivered && !isCompleted) ...[
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.help_outline_rounded, color: _primaryBlue, size: 18),
                      SizedBox(width: 8),
                      Text(
                        'Sudah terima pesanan?',
                        style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: _textDark),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Klik tombol di bawah jika paket hardware sudah sampai di tangan Anda dan berfungsi dengan normal.',
                    style: TextStyle(fontSize: 11.5, color: _textMuted),
                  ),
                  const SizedBox(height: 12),
                  FilledButton.icon(
                    onPressed: _isBusy ? null : _confirmReceiveHardwareOrder,
                    style: FilledButton.styleFrom(
                      backgroundColor: _primaryBlue,
                      foregroundColor: Colors.white,
                      minimumSize: const Size.fromHeight(42),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      textStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                    ),
                    icon: const Icon(Icons.check_circle_outline_rounded, size: 17),
                    label: const Text('Ya, Sudah Terima Pesanan'),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildTrackingStep({
    required bool isPassed,
    required bool isLatest,
    required String title,
    required String description,
    required String time,
    bool isLast = false,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Container(
              width: 14,
              height: 14,
              decoration: BoxDecoration(
                color: isLatest ? _primaryBlue : (isPassed ? const Color(0xFFDBEAFE) : const Color(0xFFE5E7EB)),
                shape: BoxShape.circle,
                border: Border.all(
                  color: isLatest ? _primaryBlue : (isPassed ? const Color(0xFF93C5FD) : _borderLight),
                  width: 2,
                ),
              ),
            ),
            if (!isLast)
              Container(
                width: 2,
                height: 38,
                color: isPassed ? const Color(0xFF93C5FD) : _borderLight,
              ),
          ],
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Text(
                        title,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: isLatest ? FontWeight.w800 : FontWeight.w700,
                          color: isLatest ? _primaryBlue : _textDark,
                        ),
                      ),
                    ),
                    Text(
                      time,
                      style: const TextStyle(fontSize: 10, color: _textMuted),
                    ),
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  description,
                  style: const TextStyle(fontSize: 11, color: _textMuted),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontSize: 11, color: _textMuted)),
          const SizedBox(height: 1),
          Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: _textDark)),
        ],
      ),
    );
  }
}
