/// Model data yang dipetakan dari respons JSON web service.
library;

class User {
  const User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.address,
  });

  factory User.fromJson(Map<String, dynamic> json) => User(
        id: json['id'] as int,
        name: json['name'] as String,
        email: json['email'] as String,
        phone: json['phone'] as String?,
        address: json['address'] as String?,
      );

  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? address;
}

class Category {
  const Category({required this.id, required this.name, required this.slug, this.productsCount});

  factory Category.fromJson(Map<String, dynamic> json) => Category(
        id: json['id'] as int,
        name: json['name'] as String,
        slug: json['slug'] as String,
        productsCount: json['products_count'] as int?,
      );

  final int id;
  final String name;
  final String slug;
  final int? productsCount;
}

class Product {
  const Product({
    required this.id,
    required this.sku,
    required this.name,
    required this.slug,
    required this.type,
    required this.typeLabel,
    required this.unit,
    required this.shortDescription,
    required this.description,
    required this.price,
    required this.isAvailable,
    this.brand,
    this.stock,
    this.imageUrl,
    this.category,
    this.ratingAverage = 0.0,
    this.reviewsCount = 0,
  });

  factory Product.fromJson(Map<String, dynamic> json) => Product(
        id: json['id'] as int,
        sku: (json['sku'] as String?) ?? '-',
        name: (json['name'] as String?) ?? '',
        slug: (json['slug'] as String?) ?? '',
        type: (json['type'] as String?) ?? 'hardware',
        typeLabel: (json['type_label'] as String?) ?? 'Hardware',
        brand: json['brand'] as String?,
        unit: (json['unit'] as String?)?.trim().isNotEmpty == true
            ? (json['unit'] as String)
            : 'unit',
        shortDescription: (json['short_description'] as String?) ?? (json['description'] as String?) ?? '',
        description: (json['description'] as String?) ?? '',
        price: (json['price'] as num).toDouble(),
        stock: json['stock'] as int?,
        isAvailable: (json['is_available'] as bool?) ?? true,
        imageUrl: json['image_url'] as String?,
        category: json['category'] == null ? null : Category.fromJson(json['category'] as Map<String, dynamic>),
        ratingAverage: (json['rating_average'] as num?)?.toDouble() ?? 0.0,
        reviewsCount: (json['reviews_count'] as num?)?.toInt() ?? 0,
      );

  final int id;
  final String sku;
  final String name;
  final String slug;

  /// hardware, software, atau service.
  final String type;
  final String typeLabel;
  final String? brand;
  final String unit;
  final String shortDescription;
  final String description;
  final double price;

  /// Null berarti stok tidak dibatasi (produk jasa).
  final int? stock;
  final bool isAvailable;
  final String? imageUrl;
  final Category? category;
  final double ratingAverage;
  final int reviewsCount;

  String? get categoryName => category?.name;
}

class ProductReview {
  const ProductReview({
    required this.id,
    required this.rating,
    required this.comment,
    this.userName,
    this.productName,
    this.adminReply,
    this.adminRepliedAt,
    this.createdAt,
  });

  factory ProductReview.fromJson(Map<String, dynamic> json) => ProductReview(
        id: json['id'] as int,
        rating: (json['rating'] as num).toInt(),
        comment: json['comment'] as String,
        userName: json['user_name'] as String?,
        productName: json['product_name'] as String?,
        adminReply: json['admin_reply'] as String?,
        adminRepliedAt: json['admin_replied_at'] != null ? DateTime.tryParse(json['admin_replied_at'].toString()) : null,
        createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at'].toString()) : null,
      );

  final int id;
  final int rating;
  final String comment;
  final String? userName;
  final String? productName;
  final String? adminReply;
  final DateTime? adminRepliedAt;
  final DateTime? createdAt;
}

class ProductReviewSummary {
  const ProductReviewSummary({
    required this.ratingAverage,
    required this.reviewsCount,
    required this.breakdown,
  });

  factory ProductReviewSummary.fromJson(Map<String, dynamic> json) {
    final rawBreakdown = json['breakdown'] as Map<String, dynamic>? ?? {};
    final Map<int, int> breakdown = {};
    for (int i = 1; i <= 5; i++) {
      breakdown[i] = (rawBreakdown[i.toString()] as num?)?.toInt() ?? 0;
    }
    return ProductReviewSummary(
      ratingAverage: (json['rating_average'] as num?)?.toDouble() ?? 0.0,
      reviewsCount: (json['reviews_count'] as num?)?.toInt() ?? 0,
      breakdown: breakdown,
    );
  }

