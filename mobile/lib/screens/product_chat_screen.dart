import 'dart:async';
import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../core/formatters.dart';
import '../models/models.dart';
import '../services/api_service.dart';
import '../widgets/product_image.dart';

class ProductChatScreen extends StatefulWidget {
  const ProductChatScreen({
    super.key,
    required this.product,
  });

  final Product product;

  @override
  State<ProductChatScreen> createState() => _ProductChatScreenState();
}

class _ProductChatScreenState extends State<ProductChatScreen> {
  final _scrollController = ScrollController();
  final _textController = TextEditingController();
  final List<OrderMessage> _messages = [];
  bool _isLoading = true;
  bool _isSending = false;
  String? _error;
  Timer? _pollingTimer;

  static const Color _bgPage = Color(0xFFF3F6FC);
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  final List<String> _quickQuestions = [
    '📦 Apakah unit ini ready siap kirim?',
    '🛡️ Bagaimana garansi & keaslian lisensinya?',
    '📄 Apakah bisa terbit Faktur Pajak resmi kantor?',
    '🛠️ Apakah melayani survei / jasa instalasi langsung?',
    '⚡ Berapa estimasi pengerjaan / pengiriman?',
  ];

  @override
  void initState() {
    super.initState();
    _loadMessages(initial: true);
    // Polling setiap 2 detik saat layar chat aktif untuk realtime chat dengan admin
    _pollingTimer = Timer.periodic(const Duration(milliseconds: 2000), (_) {
      if (mounted && !_isSending) {
        _loadMessages(initial: false);
      }
    });
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _scrollController.dispose();
    _textController.dispose();
    super.dispose();
  }

