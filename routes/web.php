<?php

use App\Http\Controllers\Admin\ApiMonitorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderChatController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\PaymentWebController;
use App\Http\Middleware\LogApiRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Arahkan halaman utama langsung ke dashboard admin
Route::redirect('/', '/admin');

// Route khusus tamu (belum login): tampilkan form login & proses submit login
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});

// Route untuk keluar akun (logout)
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

// Grup route khusus Admin (wajib login, role admin, dan otomatis dicatat ke log Web Service)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', LogApiRequests::class])->group(function (): void {
    // Halaman ringkasan dashboard utama
    Route::get('/', DashboardController::class)->name('dashboard');

    // CRUD kategori produk & jasa
    Route::resource('categories', CategoryController::class)->except('show');

    // Kelola produk, upload/hapus gambar, dan generate kode SKU otomatis
    Route::post('products/{product}/image', [ProductController::class, 'uploadImage'])->name('products.upload-image');
    Route::delete('products/{product}/image', [ProductController::class, 'destroyImage'])->name('products.destroy-image');
    Route::get('products/generate-sku', [ProductController::class, 'generateSku'])->name('products.generate-sku');
    Route::resource('products', ProductController::class)->except('show');

    // Kelola pesanan masuk, data pelanggan, laporan penjualan, dan monitor Web Service
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
    Route::resource('customers', CustomerController::class)->only(['index', 'show']);
    Route::get('/reports/sales', SalesReportController::class)->name('reports.sales');
    Route::get('/api-monitor', [ApiMonitorController::class, 'index'])->name('api-monitor.index');
    Route::post('/api-monitor/clear', [ApiMonitorController::class, 'clear'])->name('api-monitor.clear');

    // Notifications: daftar notifikasi lonceng & tandai sudah dibaca
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::match(['GET', 'POST'], '/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::match(['GET', 'POST'], '/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Order & Product Chats: inbox chat pesanan & konsultasi produk dengan pelanggan
    Route::get('/chats', [OrderChatController::class, 'index'])->name('chats.index');
    Route::get('/chats/product/{inquiry}', [OrderChatController::class, 'showProduct'])->name('chats.product.show');
    Route::post('/chats/product/{inquiry}/reply', [OrderChatController::class, 'replyProduct'])->name('chats.product.reply');
    Route::get('/chats/{order}', [OrderChatController::class, 'show'])->name('chats.show');
    Route::post('/orders/{order}/messages', [OrderChatController::class, 'store'])->name('orders.messages.store');

    // Product Reviews: daftar ulasan bintang dari pembeli & fitur balas ulasan
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// Halaman Pembayaran / Gateway Web untuk Penguji & Pelanggan
Route::get('/pay/{order:order_number}', [PaymentWebController::class, 'show'])->name('payment.web');
Route::post('/pay/{order:order_number}', [PaymentWebController::class, 'pay'])->name('payment.web.pay');

// Fallback route to serve uploaded public storage assets if web server / symlink fails
Route::get('/storage/{path}', function (string $path) {
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.fallback');
