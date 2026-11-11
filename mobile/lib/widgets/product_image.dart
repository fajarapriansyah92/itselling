import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../models/models.dart';

/// Gambar produk dari server, atau ikon enterprise sesuai tipe produk jika belum ada gambar.
class ProductImage extends StatelessWidget {
  const ProductImage({
    super.key,
    required this.product,
    this.iconSize = 42,
    this.fit = BoxFit.contain,
  });

  final Product product;
  final double iconSize;
  final BoxFit fit;

  @override
  Widget build(BuildContext context) {
    final imageUrl = ApiClient.resolveMediaUrl(product.imageUrl);

    if (imageUrl.isEmpty) {
      return _placeholder();
    }

    return Container(
      color: const Color(0xFFF8FAFC),
      alignment: Alignment.center,
      child: Image.network(
        imageUrl,
        fit: fit,
        errorBuilder: (context, error, stackTrace) => _placeholder(),
        loadingBuilder: (context, child, loadingProgress) {
          if (loadingProgress == null) return child;
          return _placeholder(isLoading: true);
        },
      ),
    );
  }

  Widget _placeholder({bool isLoading = false}) {
    final (IconData icon, List<Color> gradient, Color glowColor) = switch (product.type) {
      'service' => (
          Icons.verified_user_rounded,
          const [Color(0xFFE0F2FE), Color(0xFFF0F9FF)],
          const Color(0xFF0284C7),
        ),
      'software' => (
          Icons.token_rounded,
          const [Color(0xFFF3E8FF), Color(0xFFFAF5FF)],
          const Color(0xFF7C3AED),
        ),
      _ => (
          Icons.developer_board_rounded,
          const [Color(0xFFDBEAFE), Color(0xFFEFF6FF)],
          const Color(0xFF1B64F2),
        ),
    };

    return Container(
      decoration: BoxDecoration(
        gradient: RadialGradient(
          center: Alignment.center,
          radius: 0.9,
          colors: [
            gradient[0],
            gradient[1],
          ],
        ),
      ),
      child: Center(
        child: isLoading
            ? SizedBox(
                width: 22,
                height: 22,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: glowColor.withValues(alpha: 0.7),
                ),
              )
            : Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: glowColor.withValues(alpha: 0.25),
                    width: 1.5,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: glowColor.withValues(alpha: 0.14),
                      blurRadius: 14,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Icon(icon, size: iconSize, color: glowColor),
              ),
      ),
    );
  }
}