  final double ratingAverage;
  final int reviewsCount;
  final Map<int, int> breakdown;
}

class OrderItem {
  const OrderItem({
    this.id = 0,
    required this.productId,
    required this.productName,
    required this.price,
    required this.quantity,
    required this.subtotal,
    this.imageUrl,
    this.licenseKey,
    this.licenseKeys = const [],
    this.isReviewed = false,
    this.review,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    List<String> keys = [];
    if (json['license_keys'] is List) {
      keys = (json['license_keys'] as List)
          .map((e) => e.toString().trim())
          .where((e) => e.isNotEmpty)
          .toList();
    } else if (json['license_key'] is String && (json['license_key'] as String).isNotEmpty) {
      keys = [json['license_key'] as String];
    }

    return OrderItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      productId: json['product_id'] as int,
      productName: json['product_name'] as String,
      price: (json['price'] as num).toDouble(),
      quantity: json['quantity'] as int,
      subtotal: (json['subtotal'] as num).toDouble(),
      imageUrl: json['image_url'] as String?,
      licenseKey: keys.isNotEmpty ? keys.first : (json['license_key'] as String?),
      licenseKeys: keys,
      isReviewed: json['is_reviewed'] as bool? ?? false,
      review: json['review'] != null ? ProductReview.fromJson(json['review'] as Map<String, dynamic>) : null,
    );
  }

  final int id;
  final int productId;
  final String productName;
  final double price;
  final int quantity;
  final double subtotal;
  final String? imageUrl;
  final String? licenseKey;
  final List<String> licenseKeys;
  final bool isReviewed;
  final ProductReview? review;
}

class Order {
  const Order({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.statusLabel,
    required this.total,
    required this.recipientName,
    required this.recipientPhone,
    required this.shippingAddress,
    required this.items,
    required this.createdAt,
    this.trackingNumber,
    this.notes,
    this.paymentProofUrl,
    this.itemsCount,
    this.paidAt,
    this.completedAt,
    this.cancelledAt,
    this.isDigital,
    this.isService,
    this.paymentMethod,
    this.paymentChannel,
    this.snapToken,
    this.snapRedirectUrl,
    this.vaNumber,
    this.paymentBank,
    this.qrCodeUrl,
  });

  factory Order.fromJson(Map<String, dynamic> json) => Order(
        id: json['id'] as int,
        orderNumber: json['order_number'] as String,
        status: json['status'] as String,
        statusLabel: (json['status'] == 'paid' || (json['status_label'] as String? ?? '').toLowerCase().contains('diterima') && json['status'] == 'paid')
            ? 'Pembayaran Berhasil'
            : (json['status_label'] as String? ?? ''),
        total: (json['total'] as num).toDouble(),
        recipientName: json['recipient_name'] as String,
        recipientPhone: json['recipient_phone'] as String,
        shippingAddress: json['shipping_address'] as String,
        trackingNumber: json['tracking_number'] as String?,
        notes: json['notes'] as String?,
        paymentProofUrl: json['payment_proof_url'] as String?,
        snapToken: json['snap_token'] as String?,
        snapRedirectUrl: json['snap_redirect_url'] as String?,
        vaNumber: json['va_number'] as String?,
        paymentBank: json['payment_bank'] as String?,
        qrCodeUrl: json['qr_code_url'] as String?,
        itemsCount: json['items_count'] as int?,
        items: (json['items'] as List<dynamic>? ?? const [])
            .map((item) => OrderItem.fromJson(item as Map<String, dynamic>))
            .toList(),
        createdAt: DateTime.parse(json['created_at'] as String),
        paidAt: _parseDate(json['paid_at']),
        completedAt: _parseDate(json['completed_at']),
        cancelledAt: _parseDate(json['cancelled_at']),
        isDigital: json['is_digital'] as bool? ?? false,
        isService: json['is_service'] as bool? ?? false,
        paymentMethod: json['payment_method'] as String?,
        paymentChannel: json['payment_channel'] as String?,
      );

  final int id;
  final String orderNumber;

  /// pending, paid, processing, shipped, completed, atau cancelled.
  final String status;
  final bool? isDigital;
  final bool? isService;
  final String? paymentMethod;
  final String? paymentChannel;
  final String? snapToken;
  final String? snapRedirectUrl;
  final String? vaNumber;
  final String? paymentBank;
  final String? qrCodeUrl;
  final String statusLabel;
  final double total;
  final String recipientName;
  final String recipientPhone;
  final String shippingAddress;
  final String? trackingNumber;
  final String? notes;
  final String? paymentProofUrl;
  final int? itemsCount;
  final List<OrderItem> items;
  final DateTime createdAt;
  final DateTime? paidAt;
  final DateTime? completedAt;
  final DateTime? cancelledAt;

