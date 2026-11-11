import 'package:flutter/foundation.dart' hide Category;

import '../models/models.dart';

class CartItem {
  CartItem(this.product, this.quantity);

  final Product product;
  int quantity;

  double get subtotal => product.price * quantity;
}

class CartProvider extends ChangeNotifier {
  final Map<int, CartItem> _items = {};

  List<CartItem> get items => _items.values.toList();
  bool get isEmpty => _items.isEmpty;
  int get totalQuantity => _items.values.fold<int>(0, (sum, item) => sum + item.quantity);
  double get totalPrice => _items.values.fold<double>(0, (sum, item) => sum + item.subtotal);

  int quantityOf(int productId) => _items[productId]?.quantity ?? 0;

  /// Menambah produk ke keranjang. Mengembalikan false jika melebihi stok.
  bool add(Product product, int quantity) {
    final newQuantity = quantityOf(product.id) + quantity;
    final stock = product.stock;

    if (stock != null && newQuantity > stock) {
      return false;
    }

    _items[product.id] = CartItem(product, newQuantity);
    notifyListeners();

    return true;
  }

  void setQuantity(int productId, int quantity) {
    final item = _items[productId];
    if (item == null) {
      return;
    }

    if (quantity <= 0) {
      _items.remove(productId);
    } else {
      final stock = item.product.stock;
      item.quantity = (stock != null && quantity > stock) ? stock : quantity;
    }

    notifyListeners();
  }

  void remove(int productId) {
    _items.remove(productId);
    notifyListeners();
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }
}
