import 'dart:async';
import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../models/models.dart';
import '../services/api_service.dart';
import '../widgets/product_image.dart';
import 'product_chat_screen.dart';

class UserChatListScreen extends StatefulWidget {
  const UserChatListScreen({super.key});

  @override
  State<UserChatListScreen> createState() => _UserChatListScreenState();
}

class _UserChatListScreenState extends State<UserChatListScreen> {
  final List<UserInquiry> _inquiries = [];
  bool _isLoading = true;
  String? _error;
  Timer? _pollingTimer;

  static const Color _bgPage = Color(0xFFF3F6FC);
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  @override
  void initState() {
    super.initState();
    _loadInquiries(initial: true);

    // Auto-polling setiap 2.5 detik untuk sinkronisasi realtime tanpa perlu refresh manual
    _pollingTimer = Timer.periodic(const Duration(milliseconds: 2500), (_) {
      if (mounted) {
        _loadInquiries(silent: true);
      }
    });
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadInquiries({bool initial = false, bool silent = false}) async {
    if (initial) {
      setState(() {
        _isLoading = true;
        _error = null;
      });
    }

    try {
      final items = await ApiService.fetchUserInquiries();
      if (!mounted) return;

      setState(() {
        _inquiries
          ..clear()
          ..addAll(items);
        _isLoading = false;
        _error = null;
      });
    } on ApiException catch (e) {
      if (mounted && !silent) {
        setState(() {
          _error = e.message;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted && !silent) {
        setState(() {
          _error = 'Gagal memuat daftar chat: $e';
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _bgPage,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        shadowColor: _primaryBlue.withValues(alpha: 0.08),
        iconTheme: const IconThemeData(color: _textDark),
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Pusat Konsultasi Produk',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
                color: _textDark,
              ),
            ),
            SizedBox(height: 2),
            Row(
              children: [
                SizedBox(
                  width: 7,
                  height: 7,
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      color: Color(0xFF10B981),
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
                SizedBox(width: 5),
                Text(
                  'Realtime Live Chat & Tanya Admin',
                  style: TextStyle(
                    fontSize: 10.5,
                    color: _primaryBlue,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Segarkan',
            icon: const Icon(Icons.refresh_rounded, color: _textMuted),
            onPressed: () => _loadInquiries(initial: true),
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          color: _primaryBlue,
        ),
      );
    }

    if (_error != null && _inquiries.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline_rounded, size: 48, color: Color(0xFFEF4444)),
              const SizedBox(height: 12),
              Text(
                _error!,
                textAlign: TextAlign.center,
                style: const TextStyle(color: _textMuted, fontSize: 13),
              ),
              const SizedBox(height: 16),
              FilledButton.icon(
                onPressed: () => _loadInquiries(initial: true),
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: const Text('Coba Lagi'),
                style: FilledButton.styleFrom(
                  backgroundColor: _primaryBlue,
                  foregroundColor: Colors.white,
                  textStyle: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (_inquiries.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  shape: BoxShape.circle,
                  border: Border.all(color: const Color(0xFFBFDBFE), width: 1.5),
                ),
                child: const Icon(
                  Icons.chat_bubble_outline_rounded,
                  size: 38,
                  color: _primaryBlue,
                ),
              ),
              const SizedBox(height: 20),
              const Text(
                'Belum Ada Diskusi Produk',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: _textDark),
              ),
              const SizedBox(height: 8),
              const Text(
                'Punya pertanyaan seputar spesifikasi teknis, lisensi, garansi, atau ketersediaan stok? Buka katalog produk lalu pilih "Tanya Admin".',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 12.5, color: _textMuted, height: 1.5),
              ),
              const SizedBox(height: 24),
              FilledButton.icon(
                onPressed: () => Navigator.of(context).pop(),
                icon: const Icon(Icons.storefront_rounded, size: 18),
                label: const Text('Jelajahi Katalog'),
                style: FilledButton.styleFrom(
                  backgroundColor: _primaryBlue,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                ),
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      color: _primaryBlue,
      backgroundColor: Colors.white,
      onRefresh: () => _loadInquiries(silent: true),
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        itemCount: _inquiries.length,
        separatorBuilder: (_, __) => const SizedBox(height: 12),
        itemBuilder: (context, index) {
          final inq = _inquiries[index];
          return _buildInquiryCard(inq);
        },
      ),
    );
  }

  Widget _buildInquiryCard(UserInquiry inq) {
    final product = inq.product;
    final latest = inq.latestMessage;
    final hasUnread = inq.unreadCount > 0;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: hasUnread ? _primaryBlue.withValues(alpha: 0.45) : _borderLight,
          width: hasUnread ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: hasUnread
                ? _primaryBlue.withValues(alpha: 0.08)
                : Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () async {
            await Navigator.of(context).push(
              MaterialPageRoute<void>(
                builder: (_) => ProductChatScreen(product: product),
              ),
            );
            // Refresh silent saat kembali dari chat untuk mengupdate read badge
            if (mounted) {
              _loadInquiries(silent: true);
            }
          },
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Product Thumbnail
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    width: 58,
                    height: 58,
                    color: const Color(0xFFF8FAFC),
                    child: ProductImage(
                      product: product,
                      fit: BoxFit.cover,
                    ),
                  ),
                ),
                const SizedBox(width: 12),

                // Main Details
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // SKU & Category tag
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEFF6FF),
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(color: const Color(0xFFBFDBFE)),
                            ),
                            child: Text(
                              product.sku,
                              style: const TextStyle(
                                fontSize: 10,
                                fontFamily: 'monospace',
                                fontWeight: FontWeight.w800,
                                color: _primaryBlue,
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              product.category?.name ?? 'Katalog IT',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w600,
                                color: _textMuted,
                              ),
                            ),
                          ),
                          if (latest != null)
                            Text(
                              latest.timeHuman,
                              style: TextStyle(
                                fontSize: 10,
                                color: hasUnread ? _primaryBlue : _textMuted,
                                fontWeight: hasUnread ? FontWeight.w800 : FontWeight.w500,
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 6),

                      // Product Title
                      Text(
                        product.name,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w800,
                          color: _textDark,
                        ),
                      ),
                      const SizedBox(height: 4),

                      // Latest message snippet
                      if (latest != null)
                        Row(
                          children: [
                            Icon(
                              latest.isMe ? Icons.done_all_rounded : Icons.support_agent_rounded,
                              size: 13,
                              color: latest.isMe ? _textMuted : _primaryBlue,
                            ),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                latest.isMe ? 'Anda: ${latest.message}' : 'Admin: ${latest.message}',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: hasUnread ? _textDark : _textMuted,
                                  fontWeight: hasUnread ? FontWeight.w700 : FontWeight.w500,
                                ),
                              ),
                            ),
                          ],
                        )
                      else
                        const Text(
                          'Ketuk untuk membuka ruang chat...',
                          style: TextStyle(fontSize: 11, color: _textMuted, fontStyle: FontStyle.italic),
                        ),
                    ],
                  ),
                ),

                // Unread Badge
                if (hasUnread) ...[
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                    decoration: BoxDecoration(
                      color: _primaryBlue,
                      borderRadius: BorderRadius.circular(10),
                      boxShadow: [
                        BoxShadow(
                          color: _primaryBlue.withValues(alpha: 0.3),
                          blurRadius: 6,
                        ),
                      ],
                    ),
                    child: Text(
                      '${inq.unreadCount}',
                      style: const TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
