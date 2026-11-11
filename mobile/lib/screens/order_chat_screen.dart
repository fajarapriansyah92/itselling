import 'dart:async';
import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../models/models.dart';
import '../services/api_service.dart';

class OrderChatScreen extends StatefulWidget {
  const OrderChatScreen({
    super.key,
    required this.orderNumber,
    this.orderStatusLabel,
  });

  final String orderNumber;
  final String? orderStatusLabel;

  @override
  State<OrderChatScreen> createState() => _OrderChatScreenState();
}

class _OrderChatScreenState extends State<OrderChatScreen> {
  static const Color _bgPage = Color(0xFFF3F6FC);
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderSoft = Color(0xFFE5EAF2);

  final _scrollController = ScrollController();
  final _textController = TextEditingController();
  final List<OrderMessage> _messages = [];
  bool _isLoading = true;
  bool _isSending = false;
  String? _error;
  Timer? _pollingTimer;

  final List<String> _quickQuestions = [
    '📦 Kapan barang dikirim?',
    '🛠️ Kapan estimasi teknisi datang?',
    '📑 Mohon info nomor resi',
    '💳 Konfirmasi status pembayaran',
    '🛡️ Bagaimana klaim garansi hardware?',
  ];

  @override
  void initState() {
    super.initState();
    _loadMessages(initial: true);
    // Polling setiap 1.5 detik saat layar chat aktif untuk update pesan masuk dari admin secara realtime
    _pollingTimer = Timer.periodic(const Duration(milliseconds: 1500), (_) {
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
      final messages = await ApiService.fetchOrderMessages(widget.orderNumber, silent: !initial);
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
      final newMsg = await ApiService.sendOrderMessage(widget.orderNumber, text);
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
            backgroundColor: Colors.white,
            content: Text(e.message, style: const TextStyle(color: Color(0xFFDC2626), fontWeight: FontWeight.w700)),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSending = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: Colors.white,
            content: Text('Gagal mengirim pesan: $e', style: const TextStyle(color: Color(0xFFDC2626), fontWeight: FontWeight.w700)),
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
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 1,
        shape: const Border(bottom: BorderSide(color: _borderSoft, width: 1.2)),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: _textDark),
          onPressed: () => Navigator.of(context).pop(),
        ),
        titleSpacing: 0,
        title: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: const Icon(Icons.support_agent_rounded, color: _primaryBlue, size: 20),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Chat Admin IT Selling',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: _textDark),
                  ),
                  Text(
                    'Pesanan #${widget.orderNumber}${widget.orderStatusLabel != null ? ' • ${widget.orderStatusLabel}' : ''}',
                    style: const TextStyle(fontSize: 11, color: _textMuted),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: _primaryBlue, size: 20),
            tooltip: 'Segarkan Pesan',
            onPressed: () => _loadMessages(initial: false),
          ),
        ],
      ),
      body: Column(
        children: [
          // Quick Question Chips
          Container(
            padding: const EdgeInsets.symmetric(vertical: 8),
            decoration: const BoxDecoration(
              color: Colors.white,
              border: Border(bottom: BorderSide(color: _borderSoft)),
            ),
            child: SizedBox(
              height: 34,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 14),
                itemCount: _quickQuestions.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (context, index) {
                  final question = _quickQuestions[index];
                  return ActionChip(
                    padding: const EdgeInsets.symmetric(horizontal: 6),
                    backgroundColor: const Color(0xFFEFF6FF),
                    side: const BorderSide(color: Color(0xFFBFDBFE)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    label: Text(
                      question,
                      style: const TextStyle(
                        fontSize: 11,
                        color: _primaryBlue,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    onPressed: () => _sendMessage(question),
                  );
                },
              ),
            ),
          ),

          // Messages View
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: _primaryBlue))
                : _error != null
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.error_outline_rounded, color: Color(0xFFEF4444), size: 36),
                            const SizedBox(height: 8),
                            Text(_error!, style: const TextStyle(color: Color(0xFFDC2626), fontSize: 13)),
                            const SizedBox(height: 12),
                            FilledButton.icon(
                              style: FilledButton.styleFrom(
                                backgroundColor: _primaryBlue,
                                foregroundColor: Colors.white,
                              ),
                              onPressed: () => _loadMessages(initial: true),
                              icon: const Icon(Icons.refresh_rounded, size: 16),
                              label: const Text('Coba Lagi'),
                            ),
                          ],
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
                                    width: 56,
                                    height: 56,
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      shape: BoxShape.circle,
                                      border: Border.all(color: const Color(0xFFDBEAFE)),
                                    ),
                                    child: const Icon(Icons.forum_outlined, color: _primaryBlue, size: 28),
                                  ),
                                  const SizedBox(height: 14),
                                  const Text(
                                    'Mulai Percakapan Pesanan',
                                    style: TextStyle(color: _textDark, fontWeight: FontWeight.w800, fontSize: 15),
                                  ),
                                  const SizedBox(height: 6),
                                  const Text(
                                    'Tanyakan seputar estimasi pengiriman, instalasi teknisi, atau konfirmasi pesanan Anda.',
                                    style: TextStyle(color: _textMuted, fontSize: 12, height: 1.4),
                                    textAlign: TextAlign.center,
                                  ),
                                ],
                              ),
                            ),
                          )
                        : ListView.builder(
                            controller: _scrollController,
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                            itemCount: _messages.length,
                            itemBuilder: (context, index) {
                              final msg = _messages[index];
                              return _buildMessageBubble(msg);
                            },
                          ),
          ),

          // Bottom Input Bar
          Container(
            padding: EdgeInsets.fromLTRB(12, 8, 12, MediaQuery.of(context).padding.bottom + 8),
            decoration: const BoxDecoration(
              color: Colors.white,
              border: Border(top: BorderSide(color: _borderSoft, width: 1.2)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      color: const Color(0xFFF9FAFB),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: _borderSoft),
                    ),
                    child: TextField(
                      controller: _textController,
                      style: const TextStyle(color: _textDark, fontSize: 13),
                      cursorColor: _primaryBlue,
                      minLines: 1,
                      maxLines: 4,
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        hintText: 'Ketik pesan ke admin / teknisi...',
                        hintStyle: const TextStyle(color: Color(0xFF9CA3AF), fontSize: 13),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide.none,
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide.none,
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: _primaryBlue, width: 1.5),
                        ),
                      ),
                      onSubmitted: (_) => _sendMessage(),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                InkWell(
                  borderRadius: BorderRadius.circular(14),
                  onTap: _isSending ? null : () => _sendMessage(),
                  child: Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: _primaryBlue,
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: _primaryBlue.withValues(alpha: 0.28),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Center(
                      child: _isSending
                          ? const SizedBox.square(
                              dimension: 18,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                            )
                          : const Icon(Icons.send_rounded, color: Colors.white, size: 20),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMessageBubble(OrderMessage msg) {
    final isMe = msg.senderType == 'customer';

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: isMe ? MainAxisAlignment.end : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          if (!isMe) ...[
            Container(
              width: 30,
              height: 30,
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                shape: BoxShape.circle,
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: const Center(
                child: Icon(Icons.shield_rounded, color: _primaryBlue, size: 15),
              ),
            ),
            const SizedBox(width: 8),
          ],
          Flexible(
            child: Container(
              constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.76),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: isMe ? _primaryBlue : Colors.white,
                borderRadius: BorderRadius.only(
                  topLeft: const Radius.circular(16),
                  topRight: const Radius.circular(16),
                  bottomLeft: Radius.circular(isMe ? 16 : 4),
                  bottomRight: Radius.circular(isMe ? 4 : 16),
                ),
                border: Border.all(
                  color: isMe ? _primaryBlue : _borderSoft,
                  width: 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.03),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                children: [
                  if (!isMe) ...[
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          msg.senderName,
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            color: _primaryBlue,
                          ),
                        ),
                        const SizedBox(width: 4),
                        const Icon(Icons.verified_rounded, size: 12, color: _primaryBlue),
                      ],
                    ),
                    const SizedBox(height: 3),
                  ],
                  Text(
                    msg.message,
                    style: TextStyle(
                      fontSize: 13,
                      height: 1.4,
                      color: isMe ? Colors.white : _textDark,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        msg.createdAtHuman.isNotEmpty ? msg.createdAtHuman : '',
                        style: TextStyle(
                          fontSize: 10,
                          color: isMe ? Colors.white.withValues(alpha: 0.8) : _textMuted,
                        ),
                      ),
                      if (isMe) ...[
                        const SizedBox(width: 4),
                        Icon(
                          msg.readAt != null ? Icons.done_all_rounded : Icons.done_rounded,
                          size: 13,
                          color: Colors.white.withValues(alpha: 0.9),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
