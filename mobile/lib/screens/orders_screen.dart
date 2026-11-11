import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../core/formatters.dart';
import '../models/models.dart';
import '../services/api_service.dart';
import '../widgets/error_banner.dart';
import '../widgets/status_chip.dart';
import 'order_detail_screen.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => OrdersScreenState();
}

class OrdersScreenState extends State<OrdersScreen> {
  final List<Order> _orders = [];
  int _page = 0;
  bool _hasMore = true;
  bool _isLoading = false;
  String? _error;

  static const Color _bgPage = Color(0xFFF3F6FC);
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  @override
  void initState() {
    super.initState();
    reload();
  }

  /// Memuat ulang riwayat pesanan dari halaman pertama.
  Future<void> reload() async {
    if (_isLoading) {
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final result = await ApiService.fetchOrders();
      if (!mounted) {
        return;
      }

      setState(() {
        _orders
          ..clear()
          ..addAll(result.items);
        _page = result.currentPage;
        _hasMore = result.hasMore;
      });
    } on ApiException catch (error) {
      if (mounted) {
        setState(() => _error = error.message);
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _loadMore() async {
    if (_isLoading || !_hasMore) {
      return;
    }

    setState(() => _isLoading = true);

    try {
      final result = await ApiService.fetchOrders(page: _page + 1);
      if (mounted) {
        setState(() {
          _orders.addAll(result.items);
          _page = result.currentPage;
          _hasMore = result.hasMore;
        });
      }
    } on ApiException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
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
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _openOrder(Order order) async {
    await Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => OrderDetailScreen(orderNumber: order.orderNumber)),
    );
    reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _bgPage,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: _textDark,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 1,
        shadowColor: _primaryBlue.withValues(alpha: 0.08),
        title: Row(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: const Icon(Icons.receipt_long_rounded, color: _primaryBlue, size: 18),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Pesanan & Layanan',
                  style: TextStyle(color: _textDark, fontSize: 16, fontWeight: FontWeight.w800),
                ),
                Text(
                  _orders.isEmpty ? 'Riwayat transaksi pengadaan IT' : '${_orders.length} transaksi tercatat',
                  style: const TextStyle(color: _textMuted, fontSize: 11, fontWeight: FontWeight.w500),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: _primaryBlue),
            tooltip: 'Segarkan',
            onPressed: reload,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: reload,
        color: _primaryBlue,
        backgroundColor: Colors.white,
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_orders.isEmpty) {
      final Widget content;
      if (_isLoading) {
        content = const Center(child: CircularProgressIndicator(color: _primaryBlue));
      } else if (_error != null) {
        content = ErrorView(message: _error!, onRetry: reload);
      } else {
        content = Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 88,
                height: 88,
                decoration: BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  border: Border.all(color: const Color(0xFFDBEAFE), width: 2),
                  boxShadow: [
                    BoxShadow(
                      color: _primaryBlue.withValues(alpha: 0.12),
                      blurRadius: 24,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: const Icon(Icons.receipt_long_outlined, size: 40, color: _primaryBlue),
              ),
              const SizedBox(height: 18),
              const Text(
                'Belum Ada Pesanan',
                style: TextStyle(color: _textDark, fontSize: 18, fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 6),
              const Text(
                'Riwayat transaksi pengadaan IT Anda akan tampil di sini.',
                style: TextStyle(color: _textMuted, fontSize: 13),
              ),
            ],
          ),
        );
      }

      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [SizedBox(height: 420, child: content)],
      );
    }

    return ListView.separated(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 24),
      itemCount: _orders.length + (_hasMore ? 1 : 0),
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        if (index == _orders.length) {
          return Center(
            child: _isLoading
                ? const Padding(
                    padding: EdgeInsets.all(16),
                    child: CircularProgressIndicator(color: _primaryBlue),
                  )
                : TextButton(
                    onPressed: _loadMore,
                    child: const Text('Muat lebih banyak', style: TextStyle(color: _primaryBlue, fontWeight: FontWeight.w700)),
                  ),
          );
        }

        final order = _orders[index];

        return Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: _borderLight),
            boxShadow: [
              BoxShadow(
                color: _primaryBlue.withValues(alpha: 0.05),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              borderRadius: BorderRadius.circular(16),
              onTap: () => _openOrder(order),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(9),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEFF6FF),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: const Color(0xFFBFDBFE)),
                          ),
                          child: const Icon(Icons.inventory_2_outlined, size: 18, color: _primaryBlue),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                order.orderNumber,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w800,
                                  fontSize: 14,
                                  color: _textDark,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                formatDateTime(order.createdAt),
                                style: const TextStyle(color: _textMuted, fontSize: 11.5, fontWeight: FontWeight.w500),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        StatusChip(status: order.status, label: order.statusLabel),
                      ],
                    ),
                    if (order.isHardwareOrder &&
                        order.trackingNumber != null &&
                        (order.status == 'shipped' || order.status == 'delivered' || order.status == 'completed')) ...[
                      const SizedBox(height: 10),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: const Color(0xFFBFDBFE)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.local_shipping_outlined, size: 14, color: _primaryBlue),
                            const SizedBox(width: 6),
                            Text(
                              'Resi: ${order.trackingNumber}',
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: _primaryBlue),
                            ),
                          ],
                        ),
                      ),
                    ],
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 12),
                      child: Divider(height: 1, color: _borderLight),
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          '${order.itemsCount ?? order.items.length} item pengadaan',
                          style: const TextStyle(color: _textMuted, fontSize: 12.5, fontWeight: FontWeight.w500),
                        ),
                        Row(
                          children: [
                            Text(
                              formatRupiah(order.total),
                              style: const TextStyle(
                                fontWeight: FontWeight.w900,
                                fontSize: 15,
                                color: _primaryBlue,
                              ),
                            ),
                            const SizedBox(width: 6),
                            const Icon(Icons.chevron_right_rounded, size: 18, color: Color(0xFF9CA3AF)),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}
