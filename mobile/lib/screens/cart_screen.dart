import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/formatters.dart';
import '../providers/cart_provider.dart';
import '../widgets/product_image.dart';
import 'checkout_screen.dart';
import 'product_detail_screen.dart';

class CartScreen extends StatelessWidget {
  const CartScreen({super.key});

  static const Color _bgPage = Color(0xFFF3F6FC);
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();

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
              child: const Icon(Icons.shopping_cart_rounded, color: _primaryBlue, size: 18),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Keranjang Belanja',
                  style: TextStyle(color: _textDark, fontSize: 16, fontWeight: FontWeight.w800),
                ),
                Text(
                  cart.isEmpty ? 'Belum ada item dipilih' : '${cart.totalQuantity} item siap dipesan',
                  style: const TextStyle(color: _textMuted, fontSize: 11, fontWeight: FontWeight.w500),
                ),
              ],
            ),
          ],
        ),
        actions: [
          if (!cart.isEmpty)
            Padding(
              padding: const EdgeInsets.only(right: 8),
              child: TextButton.icon(
                onPressed: () {
                  showDialog(
                    context: context,
                    builder: (ctx) => AlertDialog(
                      backgroundColor: Colors.white,
                      surfaceTintColor: Colors.transparent,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                        side: const BorderSide(color: _borderLight),
                      ),
                      title: const Text(
                        'Kosongkan Keranjang?',
                        style: TextStyle(color: _textDark, fontWeight: FontWeight.w800, fontSize: 17),
                      ),
                      content: const Text(
                        'Semua produk dan layanan di keranjang belanja akan dihapus.',
                        style: TextStyle(color: _textMuted, fontSize: 13.5),
                      ),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.pop(ctx),
                          child: const Text('Batal', style: TextStyle(color: _textMuted, fontWeight: FontWeight.w700)),
                        ),
                        FilledButton(
                          style: FilledButton.styleFrom(
                            backgroundColor: const Color(0xFFEF4444),
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          onPressed: () {
                            cart.clear();
                            Navigator.pop(ctx);
                          },
                          child: const Text('Kosongkan', style: TextStyle(fontWeight: FontWeight.w800)),
                        ),
                      ],
                    ),
                  );
                },
                style: TextButton.styleFrom(
                  foregroundColor: const Color(0xFFEF4444),
                  backgroundColor: const Color(0xFFFEF2F2),
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                icon: const Icon(Icons.delete_sweep_outlined, size: 17, color: Color(0xFFEF4444)),
                label: const Text(
                  'Hapus Semua',
                  style: TextStyle(color: Color(0xFFEF4444), fontSize: 11.5, fontWeight: FontWeight.w700),
                ),
              ),
            ),
        ],
      ),
      body: cart.isEmpty
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 92,
                      height: 92,
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
                      child: const Icon(
                        Icons.shopping_cart_outlined,
                        size: 42,
                        color: _primaryBlue,
                      ),
                    ),
                    const SizedBox(height: 20),
                    const Text(
                      'Keranjang Belanja Kosong',
                      style: TextStyle(
                        color: _textDark,
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Belum ada perangkat hardware, lisensi software, atau jasa IT yang ditambahkan ke keranjang.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: _textMuted,
                        fontSize: 13,
                        height: 1.45,
                      ),
                    ),
                  ],
                ),
              ),
            )
          : ListView.separated(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 24),
              itemCount: cart.items.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) => _CartItemTile(item: cart.items[index]),
            ),
      bottomNavigationBar: cart.isEmpty
          ? null
          : Container(
              padding: const EdgeInsets.fromLTRB(20, 14, 20, 18),
              decoration: BoxDecoration(
                color: Colors.white,
                border: const Border(top: BorderSide(color: _borderLight)),
                boxShadow: [
                  BoxShadow(
                    color: _primaryBlue.withValues(alpha: 0.06),
                    blurRadius: 16,
                    offset: const Offset(0, -4),
                  ),
                ],
              ),
              child: SafeArea(
                top: false,
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Total Pembelian (${cart.totalQuantity} item)',
                            style: const TextStyle(
                              color: _textMuted,
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            formatRupiah(cart.totalPrice),
                            style: const TextStyle(
                              color: _primaryBlue,
                              fontSize: 20,
                              fontWeight: FontWeight.w900,
                              letterSpacing: -0.3,
                            ),
                          ),
                        ],
                      ),
                    ),
                    FilledButton.icon(
                      onPressed: () => Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const CheckoutScreen()),
                      ),
                      style: FilledButton.styleFrom(
                        backgroundColor: _primaryBlue,
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      icon: const Icon(Icons.shield_outlined, size: 18),
                      label: const Text(
                        'Checkout',
                        style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14.5),
                      ),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}

