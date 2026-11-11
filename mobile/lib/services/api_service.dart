import 'package:flutter/foundation.dart' show kIsWeb;

import '../core/api_client.dart';
import '../models/models.dart';

/// Kumpulan pemanggilan endpoint web service /api/v1.
class ApiService {
  ApiService._();

  static ApiClient get _client => ApiClient.instance;

  static String get _deviceName => kIsWeb ? 'flutter-web' : 'flutter-app';

  static (String, User) _parseAuth(Map<String, dynamic> json) {
    return (json['token'] as String, User.fromJson(json['user'] as Map<String, dynamic>));
  }

  // ---------- Autentikasi & profil ----------

  static Future<(String, User)> login({required String email, required String password}) async {
    final json = await _client.post('/auth/login', {
      'email': email,
      'password': password,
      'device_name': _deviceName,
    });

    return _parseAuth(json);
  }

  static Future<(String, User)> register({
    required String name,
    required String email,
    required String phone,
    required String address,
    required String password,
    required String passwordConfirmation,
  }) async {
    final json = await _client.post('/auth/register', {
      'name': name,
      'email': email,
      'phone': phone,
      'address': address,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'device_name': _deviceName,
    });

    return _parseAuth(json);
  }

  static Future<void> logout() => _client.post('/auth/logout');

  static Future<User> fetchProfile() async {
    final json = await _client.get('/profile');
    return User.fromJson(json['data'] as Map<String, dynamic>);
  }

  static Future<User> updateProfile({required String name, required String phone, required String address}) async {
    final json = await _client.put('/profile', {'name': name, 'phone': phone, 'address': address});
    return User.fromJson(json['data'] as Map<String, dynamic>);
  }

  // ---------- Katalog ----------

  static Future<List<Category>> fetchCategories({bool silent = false}) async {
    final json = await _client.get('/categories', silent: silent);
    return (json['data'] as List<dynamic>).map((item) => Category.fromJson(item as Map<String, dynamic>)).toList();
  }

  static Future<Paginated<Product>> fetchProducts({int page = 1, String? category, String? search, bool silent = false}) async {
    final json = await _client.get(
      '/products',
      query: {
        'page': '$page',
        if (category != null) 'category': category,
        if (search != null && search.isNotEmpty) 'search': search,
      },
      silent: silent,
    );

    return Paginated.fromJson(json, Product.fromJson);
  }

  // ---------- Keranjang (Cart Web Service Sync) ----------

  static Future<void> fetchCart({required int itemsCount, required double totalPrice}) async {
    try {
      await _client.get('/cart', query: {
        'items_count': '$itemsCount',
        'total_price': '${totalPrice.toInt()}',
      });
    } catch (_) {}
  }

  static Future<void> syncCartAction({
    required String action,
    int? productId,
    int? quantity,
    required int itemsCount,
  }) async {
    try {
      await _client.post('/cart', {
        'action': action,
        if (productId != null) 'product_id': productId,
        if (quantity != null) 'quantity': quantity,
        'items_count': itemsCount,
      });
    } catch (_) {}
  }

  static Future<Product> fetchProduct(String slug) async {
    final json = await _client.get('/products/$slug');
    return Product.fromJson(json['data'] as Map<String, dynamic>);
  }

  // ---------- Pesanan ----------

  static Future<Paginated<Order>> fetchOrders({int page = 1}) async {
    final json = await _client.get('/orders', query: {'page': '$page'});
    return Paginated.fromJson(json, Order.fromJson);
  }

  static Future<Order> fetchOrder(String orderNumber) async {
    final json = await _client.get('/orders/$orderNumber');
    return Order.fromJson(json['data'] as Map<String, dynamic>);
  }

  static Future<Order> placeOrder({
    required List<Map<String, int>> items,
    required String recipientName,
    required String recipientPhone,
    required String shippingAddress,
    String? notes,
    String? paymentMethod,
    String? paymentChannel,
  }) async {
    final json = await _client.post('/orders', {
      'items': items,
      'recipient_name': recipientName,
      'recipient_phone': recipientPhone,
      'shipping_address': shippingAddress,
      'notes': notes,
      if (paymentMethod != null) 'payment_method': paymentMethod,
      if (paymentChannel != null) 'payment_channel': paymentChannel,
    });

    return Order.fromJson(json['data'] as Map<String, dynamic>);
  }

  static Future<Order> uploadPaymentProof(
    String orderNumber, {
    required List<int> bytes,
    required String filename,
  }) async {
    final json = await _client.uploadBytes(
      '/orders/$orderNumber/payment-proof',
      field: 'payment_proof',
      bytes: bytes,
      filename: filename,
    );
    return Order.fromJson(json['data'] as Map<String, dynamic>);
  }