  bool get isPending => status == 'pending';
  bool get isDigitalOrder => isDigital ?? false;
  bool get isServiceOrder => isService ?? false;
  bool get isHardwareOrder => !isDigitalOrder && !isServiceOrder;
  bool get isCreditCard => paymentMethod == 'credit_card';
  bool get isEWallet => paymentMethod == 'ewallet';
  bool get isBankTransfer => paymentMethod == 'bank_transfer' || (!isCreditCard && !isEWallet);

  static DateTime? _parseDate(Object? value) => value == null ? null : DateTime.parse(value as String);
}

/// Hasil endpoint yang menggunakan pagination Laravel (data + meta).
class Paginated<T> {
  const Paginated({required this.items, required this.currentPage, required this.lastPage});

  factory Paginated.fromJson(Map<String, dynamic> json, T Function(Map<String, dynamic>) fromJson) {
    final meta = json['meta'] as Map<String, dynamic>;

    return Paginated(
      items: (json['data'] as List<dynamic>).map((item) => fromJson(item as Map<String, dynamic>)).toList(),
      currentPage: meta['current_page'] as int,
      lastPage: meta['last_page'] as int,
    );
  }

  final List<T> items;
  final int currentPage;
  final int lastPage;

  bool get hasMore => currentPage < lastPage;
}

class OrderMessage {
  const OrderMessage({
    required this.id,
    this.orderId,
    this.userId,
    required this.senderName,
    required this.senderType,
    required this.message,
    required this.isMe,
    this.readAt,
    required this.createdAt,
    required this.createdAtHuman,
  });

  factory OrderMessage.fromJson(Map<String, dynamic> json) => OrderMessage(
        id: (json['id'] as num?)?.toInt() ?? 0,
        orderId: (json['order_id'] as num?)?.toInt(),
        userId: (json['user_id'] as num?)?.toInt(),
        senderName: (json['sender_name'] as String?) ?? 'User',
        senderType: (json['sender_type'] as String?) ?? 'customer',
        message: (json['message'] as String?) ?? '',
        isMe: json['is_me'] as bool? ?? ((json['sender_type'] as String?) == 'customer'),
        readAt: json['read_at'] as String?,
        createdAt: (json['created_at'] as String?) ?? '',
        createdAtHuman: (json['created_at_human'] as String?) ?? '',
      );

  final int id;
  final int? orderId;
  final int? userId;
  final String senderName;
  final String senderType;
  final String message;
  final bool isMe;
  final String? readAt;
  final String createdAt;
  final String createdAtHuman;

  bool get isCustomer => senderType == 'customer';
  bool get isAdmin => senderType == 'admin';
  bool get isRead => readAt != null;
}

class UserInquiry {
  const UserInquiry({
    required this.id,
    required this.unreadCount,
    required this.product,
    this.latestMessage,
  });

  factory UserInquiry.fromJson(Map<String, dynamic> json) => UserInquiry(
        id: (json['id'] as num?)?.toInt() ?? 0,
        unreadCount: (json['unread_count'] as num?)?.toInt() ?? 0,
        product: Product.fromJson(json['product'] as Map<String, dynamic>),
        latestMessage: json['latest_message'] == null
            ? null
            : InquiryLatestMessage.fromJson(json['latest_message'] as Map<String, dynamic>),
      );

  final int id;
  final int unreadCount;
  final Product product;
  final InquiryLatestMessage? latestMessage;
}

class InquiryLatestMessage {
  const InquiryLatestMessage({
    required this.message,
    required this.senderType,
    required this.isMe,
    required this.timeHuman,
    required this.createdAt,
  });

  factory InquiryLatestMessage.fromJson(Map<String, dynamic> json) => InquiryLatestMessage(
        message: (json['message'] as String?) ?? '',
        senderType: (json['sender_type'] as String?) ?? 'customer',
        isMe: json['is_me'] as bool? ?? false,
        timeHuman: (json['time_human'] as String?) ?? '',
        createdAt: (json['created_at'] as String?) ?? '',
      );

  final String message;
  final String senderType;
  final bool isMe;
  final String timeHuman;
  final String createdAt;
}