class _CartItemTile extends StatelessWidget {
  const _CartItemTile({required this.item});

  final CartItem item;

  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  @override
  Widget build(BuildContext context) {
    final cart = context.read<CartProvider>();
    final product = item.product;
    final stock = product.stock;
    final isService = product.type == 'service';
    final isSoftware = product.type == 'software';

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
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
            );
          },
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Thumbnail
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: SizedBox.square(
                    dimension: 76,
                    child: Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        border: Border.all(color: const Color(0xFFF1F5F9)),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ProductImage(product: product, iconSize: 30),
                    ),
                  ),
                ),
                const SizedBox(width: 12),

                // Product Info
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Category / Type Pill
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: isService
                              ? const Color(0xFFE0F2FE)
                              : (isSoftware ? const Color(0xFFF3E8FF) : const Color(0xFFDBEAFE)),
                          borderRadius: BorderRadius.circular(5),
                        ),
                        child: Text(
                          product.typeLabel.toUpperCase(),
                          style: TextStyle(
                            color: isService
                                ? const Color(0xFF0369A1)
                                : (isSoftware ? const Color(0xFF6D28D9) : const Color(0xFF1D4ED8)),
                            fontSize: 8.5,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 0.3,
                          ),
                        ),
                      ),
                      const SizedBox(height: 5),
                      Text(
                        product.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: _textDark,
                          fontWeight: FontWeight.w800,
                          fontSize: 13.5,
                          height: 1.25,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${formatRupiah(product.price)} / ${product.unit}',
                        style: const TextStyle(color: _textMuted, fontSize: 11.5, fontWeight: FontWeight.w500),
                      ),
                      const SizedBox(height: 7),
                      Text(
                        formatRupiah(item.subtotal),
                        style: const TextStyle(
                          fontWeight: FontWeight.w900,
                          color: _primaryBlue,
                          fontSize: 14.5,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),

                // Actions: Delete + Quantity Stepper
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    InkWell(
                      borderRadius: BorderRadius.circular(8),
                      onTap: () => cart.remove(product.id),
                      child: Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF2F2),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xFFEF4444)),
                      ),
                    ),
                    const SizedBox(height: 18),
                    Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: _borderLight),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          InkWell(
                            onTap: () => cart.setQuantity(product.id, item.quantity - 1),
                            borderRadius: const BorderRadius.horizontal(left: Radius.circular(10)),
                            child: const Padding(
                              padding: EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                              child: Icon(Icons.remove, size: 15, color: _primaryBlue),
                            ),
                          ),
                          Container(
                            constraints: const BoxConstraints(minWidth: 28),
                            alignment: Alignment.center,
                            padding: const EdgeInsets.symmetric(horizontal: 4),
                            child: Text(
                              '${item.quantity}',
                              style: const TextStyle(color: _textDark, fontWeight: FontWeight.w800, fontSize: 13),
                            ),
                          ),
                          InkWell(
                            onTap: (stock == null || item.quantity < stock)
                                ? () => cart.setQuantity(product.id, item.quantity + 1)
                                : null,
                            borderRadius: const BorderRadius.horizontal(right: Radius.circular(10)),
                            child: Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                              child: Icon(
                                Icons.add,
                                size: 15,
                                color: (stock == null || item.quantity < stock)
                                    ? _primaryBlue
                                    : const Color(0xFFCBD5E1),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
