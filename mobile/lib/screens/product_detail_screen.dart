import 'dart:async';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/api_client.dart';
import '../core/formatters.dart';
import '../models/models.dart';
import '../providers/cart_provider.dart';
import '../services/api_service.dart';
import '../widgets/product_image.dart';
import 'cart_screen.dart';
import 'checkout_screen.dart';
import 'login_screen.dart';
import 'product_chat_screen.dart';
import '../providers/auth_provider.dart';

class ProductDetailScreen extends StatefulWidget {
  const ProductDetailScreen({super.key, required this.product});

  final Product product;

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  late Product _product = widget.product;
  int _quantity = 1;
  List<ProductReview> _reviews = [];
  ProductReviewSummary? _reviewSummary;
  bool _isLoadingReviews = true;
  Timer? _pollTimer;

  static const Color _bgPage = Color(0xFFF3F6FC);
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  @override
  void initState() {
    super.initState();
    _refreshProduct();
    _loadReviews();
    _pollTimer = Timer.periodic(const Duration(seconds: 4), (_) {
      if (mounted) {
        _refreshProduct();
        _loadReviews(silent: true);
      }
    });
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _refreshProduct() async {
    try {
      final product = await ApiService.fetchProduct(_product.slug);
      if (mounted) {
        setState(() => _product = product);
      }
    } on ApiException {
      // Data lama tetap aman
    }
  }

  Future<void> _loadReviews({bool silent = false}) async {
    if (!silent && _reviews.isEmpty) {
      setState(() => _isLoadingReviews = true);
    }
    try {
      final result = await ApiService.fetchProductReviews(_product.slug);
      if (mounted) {
        setState(() {
          _reviews = result.reviews;
          _reviewSummary = result.summary;
          _isLoadingReviews = false;
        });
      }
    } catch (_) {
      if (mounted && !silent) {
        setState(() => _isLoadingReviews = false);
      }
    }
  }

  int? get _maxQuantity {
    final stock = _product.stock;
    return stock == null ? null : stock - context.read<CartProvider>().quantityOf(_product.id);
  }

  Widget _buildBottomNavIconButton({
    required IconData icon,
    required String label,
    required String tooltip,
    required VoidCallback onTap,
    int badgeCount = 0,
  }) {
    return Tooltip(
      message: tooltip,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(10),
        child: Container(
          width: 50,
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Stack(
                clipBehavior: Clip.none,
                children: [
                  Icon(icon, color: const Color(0xFF4B5563), size: 21),
                  if (badgeCount > 0)
                    Positioned(
                      top: -4,
                      right: -7,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEF4444),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.white, width: 1.5),
                        ),
                        constraints: const BoxConstraints(minWidth: 15, minHeight: 15),
                        child: Text(
                          badgeCount > 99 ? '99+' : '$badgeCount',
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 8.5,
                            fontWeight: FontWeight.w900,
                            height: 1,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 3),
              Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: Color(0xFF4B5563),
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showPurchaseBottomSheet({required bool isDirectCheckout}) {
    int sheetQuantity = _quantity;
    final stock = _product.stock;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) {
        return StatefulBuilder(
          builder: (context, setSheetState) {
            final maxQty = stock == null ? null : stock - context.read<CartProvider>().quantityOf(_product.id);
            final canProceed = _product.isAvailable && (maxQty == null || maxQty >= sheetQuantity) && sheetQuantity > 0;
            final subtotal = _product.price * sheetQuantity;

            return Container(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
                top: 12,
                left: 20,
                right: 20,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
                boxShadow: [
                  BoxShadow(color: Colors.black12, blurRadius: 24, offset: Offset(0, -6)),
                ],
              ),
              child: SafeArea(
                top: false,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
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

                    // Product Snapshot Row
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(14),
                          child: Container(
                            width: 76,
                            height: 76,
                            color: const Color(0xFFF8FAFC),
                            child: ProductImage(product: _product, iconSize: 34),
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                formatRupiah(_product.price),
                                style: const TextStyle(
                                  color: _primaryBlue,
                                  fontSize: 19,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: -0.2,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                stock == null
                                    ? 'Layanan Jasa Siap Dijadwalkan'
                                    : (stock > 0 ? 'Stok: $stock ${_product.unit}' : 'Stok Habis'),
                                style: TextStyle(
                                  color: stock == null || stock > 0 ? _textMuted : const Color(0xFFEF4444),
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                _product.name,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: _textDark,
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  height: 1.25,
                                ),
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          visualDensity: VisualDensity.compact,
                          icon: const Icon(Icons.close_rounded, color: _textMuted, size: 20),
                          onPressed: () => Navigator.pop(sheetContext),
                        ),
                      ],
                    ),

                    const SizedBox(height: 18),
                    const Divider(color: _borderLight, height: 1),
                    const SizedBox(height: 16),

                    // Quantity Stepper Row
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Jumlah Pembelian',
                              style: TextStyle(color: _textDark, fontSize: 13.5, fontWeight: FontWeight.w700),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Satuan: ${_product.unit}',
                              style: const TextStyle(color: _textMuted, fontSize: 11.5),
                            ),
                          ],
                        ),
                        Container(
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: _borderLight),
                          ),
                          child: Row(
                            children: [
                              IconButton(
                                visualDensity: VisualDensity.compact,
                                onPressed: sheetQuantity > 1
                                    ? () => setSheetState(() => sheetQuantity--)
                                    : null,
                                icon: const Icon(Icons.remove, size: 16, color: _primaryBlue),
                              ),
                              Container(
                                constraints: const BoxConstraints(minWidth: 36),
                                alignment: Alignment.center,
                                child: Text(
                                  '$sheetQuantity',
                                  style: const TextStyle(
                                    color: _textDark,
                                    fontWeight: FontWeight.w800,
                                    fontSize: 14,
                                  ),
                                ),
                              ),
                              IconButton(
                                visualDensity: VisualDensity.compact,
                                onPressed: (maxQty == null || sheetQuantity < maxQty)
                                    ? () => setSheetState(() => sheetQuantity++)
                                    : null,
                                icon: const Icon(Icons.add, size: 16, color: _primaryBlue),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 18),
                    // Subtotal preview
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFBFDBFE)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Subtotal Estimasi',
                            style: TextStyle(color: Color(0xFF1E40AF), fontSize: 12.5, fontWeight: FontWeight.w600),
                          ),
                          Text(
                            formatRupiah(subtotal),
                            style: const TextStyle(
                              color: _primaryBlue,
                              fontSize: 15,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 20),

                    // Primary Action Button
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: FilledButton(
                        onPressed: canProceed
                            ? () {
                                final parentContext = this.context;
                                final messenger = ScaffoldMessenger.of(parentContext);
                                final nav = Navigator.of(parentContext);

                                setState(() => _quantity = sheetQuantity);
                                final added = context.read<CartProvider>().add(_product, sheetQuantity);
                                Navigator.of(sheetContext).pop();

                                if (added) {
                                  if (isDirectCheckout) {
                                    nav.push(
                                      MaterialPageRoute(builder: (_) => const CheckoutScreen()),
                                    );
                                  } else {
                                    messenger.clearSnackBars();
                                    messenger.showSnackBar(
                                      SnackBar(
                                        behavior: SnackBarBehavior.floating,
                                        duration: const Duration(milliseconds: 2800),
                                        dismissDirection: DismissDirection.horizontal,
                                        backgroundColor: Colors.white,
                                        elevation: 6,
                                        shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(14),
                                          side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.2),
                                        ),
                                        content: Row(
                                          children: [
                                            const Icon(Icons.check_circle_rounded, color: _primaryBlue, size: 20),
                                            const SizedBox(width: 10),
                                            Expanded(
                                              child: Text(
                                                '$sheetQuantity ${_product.unit} ${_product.name} berhasil ditambahkan!',
                                                maxLines: 2,
                                                overflow: TextOverflow.ellipsis,
                                                style: const TextStyle(color: _textDark, fontWeight: FontWeight.w700, fontSize: 12.5),
                                              ),
                                            ),
                                          ],
                                        ),
                                        action: SnackBarAction(
                                          label: 'Lihat Keranjang',
                                          backgroundColor: _primaryBlue,
                                          textColor: Colors.white,
                                          onPressed: () {
                                            messenger.hideCurrentSnackBar();
                                            nav.push(
                                              MaterialPageRoute(builder: (_) => const CartScreen()),
                                            );
                                          },
                                        ),
                                      ),
                                    );

                                    Timer(const Duration(milliseconds: 3000), () {
                                      try {
                                        messenger.hideCurrentSnackBar();
                                      } catch (_) {}
                                    });
                                  }
                                } else {
                                  messenger.clearSnackBars();
                                  messenger.showSnackBar(
                                    SnackBar(
                                      behavior: SnackBarBehavior.floating,
                                      duration: const Duration(seconds: 3),
                                      backgroundColor: const Color(0xFF991B1B),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      content: const Text(
                                        'Jumlah pesanan melebihi sisa stok yang tersedia.',
                                        style: TextStyle(color: Colors.white),
                                      ),
                                    ),
                                  );
                                }
                              }
                            : null,
                        style: FilledButton.styleFrom(
                          backgroundColor: _primaryBlue,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              isDirectCheckout ? Icons.bolt_rounded : Icons.add_shopping_cart_rounded,
                              size: 18,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              isDirectCheckout ? 'Beli Langsung Sekarang' : 'Masukkan ke Keranjang',
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _openProductChat() {
    final auth = context.read<AuthProvider>();
    if (!auth.isAuthenticated) {
      showModalBottomSheet(
        context: context,
        backgroundColor: Colors.transparent,
        builder: (sheetContext) => Container(
          padding: const EdgeInsets.all(24),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: SafeArea(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: const BoxDecoration(
                    color: Color(0xFFEFF6FF),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.chat_bubble_outline_rounded, color: _primaryBlue, size: 36),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Masuk untuk Memulai Chat',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: _textDark),
                ),
                const SizedBox(height: 8),
                Text(
                  'Silakan masuk akun terlebih dahulu agar pertanyaan Anda tentang ${_product.name} langsung terhubung ke Admin.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 12.5, color: _textMuted, height: 1.4),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  height: 46,
                  child: FilledButton(
                    onPressed: () {
                      Navigator.pop(sheetContext);
                      Navigator.of(context).push(MaterialPageRoute(builder: (_) => const LoginScreen()));
                    },
                    style: FilledButton.styleFrom(
                      backgroundColor: _primaryBlue,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('Masuk Sekarang', style: TextStyle(fontWeight: FontWeight.w800)),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
      return;
    }

    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => ProductChatScreen(product: _product),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final stock = _product.stock;
    final maxQuantity = _maxQuantity;
    final canAdd = _product.isAvailable && (maxQuantity == null || maxQuantity >= _quantity);

    final (Color typeBg, Color typeFg, String typeTitle) = switch (_product.type) {
      'service' => (const Color(0xFFE0F2FE), const Color(0xFF0284C7), 'LAYANAN JASA IT'),
      'software' => (const Color(0xFFF3E8FF), const Color(0xFF7C3AED), 'SOFTWARE & LISENSI'),
      _ => (const Color(0xFFDBEAFE), _primaryBlue, 'HARDWARE & PERANGKAT'),
    };

    return Scaffold(
      backgroundColor: _bgPage,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: _textDark,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        title: Text(
          _product.category?.name ?? 'Detail Layanan',
          style: const TextStyle(color: _textDark, fontWeight: FontWeight.w800, fontSize: 16),
        ),
        iconTheme: const IconThemeData(color: _textDark),
        actions: [
          IconButton(
            tooltip: 'Tanya Produk / Chat',
            icon: const Icon(Icons.chat_outlined, color: _primaryBlue),
            onPressed: _openProductChat,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await Future.wait([
            _refreshProduct(),
            _loadReviews(),
          ]);
        },
        color: _primaryBlue,
        backgroundColor: Colors.white,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          children: [
            // Hero Image
            Container(
              color: Colors.white,
              child: AspectRatio(
                aspectRatio: 16 / 10,
                child: ProductImage(product: _product, iconSize: 84),
              ),
            ),

            // Main Header Info
            Container(
              color: Colors.white,
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                        decoration: BoxDecoration(
                          color: typeBg,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          typeTitle,
                          style: TextStyle(color: typeFg, fontSize: 11, fontWeight: FontWeight.w800),
                        ),
                      ),
                      if (_product.brand != null) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF3F4F6),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            _product.brand!,
                            style: const TextStyle(color: Color(0xFF4B5563), fontSize: 11, fontWeight: FontWeight.w600),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    _product.name,
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: _textDark, height: 1.3),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Text('SKU: ', style: TextStyle(color: _textMuted, fontSize: 12)),
                      Text(_product.sku, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12, color: Color(0xFF374151))),
                      const SizedBox(width: 12),
                      const Icon(Icons.star_rounded, size: 16, color: Color(0xFFF59E0B)),
                      const SizedBox(width: 2),
                      Text(
                        _product.reviewsCount > 0
                            ? '${_product.ratingAverage.toStringAsFixed(1)} (${_product.reviewsCount} ulasan) · Teruji'
                            : 'Belum ada ulasan · Teruji',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: _textDark),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFBFDBFE)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Harga Penawaran', style: TextStyle(fontSize: 11, color: Color(0xFF1E40AF), fontWeight: FontWeight.w600)),
                            const SizedBox(height: 2),
                            Text(
                              formatRupiah(_product.price),
                              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: _primaryBlue),
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFBFDBFE)),
                          ),
                          child: Text(
                            'per ${_product.unit}',
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: _primaryBlue),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Icon(
                        stock == null || stock > 0 ? Icons.check_circle_rounded : Icons.cancel_rounded,
                        size: 16,
                        color: stock == null || stock > 0 ? const Color(0xFF059669) : Colors.red,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        stock == null ? 'Layanan Jasa Siap Dijadwalkan' : (stock > 0 ? 'Stok unit tersedia ($stock unit)' : 'Stok sedang habis'),
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                          color: stock == null || stock > 0 ? const Color(0xFF059669) : Colors.red,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            const SizedBox(height: 10),

            // Dynamic Technical Highlights & Guarantees Card
            _buildDynamicBenefitsCard(),

            const SizedBox(height: 10),

            // Description Section
            Container(
              color: Colors.white,
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Deskripsi & Cakupan Pekerjaan', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: _textDark)),
                  const SizedBox(height: 10),
                  Text(
                    _product.description,
                    style: const TextStyle(height: 1.6, fontSize: 13.5, color: Color(0xFF4B5563)),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 10),

            // Customer Reviews Section
            _buildReviewsSection(),

            const SizedBox(height: 80),
          ],
        ),
      ),
      bottomNavigationBar: SafeArea(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: const BoxDecoration(
            color: Colors.white,
            border: Border(top: BorderSide(color: _borderLight)),
            boxShadow: [
              BoxShadow(color: Colors.black12, blurRadius: 10, offset: Offset(0, -3)),
            ],
          ),
          child: Row(
            children: [
              _buildBottomNavIconButton(
                icon: Icons.chat_outlined,
                label: 'Chat',
                tooltip: 'Chat Produk & Konsultasi',
                onTap: _openProductChat,
              ),
              const SizedBox(width: 4),

              Consumer<CartProvider>(
                builder: (context, cart, _) {
                  return _buildBottomNavIconButton(
                    icon: Icons.shopping_cart_outlined,
                    label: 'Keranjang',
                    tooltip: 'Buka Keranjang',
                    badgeCount: cart.totalQuantity,
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const CartScreen()),
                      );
                    },
                  );
                },
              ),
              const SizedBox(width: 8),

              // "+ Keranjang" Button
              Expanded(
                flex: 5,
                child: OutlinedButton(
                  onPressed: canAdd ? () => _showPurchaseBottomSheet(isDirectCheckout: false) : null,
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size.fromHeight(44),
                    side: BorderSide(
                      color: canAdd ? _primaryBlue : const Color(0xFFD1D5DB),
                      width: 1.2,
                    ),
                    backgroundColor: canAdd ? const Color(0xFFEFF6FF) : Colors.transparent,
                    foregroundColor: canAdd ? _primaryBlue : const Color(0xFF9CA3AF),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(horizontal: 6),
                  ),
                  child: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.add_shopping_cart_rounded, size: 16),
                      SizedBox(width: 4),
                      Flexible(
                        child: Text(
                          '+ Keranjang',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 8),

              // "Beli Langsung" Button
              Expanded(
                flex: 6,
                child: FilledButton(
                  onPressed: canAdd ? () => _showPurchaseBottomSheet(isDirectCheckout: true) : null,
                  style: FilledButton.styleFrom(
                    minimumSize: const Size.fromHeight(44),
                    backgroundColor: canAdd ? _primaryBlue : const Color(0xFFE5E7EB),
                    foregroundColor: canAdd ? Colors.white : const Color(0xFF9CA3AF),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(horizontal: 6),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.bolt_rounded, size: 18),
                      const SizedBox(width: 2),
                      Flexible(
                        child: Text(
                          _product.isAvailable ? 'Beli Langsung' : 'Stok Habis',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDynamicBenefitsCard() {
    final benefits = _getProductBenefits();
    final sectionTitle = _getBenefitsSectionTitle();

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.verified_user_outlined, size: 18, color: _primaryBlue),
              const SizedBox(width: 8),
              Text(
                sectionTitle,
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: _textDark),
              ),
            ],
          ),
          const SizedBox(height: 14),
          for (int i = 0; i < benefits.length; i++) ...[
            if (i > 0) const SizedBox(height: 12),
            _buildBenefitTile(
              icon: benefits[i].icon,
              color: benefits[i].color,
              title: benefits[i].title,
              desc: benefits[i].desc,
            ),
          ],
        ],
      ),
    );
  }

  String _getBenefitsSectionTitle() {
    final type = _product.type.toLowerCase();
    final catSlug = _product.category?.slug.toLowerCase() ?? '';

    if (type == 'software' || catSlug.contains('software') || catSlug.contains('lisensi')) {
      return 'Keunggulan & Jaminan Lisensi Resmi';
    }
    if (type == 'hardware' || catSlug.contains('hardware')) {
      return 'Garansi & Jaminan Kualitas Hardware';
    }
    if (_product.name.toLowerCase().contains('maintenance') || _product.name.toLowerCase().contains('audit')) {
      return 'Keunggulan & Standar Layanan Konsultasi';
    }
    return 'Keunggulan & Jaminan Pekerjaan Lapangan';
  }

  List<_ProductBenefit> _getProductBenefits() {
    final type = _product.type.toLowerCase();
    final catSlug = _product.category?.slug.toLowerCase() ?? '';
    final nameLower = _product.name.toLowerCase();

    if (catSlug.contains('keamanan') || nameLower.contains('antivirus') || nameLower.contains('security') || nameLower.contains('kaspersky') || nameLower.contains('eset') || nameLower.contains('bitdefender')) {
      return const [
        _ProductBenefit(
          icon: Icons.verified_user_rounded,
          color: _primaryBlue,
          title: '100% Lisensi Asli & Terdaftar Resmi',
          desc: 'Key legal langsung dari authorized distributor tier-1 dengan akses aktivasi konsol vendor resmi.',
        ),
        _ProductBenefit(
          icon: Icons.security_rounded,
          color: Color(0xFF0284C7),
          title: 'Proteksi Endpoint & Ransomware 24/7',
          desc: 'Perlindungan proaktif multi-layer terhadap malware, ransomware, dan update database ancaman otomatis.',
        ),
        _ProductBenefit(
          icon: Icons.receipt_long_rounded,
          color: Color(0xFF7C3AED),
          title: 'Jaminan Aktivasi & Faktur Pajak Resmi',
          desc: 'Garansi aktivasi lancar hingga masa langganan berakhir serta faktur pajak resmi untuk kepatuhan audit IT kantor.',
        ),
      ];
    }

    if (type == 'software' || catSlug.contains('software') || catSlug.contains('lisensi') || nameLower.contains('windows') || nameLower.contains('office') || nameLower.contains('microsoft')) {
      return const [
        _ProductBenefit(
          icon: Icons.verified_rounded,
          color: _primaryBlue,
          title: 'Lisensi Original Genuine (OEM / CSP)',
          desc: 'Aktivasi online langsung ke server resmi Microsoft / vendor tanpa aktivator ilegal, crack, ataupun bypass.',
        ),
        _ProductBenefit(
          icon: Icons.cloud_sync_rounded,
          color: Color(0xFF0284C7),
          title: 'Akses Pembaruan & Stabilitas Penuh',
          desc: 'Mendapatkan patch keamanan berkala, update fitur resmi dari prinsipal, dan stabilitas operasional terjamin.',
        ),
        _ProductBenefit(
          icon: Icons.receipt_long_rounded,
          color: Color(0xFF7C3AED),
          title: 'Sertifikat Legalitas & Faktur Pajak Resmi',
          desc: 'Dilengkapi bukti kepemilikan lisensi resmi (CoA) dan nota komersial sah untuk pembukuan aset instansi.',
        ),
      ];
    }

    if (catSlug.contains('jaringan') || nameLower.contains('mikrotik') || nameLower.contains('cisco') || nameLower.contains('router') || nameLower.contains('switch') || nameLower.contains('access point') || nameLower.contains('firewall') || nameLower.contains('omada')) {
      return const [
        _ProductBenefit(
          icon: Icons.shield_rounded,
          color: _primaryBlue,
          title: 'Garansi Resmi Distributor 1–2 Tahun',
          desc: 'Unit 100% original dan baru, didukung layanan klaim purnajual authorized service center di Indonesia.',
        ),
        _ProductBenefit(
          icon: Icons.router_rounded,
          color: Color(0xFF0284C7),
          title: 'Quality Control & Firmware Stabil',
          desc: 'Pemeriksaan port fisik dan kelengkapan unit sebelum dikirim untuk memastikan perangkat siap diintegrasikan.',
        ),
        _ProductBenefit(
          icon: Icons.support_agent_rounded,
          color: Color(0xFF7C3AED),
          title: 'Konsultasi Topologi & Faktur Resmi',
          desc: 'Free konsultasi penyesuaian arsitektur jaringan kantor serta invoice dan faktur pajak resmi DSC.',
        ),
      ];
    }

    if (type == 'hardware' || catSlug.contains('hardware') || catSlug.contains('komputer') || nameLower.contains('server') || nameLower.contains('laptop') || nameLower.contains('thinkpad') || nameLower.contains('synology') || nameLower.contains('ups') || nameLower.contains('dell')) {
      return const [
        _ProductBenefit(
          icon: Icons.verified_rounded,
          color: _primaryBlue,
          title: 'Unit Original & Garansi Prinsipal Resmi',
          desc: 'Nomor seri (Serial Number) terdaftar resmi pada basis data prinsipal (Dell, Lenovo, Synology, APC) untuk garansi resmi.',
        ),
        _ProductBenefit(
          icon: Icons.local_shipping_rounded,
          color: Color(0xFF0284C7),
          title: 'Kemasan Standar Enterprise & Asuransi',
          desc: 'Pengemasan ekstra aman tahan guncangan khusus perangkat server/elektronik serta asuransi logistik penuh.',
        ),
        _ProductBenefit(
          icon: Icons.assignment_turned_in_rounded,
          color: Color(0xFF7C3AED),
          title: 'Berita Acara (BAST) & Faktur Pajak Resmi',
          desc: 'Disertai dokumen serah terima aset terperinci dan nota resmi untuk kebutuhan audit & inventaris perusahaan.',
        ),
      ];
    }

    if (nameLower.contains('maintenance') || nameLower.contains('audit') || nameLower.contains('keamanan jaringan')) {
      return const [
        _ProductBenefit(
          icon: Icons.workspace_premium_rounded,
          color: _primaryBlue,
          title: 'Lead Engineer Berlisensi Internasional',
          desc: 'Dikerjakan langsung oleh lead engineer bersertifikasi industri (MikroTik MTCRE, Cisco CCNA, CompTIA Security+).',
        ),
        _ProductBenefit(
          icon: Icons.speed_rounded,
          color: Color(0xFF0284C7),
          title: 'SLA Respons Cepat & Monitoring Berkala',
          desc: 'Dukungan helpdesk prioritas, penanganan insiden darurat terstruktur, dan kunjungan checklist preventif rutin.',
        ),
        _ProductBenefit(
          icon: Icons.folder_shared_rounded,
          color: Color(0xFF7C3AED),
          title: 'Laporan Komprehensif & Perjanjian NDA',
          desc: 'Laporan evaluasi sistem berkala, rekomendasi mitigasi risiko, jaminan kerahasiaan data (NDA), dan nota legal.',
        ),
      ];
    }

    return const [
      _ProductBenefit(
        icon: Icons.engineering_rounded,
        color: _primaryBlue,
        title: 'Teknisi Lapangan Berpengalaman & Rapi',
        desc: 'Instalasi rapi mengikuti standar perkabelan terstruktur (ANSI/TIA) dan manajemen jalur kabel profesional.',
      ),
      _ProductBenefit(
        icon: Icons.verified_user_rounded,
        color: Color(0xFF0284C7),
        title: 'Garansi Instalasi & Pendampingan Sistem',
        desc: 'Garansi masa pemeliharaan setelah go-live, free perbaikan kendala konfigurasi, dan uji fungsi bersama klien.',
      ),
      _ProductBenefit(
        icon: Icons.receipt_long_rounded,
        color: Color(0xFF7C3AED),
        title: 'Dokumentasi Topologi & BAST Resmi',
        desc: 'Dilengkapi cetak diagram topologi, port mapping/labeling, Berita Acara Serah Terima (BAST), dan faktur resmi.',
      ),
    ];
  }

  Widget _buildBenefitTile({required IconData icon, required Color color, required String title, required String desc}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: color.withValues(alpha: 0.25)),
          ),
          child: Icon(icon, color: color, size: 20),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _textDark)),
              const SizedBox(height: 2),
              Text(desc, style: const TextStyle(fontSize: 11.5, color: _textMuted, height: 1.35)),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildReviewsSection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.rate_review_outlined, color: _primaryBlue, size: 20),
                  const SizedBox(width: 8),
                  const Text(
                    'Ulasan Pembeli',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: _textDark),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFBFDBFE)),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.bolt_rounded, size: 10, color: _primaryBlue),
                        SizedBox(width: 2),
                        Text('Realtime', style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: _primaryBlue)),
                      ],
                    ),
                  ),
                ],
              ),
              if (_reviewSummary != null && _reviewSummary!.reviewsCount > 0)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF3C7),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.star_rounded, size: 14, color: Color(0xFFF59E0B)),
                      const SizedBox(width: 3),
                      Text(
                        '${_reviewSummary!.ratingAverage.toStringAsFixed(1)} / 5.0',
                        style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Color(0xFFB45309)),
                      ),
                    ],
                  ),
                ),
            ],
          ),
          const SizedBox(height: 16),

          if (_isLoadingReviews)
            const Center(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 24),
                child: CircularProgressIndicator(strokeWidth: 2, color: _primaryBlue),
              ),
            )
          else if (_reviews.isEmpty)
            Container(
              padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: _borderLight),
              ),
              child: const Column(
                children: [
                  Icon(Icons.star_outline_rounded, size: 36, color: Color(0xFF9CA3AF)),
                  SizedBox(height: 8),
                  Text(
                    'Belum Ada Ulasan Pembeli',
                    style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5, color: _textDark),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'Jadilah yang pertama memberikan ulasan setelah menyelesaikan pesanan produk ini.',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 11.5, color: _textMuted),
                  ),
                ],
              ),
            )
          else ...[
            if (_reviewSummary != null)
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: _borderLight),
                ),
                child: Row(
                  children: [
                    Column(
                      children: [
                        Text(
                          _reviewSummary!.ratingAverage.toStringAsFixed(1),
                          style: const TextStyle(fontSize: 32, fontWeight: FontWeight.w900, color: _textDark, height: 1.1),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: List.generate(5, (index) {
                            return Icon(
                              index < _reviewSummary!.ratingAverage.round() ? Icons.star_rounded : Icons.star_outline_rounded,
                              size: 14,
                              color: const Color(0xFFF59E0B),
                            );
                          }),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${_reviewSummary!.reviewsCount} ulasan',
                          style: const TextStyle(fontSize: 11, color: _textMuted),
                        ),
                      ],
                    ),
                    const SizedBox(width: 16),
                    Container(height: 60, width: 1, color: _borderLight),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        children: [5, 4, 3, 2, 1].map((star) {
                          final count = _reviewSummary!.breakdown[star] ?? 0;
                          final total = _reviewSummary!.reviewsCount;
                          final fraction = total > 0 ? (count / total) : 0.0;
                          return Padding(
                            padding: const EdgeInsets.symmetric(vertical: 1.5),
                            child: Row(
                              children: [
                                Text('$star', style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: _textMuted)),
                                const SizedBox(width: 2),
                                const Icon(Icons.star_rounded, size: 10, color: Color(0xFFF59E0B)),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: ClipRRect(
                                    borderRadius: BorderRadius.circular(4),
                                    child: LinearProgressIndicator(
                                      value: fraction,
                                      minHeight: 5,
                                      backgroundColor: const Color(0xFFE5E7EB),
                                      valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFFF59E0B)),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                SizedBox(
                                  width: 18,
                                  child: Text(
                                    '$count',
                                    textAlign: TextAlign.end,
                                    style: const TextStyle(fontSize: 10.5, color: _textMuted),
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ),
                    ),
                  ],
                ),
              ),

            const SizedBox(height: 16),

            for (final review in _reviews) ...[
              Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: _borderLight),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Container(
                                width: 28,
                                height: 28,
                                decoration: BoxDecoration(
                                  color: const Color(0xFFDBEAFE),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                alignment: Alignment.center,
                                child: Text(
                                  (review.userName?.isNotEmpty == true ? review.userName![0] : 'U').toUpperCase(),
                                  style: const TextStyle(fontWeight: FontWeight.w900, color: _primaryBlue, fontSize: 12),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    review.userName ?? 'Pelanggan',
                                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5, color: _textDark),
                                  ),
                                  const Text(
                                    'Pembeli Terverifikasi',
                                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF059669)),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          if (review.createdAt != null)
                            Text(
                              formatDateTime(review.createdAt!),
                              style: const TextStyle(fontSize: 10.5, color: _textMuted),
                            ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: List.generate(5, (index) {
                          return Icon(
                            index < review.rating ? Icons.star_rounded : Icons.star_outline_rounded,
                            size: 15,
                            color: const Color(0xFFF59E0B),
                          );
                        }),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        review.comment,
                        style: const TextStyle(fontSize: 13, color: Color(0xFF374151), height: 1.4),
                      ),
                      if (review.adminReply != null && review.adminReply!.isNotEmpty) ...[
                        const SizedBox(height: 10),
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEFF6FF),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFBFDBFE)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Row(
                                children: [
                                  Icon(Icons.storefront_rounded, size: 14, color: _primaryBlue),
                                  SizedBox(width: 5),
                                  Text(
                                    'Respon Resmi Toko',
                                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: _primaryBlue),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text(
                                review.adminReply!,
                                style: const TextStyle(fontSize: 12, color: Color(0xFF374151), height: 1.3),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ],
          ],
        ],
      ),
    );
  }
}

class _ProductBenefit {
  final IconData icon;
  final Color color;
  final String title;
  final String desc;

  const _ProductBenefit({
    required this.icon,
    required this.color,
    required this.title,
    required this.desc,
  });
}
