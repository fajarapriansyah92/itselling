<?php

use App\Http\Controllers\Api\V1\AdminWebServiceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\OrderCancellationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OrderMessageController;
use App\Http\Controllers\Api\V1\OrderPaymentProofController;
use App\Http\Controllers\Api\V1\PaymentGatewayController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductMessageController;
use App\Http\Controllers\Api\V1\ProductReviewController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Endpoint utama (/api) untuk menampilkan info manifest & dokumentasi singkat Web Service
Route::get('/', function () {
    return response()->json([
        'service' => 'IT Selling RESTful Web Service',
        'status' => 'ONLINE',
        'version' => '1.0',
        'architecture' => 'Client-Server RESTful Web Service',
        'protocol' => 'HTTP/1.1 REST over JSON',
        'server_timestamp' => now()->toIso8601String(),
        'client_targets' => [
            'mobile' => 'Flutter Android Client (Dart)',
            'web' => 'Control Panel & API Monitor',
        ],
        'documentation' => [
            'auth' => [
                'register' => 'POST /api/v1/auth/register',
                'login' => 'POST /api/v1/auth/login',
                'logout' => 'POST /api/v1/auth/logout (Bearer Token)',
            ],
            'catalog' => [
                'products' => 'GET /api/v1/products',
                'categories' => 'GET /api/v1/categories',
                'product_detail' => 'GET /api/v1/products/{slug}',
                'reviews' => 'GET /api/v1/products/{id}/reviews',
            ],
            'orders' => [
                'list' => 'GET /api/v1/orders (Bearer Token)',
                'create' => 'POST /api/v1/orders (Bearer Token)',
                'snap_token' => 'POST /api/v1/orders/{id}/snap-token (Midtrans Gateway)',
            ],
            'admin_web_platform' => [
                'dashboard' => 'GET /api/v1/admin/dashboard',
                'products' => 'GET /api/v1/admin/products',
                'categories' => 'GET /api/v1/admin/categories',
                'orders' => 'GET /api/v1/admin/orders',
                'customers' => 'GET /api/v1/admin/customers',
                'chats' => 'GET /api/v1/admin/chats',
                'reviews' => 'GET /api/v1/admin/reviews',
                'sales_report' => 'GET /api/v1/admin/reports/sales',
            ],
        ],
    ]);
})->name('api.manifest');

// Grup utama endpoint REST API versi 1 (/api/v1/...)
Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/', function () {
        return redirect()->route('api.manifest');
    });

    // Endpoint login & register pelanggan (dibatasi maks 10x per menit biar aman dari brute force)
    Route::middleware('throttle:10,1')->group(function (): void {
        Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    });

    // Endpoint webhook notifikasi pembayaran otomatis dari Midtrans Payment Gateway
    Route::post('/midtrans/webhook', [PaymentGatewayController::class, 'webhook'])->name('midtrans.webhook');
    Route::post('/midtrans/mock-pay', [PaymentGatewayController::class, 'mockPay'])->name('midtrans.mock-pay');

    // Endpoint publik katalog (bisa diakses tanpa token untuk lihat kategori, produk, dan ulasan)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index'])->name('products.reviews.index');

    // Endpoint Keranjang Belanja (Cart Sync Web Service) untuk Mobile Client
    Route::get('/cart', function (Request $request) {
        return response()->json([
            'service' => 'IT Selling RESTful Web Service',
            'endpoint' => '/api/v1/cart',
            'status' => 'synced',
            'items_count' => (int) $request->query('items_count', 0),
            'total_price' => (float) $request->query('total_price', 0),
            'timestamp' => now()->toIso8601String(),
        ]);
    })->name('cart.index');

    Route::post('/cart', function (Request $request) {
        return response()->json([
            'service' => 'IT Selling RESTful Web Service',
            'endpoint' => '/api/v1/cart',
            'action' => $request->input('action', 'update'),
            'product_id' => $request->input('product_id'),
            'quantity' => $request->input('quantity'),
            'items_count' => $request->input('items_count', 0),
            'status' => 'updated',
        ]);
    })->name('cart.sync');

    // Endpoint RESTful Web Service untuk Platform ke-2: Web Control Panel (Admin)
    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', [AdminWebServiceController::class, 'dashboard'])->name('dashboard');
        Route::get('/products', [AdminWebServiceController::class, 'products'])->name('products');
        Route::get('/categories', [AdminWebServiceController::class, 'categories'])->name('categories');
        Route::get('/orders', [AdminWebServiceController::class, 'orders'])->name('orders');
        Route::get('/customers', [AdminWebServiceController::class, 'customers'])->name('customers');
        Route::get('/chats', [AdminWebServiceController::class, 'chats'])->name('chats');
        Route::get('/reviews', [AdminWebServiceController::class, 'reviews'])->name('reviews');
        Route::get('/reports/sales', [AdminWebServiceController::class, 'salesReport'])->name('reports.sales');
    });

    // Endpoint yang wajib pakai Bearer Token (pelanggan harus sudah login di aplikasi Flutter)
    Route::middleware('auth:sanctum')->group(function (): void {
        // Logout & kelola profil user
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Buat pesanan baru, lihat daftar pesanan, pembayaran Midtrans, dan konfirmasi pesanan selesai
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/payment-proof', [OrderPaymentProofController::class, 'store'])->name('orders.payment-proof.store');
        Route::post('/orders/{order}/snap-token', [PaymentGatewayController::class, 'snapToken'])->name('orders.snap-token');
        Route::get('/orders/{order}/sync-payment', [PaymentGatewayController::class, 'syncStatus'])->name('orders.sync-payment');
        Route::post('/orders/{order}/simulate-status', [OrderController::class, 'simulateStatus'])->name('orders.simulate-status');
        Route::post('/orders/{order}/receive', [OrderController::class, 'receive'])->name('orders.receive');
        Route::post('/orders/{order}/complete-digital', [OrderController::class, 'completeDigital'])->name('orders.complete-digital');
        Route::post('/orders/{order}/complete-service', [OrderController::class, 'completeService'])->name('orders.complete-service');
        Route::post('/orders/{order}/cancel', [OrderCancellationController::class, 'store'])->name('orders.cancel');

        // Chat untuk pesanan yang sudah dibuat
        Route::get('/orders/{order}/messages', [OrderMessageController::class, 'index'])->name('orders.messages.index');
        Route::post('/orders/{order}/messages', [OrderMessageController::class, 'store'])->name('orders.messages.store');

        // Chat tanya-tanya produk sebelum membeli (konsultasi produk)
        Route::get('/products/{product:slug}/messages', [ProductMessageController::class, 'index'])->name('products.messages.index');
        Route::post('/products/{product:slug}/messages', [ProductMessageController::class, 'store'])->name('products.messages.store');

        // Daftar riwayat chat produk milik user & jumlah pesan yang belum dibaca
        Route::get('/user/inquiries', [ProductMessageController::class, 'userInquiries'])->name('user.inquiries.index');
        Route::get('/user/inquiries/unread-count', [ProductMessageController::class, 'unreadCount'])->name('user.inquiries.unread-count');

        // Kirim ulasan & rating bintang setelah pesanan selesai
        Route::post('/orders/{order}/reviews', [ProductReviewController::class, 'store'])->name('orders.reviews.store');
    });
});
