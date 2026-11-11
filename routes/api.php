<?php

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
use Illuminate\Support\Facades\Route;

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
        ],
    ]);
})->name('api.manifest');

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/', function () {
        return redirect()->route('api.manifest');
    });

    Route::middleware('throttle:10,1')->group(function (): void {
        Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    });

    Route::post('/midtrans/webhook', [PaymentGatewayController::class, 'webhook'])->name('midtrans.webhook');
    Route::post('/midtrans/mock-pay', [PaymentGatewayController::class, 'mockPay'])->name('midtrans.mock-pay');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index'])->name('products.reviews.index');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

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

        Route::get('/orders/{order}/messages', [OrderMessageController::class, 'index'])->name('orders.messages.index');
        Route::post('/orders/{order}/messages', [OrderMessageController::class, 'store'])->name('orders.messages.store');

        Route::get('/products/{product:slug}/messages', [ProductMessageController::class, 'index'])->name('products.messages.index');
        Route::post('/products/{product:slug}/messages', [ProductMessageController::class, 'store'])->name('products.messages.store');

        Route::get('/user/inquiries', [ProductMessageController::class, 'userInquiries'])->name('user.inquiries.index');
        Route::get('/user/inquiries/unread-count', [ProductMessageController::class, 'unreadCount'])->name('user.inquiries.unread-count');

        Route::post('/orders/{order}/reviews', [ProductReviewController::class, 'store'])->name('orders.reviews.store');
    });
});
