import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/formatters.dart';
import '../models/models.dart';
import '../providers/auth_provider.dart';
import '../screens/order_detail_screen.dart';
import '../services/api_service.dart';

class NotificationsSheet extends StatefulWidget {
  const NotificationsSheet({super.key, this.onAllRead});

  final VoidCallback? onAllRead;

  static Future<void> show(BuildContext context, {VoidCallback? onAllRead}) {
    return showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => NotificationsSheet(onAllRead: onAllRead),
    );
  }

  @override
  State<NotificationsSheet> createState() => _NotificationsSheetState();
}

class _NotificationsSheetState extends State<NotificationsSheet> {
  bool _isLoading = true;
  bool _markedAllRead = false;
  List<Order> _recentOrders = [];

  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    final auth = context.read<AuthProvider>();
    if (!auth.isAuthenticated) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
      return;
    }

    try {
      final orders = await ApiService.fetchOrders(page: 1);
      if (mounted) {
        setState(() {
          _recentOrders = orders.items.take(6).toList();
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _markAllRead() {
    setState(() {
      _markedAllRead = true;
    });
    widget.onAllRead?.call();
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        behavior: SnackBarBehavior.floating,
        content: Text('Semua notifikasi telah ditandai sudah dibaca.'),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final unreadCount = _markedAllRead ? 0 : (_recentOrders.length + 2);

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.82,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const SizedBox(height: 12),
          Container(
            width: 44,
            height: 4,
            decoration: BoxDecoration(
              color: const Color(0xFFD1D5DB),
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 14),

          // Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFBFDBFE)),
                  ),
                  child: const Icon(Icons.notifications_active_rounded, color: _primaryBlue, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Text(
                            'Pusat Notifikasi',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w800,
                              color: _textDark,
                            ),
                          ),
                          const SizedBox(width: 8),
                          if (unreadCount > 0)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEFF6FF),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: const Color(0xFFBFDBFE)),
                              ),
                              child: Text(
                                '$unreadCount Baru',
                                style: const TextStyle(
                                  color: _primaryBlue,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      const Text(
                        'Pembaruan pesanan, pembayaran & sistem IT Selling',
                        style: TextStyle(fontSize: 11, color: _textMuted),
                      ),
                    ],
                  ),
                ),
                if (unreadCount > 0)
                  TextButton(
                    onPressed: _markAllRead,
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    child: const Text(
                      'Tandai dibaca',
                      style: TextStyle(
                        color: _primaryBlue,
                        fontWeight: FontWeight.w700,
                        fontSize: 12,
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          const Divider(height: 1, color: _borderLight),

          // Content List
          Flexible(
            child: _isLoading
                ? const Center(
                    child: Padding(
                      padding: EdgeInsets.all(40),
                      child: CircularProgressIndicator(color: _primaryBlue),
                    ),
                  )
                : ListView(
                    shrinkWrap: true,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    children: [
                      if (_recentOrders.isNotEmpty) ...[
                        Padding(
                          padding: const EdgeInsets.only(left: 4, bottom: 8, top: 4),
                          child: Text(
                            'PEMBARUAN PESANAN SAYA (${_recentOrders.length})',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                              color: _textMuted,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ),
                        ..._recentOrders.map((order) {
                          final isUnread = !_markedAllRead;
                          return _buildOrderNotificationItem(order, isUnread);
                        }),
                        const SizedBox(height: 12),
                      ],

                      const Padding(
                        padding: EdgeInsets.only(left: 4, bottom: 8, top: 4),
                        child: Text(
                          'PEMBERITAHUAN SISTEM',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            color: _textMuted,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ),
                      _buildSystemNotificationItem(
                        icon: Icons.rocket_launch_rounded,
                        iconColor: _primaryBlue,
                        title: 'Sistem IT Selling v1.2 Aktif',
                        message:
                            'Fitur pelacakan resi instan ekspedisi (JNE/J&T/SiCepat) & integrasi katalog hardware aktif.',
                        time: 'Hari ini',
                        isUnread: !_markedAllRead,
                      ),
                      const SizedBox(height: 8),
                      _buildSystemNotificationItem(
                        icon: Icons.verified_user_rounded,
                        iconColor: const Color(0xFF0284C7),
                        title: 'Garansi Resmi & Teknisi Bersertifikat',
                        message:
                            'Seluruh unit hardware dan pengiriman dilindungi asuransi serta dukungan perakitan teknisi kami.',
                        time: '1 hari lalu',
                        isUnread: false,
                      ),
                      const SizedBox(height: 8),
                      _buildSystemNotificationItem(
                        icon: Icons.headset_mic_rounded,
                        iconColor: const Color(0xFF7C3AED),
                        title: 'Helpdesk & Konsultasi IT Siap Membantu',
                        message:
                            'Butuh instalasi server, rak server, atau konfigurasi Mikrotik/Cisco? Hubungi helpdesk kami.',
                        time: '2 hari lalu',
                        isUnread: false,
                      ),
                      const SizedBox(height: 24),
                    ],
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildOrderNotificationItem(Order order, bool isUnread) {
    IconData icon;
    Color iconColor;
    Color iconBg;

    if (order.status == 'completed') {
      icon = Icons.check_circle_rounded;
      iconColor = const Color(0xFF059669);
      iconBg = const Color(0xFFD1FAE5);
    } else if (order.status == 'shipped' || order.status == 'processing') {
      icon = Icons.local_shipping_rounded;
      iconColor = _primaryBlue;
      iconBg = const Color(0xFFDBEAFE);
    } else if (order.status == 'paid') {
      icon = Icons.verified_rounded;
      iconColor = const Color(0xFF0284C7);
      iconBg = const Color(0xFFE0F2FE);
    } else {
      icon = Icons.receipt_long_rounded;
      iconColor = const Color(0xFFD97706);
      iconBg = const Color(0xFFFEF3C7);
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: isUnread ? const Color(0xFFEFF6FF) : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isUnread ? const Color(0xFFBFDBFE) : _borderLight,
          width: 1,
        ),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: () {
            Navigator.of(context).pop();
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => OrderDetailScreen(orderNumber: order.orderNumber),
              ),
            );
          },
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: iconBg,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(icon, color: iconColor, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              'Pesanan #${order.orderNumber}',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w800,
                                color: _textDark,
                              ),
                            ),
                          ),
                          if (isUnread)
                            Container(
                              width: 7,
                              height: 7,
                              decoration: const BoxDecoration(
                                color: _primaryBlue,
                                shape: BoxShape.circle,
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'Status: ${order.statusLabel} • ${formatRupiah(order.total)}',
                        style: const TextStyle(
                          fontSize: 12,
                          color: _textMuted,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      if (order.trackingNumber != null && order.trackingNumber!.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(Icons.qr_code_2_rounded, size: 13, color: _primaryBlue),
                            const SizedBox(width: 4),
                            Text(
                              'Resi: ${order.trackingNumber}',
                              style: const TextStyle(
                                fontSize: 11,
                                color: _primaryBlue,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                const Icon(Icons.chevron_right_rounded, color: Color(0xFF9CA3AF), size: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSystemNotificationItem({
    required IconData icon,
    required Color iconColor,
    required String title,
    required String message,
    required String time,
    required bool isUnread,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: isUnread ? const Color(0xFFEFF6FF) : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isUnread ? const Color(0xFFBFDBFE) : _borderLight,
          width: 1,
        ),
      ),
      padding: const EdgeInsets.all(12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: iconColor.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: iconColor, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        title,
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                          color: _textDark,
                        ),
                      ),
                    ),
                    Text(
                      time,
                      style: const TextStyle(fontSize: 10, color: _textMuted),
                    ),
                  ],
                ),
                const SizedBox(height: 3),
                Text(
                  message,
                  style: const TextStyle(
                    fontSize: 11,
                    height: 1.4,
                    color: _textMuted,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