  static Future<Order> cancelOrder(String orderNumber) async {
    final json = await _client.post('/orders/$orderNumber/cancel');
    return Order.fromJson(json['data'] as Map<String, dynamic>);
  }

  static Future<Order> receiveOrder(String orderNumber) async {
    final json = await _client.post('/orders/$orderNumber/receive');
    return Order.fromJson(json['data'] as Map<String, dynamic>);
  }

  static Future<Order> simulateOrderStatus(String orderNumber, String status) async {
    final json = await _client.post('/orders/$orderNumber/simulate-status', {'status': status});
    return Order.fromJson(json['data'] as Map<String, dynamic>);
  }

  static Future<Order> completeDigitalOrder(String orderNumber) async {
    final json = await _client.post('/orders/$orderNumber/complete-digital');
    return Order.fromJson(json['data'] as Map<String, dynamic>);
  }

  static Future<Order> completeServiceOrder(String orderNumber) async {
    final json = await _client.post('/orders/$orderNumber/complete-service');
    return Order.fromJson(json['data'] as Map<String, dynamic>);
  }

  static Future<List<OrderMessage>> fetchOrderMessages(String orderNumber, {bool silent = false}) async {
    final json = await _client.get('/orders/$orderNumber/messages', silent: silent);
    final data = json['data'] as List<dynamic>;
    return data.map((item) => OrderMessage.fromJson(item as Map<String, dynamic>)).toList();
  }

  static Future<OrderMessage> sendOrderMessage(String orderNumber, String message) async {
    final json = await _client.post(
      '/orders/$orderNumber/messages',
      {'message': message},
    );
    final data = json['data'] as Map<String, dynamic>;
    return OrderMessage.fromJson(data);
  }

  static Future<({ProductReviewSummary summary, List<ProductReview> reviews})> fetchProductReviews(String productSlug) async {
    final json = await _client.get('/products/$productSlug/reviews');
    final summary = ProductReviewSummary.fromJson(json['summary'] as Map<String, dynamic>? ?? {});
    final data = json['data'] as List<dynamic>? ?? [];
    final reviews = data.map((item) => ProductReview.fromJson(item as Map<String, dynamic>)).toList();
    return (summary: summary, reviews: reviews);
  }

  static Future<ProductReview> submitOrderReview({
    required String orderNumber,
    required int orderItemId,
    required int rating,
    required String comment,
  }) async {
    final json = await _client.post(
      '/orders/$orderNumber/reviews',
      {
        'order_item_id': orderItemId,
        'rating': rating,
        'comment': comment,
      },
    );
    final data = json['data'] as Map<String, dynamic>;
    return ProductReview.fromJson(data);
  }

  static Future<List<OrderMessage>> fetchProductMessages(String productSlug, {bool silent = false}) async {
    final json = await _client.get('/products/$productSlug/messages', silent: silent);
    final data = json['data'] as List<dynamic>? ?? [];
    return data.map((item) => OrderMessage.fromJson(item as Map<String, dynamic>)).toList();
  }

  static Future<OrderMessage> sendProductMessage(String productSlug, String message) async {
    final json = await _client.post(
      '/products/$productSlug/messages',
      {'message': message},
    );
    final data = json['data'] as Map<String, dynamic>;
    return OrderMessage.fromJson(data);
  }

  static Future<List<UserInquiry>> fetchUserInquiries() async {
    final json = await _client.get('/user/inquiries');
    final data = json['data'] as List<dynamic>? ?? [];
    return data.map((item) => UserInquiry.fromJson(item as Map<String, dynamic>)).toList();
  }

  static Future<int> fetchUserUnreadChatCount({bool silent = true}) async {
    try {
      final json = await _client.get('/user/inquiries/unread-count', silent: silent);
      return (json['unread_count'] as num?)?.toInt() ?? 0;
    } catch (_) {
      return 0;
    }
  }

  static Future<Map<String, dynamic>> createOrderSnapToken(String orderNumber, {String? paymentChannel, bool force = false}) async {
    final json = await _client.post('/orders/$orderNumber/snap-token', {
      if (paymentChannel != null && paymentChannel.isNotEmpty) 'payment_channel': paymentChannel,
      if (force) 'force': true,
    });
    return json;
  }

  static Future<Map<String, dynamic>> syncOrderPaymentStatus(String orderNumber) async {
    final json = await _client.get('/orders/$orderNumber/sync-payment');
    return json;
  }
}