  Future<void> _loadMessages({bool initial = false}) async {
    if (initial) {
      setState(() {
        _isLoading = true;
        _error = null;
      });
    }

    try {
      final messages = await ApiService.fetchProductMessages(widget.product.slug, silent: !initial);
      if (mounted) {
        final hadNewMessages = messages.length != _messages.length ||
            (messages.isNotEmpty && _messages.isNotEmpty && messages.last.id != _messages.last.id);
        setState(() {
          _messages.clear();
          _messages.addAll(messages);
          _isLoading = false;
          _error = null;
        });

        if (initial || hadNewMessages) {
          _scrollToBottom();
        }
      }
    } on ApiException catch (e) {
      if (mounted && initial) {
        setState(() {
          _error = e.message;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted && initial) {
        setState(() {
          _error = 'Gagal memuat pesan: $e';
          _isLoading = false;
        });
      }
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent + 80,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _sendMessage([String? presetText]) async {
    final text = (presetText ?? _textController.text).trim();
    if (text.isEmpty || _isSending) {
      return;
    }

    if (presetText == null) {
      _textController.clear();
    }

    setState(() => _isSending = true);

    try {
      final newMsg = await ApiService.sendProductMessage(widget.product.slug, text);
      if (mounted) {
        setState(() {
          _messages.add(newMsg);
          _isSending = false;
        });
        _scrollToBottom();
      }
    } on ApiException catch (e) {
      if (mounted) {
        setState(() => _isSending = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: const Color(0xFFEF4444),
            content: Text(e.message),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSending = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: const Color(0xFFEF4444),
            content: Text('Gagal mengirim pesan: $e'),
          ),
        );
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
        titleSpacing: 0,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(7),
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: const Icon(Icons.support_agent_rounded, color: _primaryBlue, size: 20),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Chat Tim Solusi IT',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: _textDark),
                  ),
                  Row(
                    children: [
                      Icon(Icons.circle, color: Color(0xFF10B981), size: 8),
                      SizedBox(width: 5),
                      Text(
                        'Online · PT Digital Solusi Cerdas',
                        style: TextStyle(fontSize: 11, color: _textMuted, fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      body: Column(
        children: [
          // Sticky Auto-detected Product Preview Card
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: const BoxDecoration(
              color: Colors.white,
              border: Border(bottom: BorderSide(color: _borderLight, width: 1.0)),
            ),
            child: Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    width: 52,
                    height: 52,
                    color: const Color(0xFFF8FAFC),
                    child: ProductImage(product: widget.product, iconSize: 24),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEFF6FF),
                              borderRadius: BorderRadius.circular(4),
                              border: Border.all(color: const Color(0xFFBFDBFE)),
                            ),
                            child: const Text(
                              'Menanyakan Produk Ini',
                              style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: _primaryBlue),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            widget.product.sku,
                            style: const TextStyle(fontSize: 10, color: _textMuted, fontFamily: 'monospace'),
                          ),
                        ],
                      ),
                      const SizedBox(height: 3),
                      Text(
                        widget.product.name,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: _textDark),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${formatRupiah(widget.product.price)} / ${widget.product.unit}',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: _primaryBlue),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Message Stream
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: _primaryBlue))
                : _error != null
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.error_outline_rounded, color: Color(0xFFEF4444), size: 36),
                              const SizedBox(height: 10),
                              Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: _textMuted)),
                              const SizedBox(height: 12),
                              FilledButton(
                                style: FilledButton.styleFrom(backgroundColor: _primaryBlue),
                                onPressed: () => _loadMessages(initial: true),
                                child: const Text('Coba Lagi'),
                              ),
                            ],
                          ),
                        ),
                      )
                    : _messages.isEmpty
                        ? Center(
                            child: Padding(
                              padding: const EdgeInsets.all(32),
                              child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(16),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFEFF6FF),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: const Color(0xFFBFDBFE)),
                                    ),
                                    child: const Icon(Icons.forum_outlined, size: 40, color: _primaryBlue),
                                  ),
                                  const SizedBox(height: 16),
                                  const Text(
                                    'Pusat Konsultasi Produk',
                                    style: TextStyle(color: _textDark, fontSize: 16, fontWeight: FontWeight.w800),
                                  ),
                                  const SizedBox(height: 6),
                                  Text(
                                    'Punya pertanyaan tentang ${widget.product.name}? Tulis pesan Anda di bawah atau gunakan pertanyaan cepat.',
                                    textAlign: TextAlign.center,
                                    style: const TextStyle(color: _textMuted, fontSize: 12.5, height: 1.4),
                                  ),
                                ],
                              ),
                            ),
                          )
                        : ListView.builder(
                            controller: _scrollController,
                            padding: const EdgeInsets.all(16),
                            itemCount: _messages.length,
                            itemBuilder: (context, index) {
                              final msg = _messages[index];
                              final isMe = msg.isMe;

                              return Align(
                                alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                                child: Container(
                                  margin: const EdgeInsets.only(bottom: 12),
                                  constraints: BoxConstraints(
                                    maxWidth: MediaQuery.of(context).size.width * 0.78,
                                  ),
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: isMe ? _primaryBlue : Colors.white,
                                    borderRadius: BorderRadius.only(
                                      topLeft: const Radius.circular(16),
                                      topRight: const Radius.circular(16),
                                      bottomLeft: Radius.circular(isMe ? 16 : 4),
                                      bottomRight: Radius.circular(isMe ? 4 : 16),
                                    ),
                                    border: isMe ? null : Border.all(color: _borderLight),
                                    boxShadow: [
                                      BoxShadow(
                                        color: isMe
                                            ? _primaryBlue.withValues(alpha: 0.18)
                                            : Colors.black.withValues(alpha: 0.04),
                                        blurRadius: 8,
                                        offset: const Offset(0, 2),
                                      ),
                                    ],
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      if (!isMe)
                                        const Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Text(
                                              'Customer Support DSC',
                                              style: TextStyle(
                                                color: _primaryBlue,
                                                fontWeight: FontWeight.w800,
                                                fontSize: 10.5,
                                              ),
                                            ),
                                            SizedBox(width: 4),
                                            Icon(Icons.verified_rounded, color: _primaryBlue, size: 12),
                                          ],
                                        ),
                                      if (!isMe) const SizedBox(height: 4),
                                      Text(
                                        msg.message,
                                        style: TextStyle(
                                          color: isMe ? Colors.white : _textDark,
                                          fontWeight: isMe ? FontWeight.w600 : FontWeight.w500,
                                          fontSize: 13,
                                          height: 1.35,
                                        ),
                                      ),
                                      const SizedBox(height: 4),
                                      Row(
                                        mainAxisSize: MainAxisSize.min,
                                        mainAxisAlignment: MainAxisAlignment.end,
                                        children: [
                                          Text(
                                            _formatTime(msg.createdAt),
                                            style: TextStyle(
                                              color: isMe ? Colors.white.withValues(alpha: 0.78) : _textMuted,
                                              fontSize: 9.5,
                                              fontFamily: 'monospace',
                                            ),
                                          ),
                                          if (isMe) ...[
                                            const SizedBox(width: 4),
                                            Icon(
                                              msg.isRead ? Icons.done_all_rounded : Icons.done_rounded,
                                              size: 13,
                                              color: msg.isRead
                                                  ? const Color(0xFF93C5FD)
                                                  : Colors.white.withValues(alpha: 0.7),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
          ),

          // Quick Questions Carousel
          Container(
            height: 40,
            margin: const EdgeInsets.only(bottom: 6),
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 14),
              itemCount: _quickQuestions.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (context, index) {
                final q = _quickQuestions[index];
                return ActionChip(
                  label: Text(q, style: const TextStyle(fontSize: 11, color: _textDark, fontWeight: FontWeight.w600)),
                  backgroundColor: Colors.white,
                  side: const BorderSide(color: _borderLight),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  padding: const EdgeInsets.symmetric(horizontal: 4),
                  onPressed: _isSending ? null : () => _sendMessage(q),
                );
              },
            ),
          ),

          // Bottom Input Bar
          SafeArea(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: const BoxDecoration(
                color: Colors.white,
                border: Border(top: BorderSide(color: _borderLight, width: 1.0)),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _textController,
                      style: const TextStyle(color: _textDark, fontSize: 13),
                      maxLines: 4,
                      minLines: 1,
                      textInputAction: TextInputAction.send,
                      onSubmitted: (_) => _sendMessage(),
                      decoration: InputDecoration(
                        hintText: 'Tanyakan tentang ${widget.product.name}...',
                        hintStyle: const TextStyle(color: Color(0xFF9CA3AF), fontSize: 12.5),
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(20),
                          borderSide: const BorderSide(color: _borderLight),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(20),
                          borderSide: const BorderSide(color: _borderLight),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(20),
                          borderSide: const BorderSide(color: _primaryBlue, width: 1.5),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    decoration: BoxDecoration(
                      color: _primaryBlue,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: _primaryBlue.withValues(alpha: 0.25),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: IconButton(
                      onPressed: _isSending ? null : () => _sendMessage(),
                      icon: _isSending
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                            )
                          : const Icon(Icons.send_rounded, color: Colors.white, size: 18),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _formatTime(String raw) {
    if (raw.isEmpty) return '';
    try {
      final dt = DateTime.parse(raw).toLocal();
      final hour = dt.hour.toString().padLeft(2, '0');
      final min = dt.minute.toString().padLeft(2, '0');
      return '$hour:$min';
    } catch (_) {
      return raw;
    }
  }
}
