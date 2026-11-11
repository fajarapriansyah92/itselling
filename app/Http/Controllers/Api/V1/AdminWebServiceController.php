<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\ProductInquiryMessage;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminWebServiceController extends Controller
{
    /**
     * Ringkasan KPI Dashboard Web Admin via RESTful Web Service.
     */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'service' => 'IT Selling RESTful Web Service',
            'platform' => 'Web Control Panel (Admin)',
            'data' => [
                'revenue_this_month' => (int) Order::revenue()->where('created_at', '>=', now()->startOfMonth())->sum('total'),
                'revenue_total' => (int) Order::revenue()->sum('total'),
                'pending_order_count' => Order::where('status', OrderStatus::Pending)->count(),
                'in_progress_order_count' => Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Processing])->count(),
                'customer_count' => User::where('role', UserRole::Customer)->count(),
                'active_product_count' => Product::active()->count(),
                'unread_notifications' => AdminNotification::whereNull('read_at')->count(),
                'unread_chats' => OrderMessage::where('sender_type', 'customer')->whereNull('read_at')->count()
                    + ProductInquiryMessage::where('sender_type', 'customer')->whereNull('read_at')->count(),
            ],
        ]);
    }

    /**
     * Daftar Produk & Jasa untuk Web Admin via RESTful Web Service.
     */
    public function products(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('search')->trim()->toString().'%';
                $query->where(fn ($q) => $q->where('name', 'like', $keyword)->orWhere('sku', 'like', $keyword)->orWhere('brand', 'like', $keyword));
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'service' => 'IT Selling RESTful Web Service',
            'platform' => 'Web Control Panel (Admin)',
            'total' => $products->total(),
            'data' => $products->items(),
        ]);
    }

    /**
     * Daftar Kategori untuk Web Admin via RESTful Web Service.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return response()->json([
            'service' => 'IT Selling RESTful Web Service',
            'platform' => 'Web Control Panel (Admin)',
            'total' => $categories->count(),
            'data' => $categories,
        ]);
    }

    /**
     * Daftar Pesanan untuk Web Admin via RESTful Web Service.
     */
    public function orders(Request $request): JsonResponse
    {
        $status = OrderStatus::tryFrom($request->string('status')->toString());

        $orders = Order::query()
            ->with('user')
            ->withSum('items as items_count', 'quantity')
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15);

        return response()->json([
            'service' => 'IT Selling RESTful Web Service',
            'platform' => 'Web Control Panel (Admin)',
            'total' => $orders->total(),
            'data' => $orders->items(),
        ]);
    }

    /**
     * Daftar Pelanggan untuk Web Admin via RESTful Web Service.
     */
    public function customers(): JsonResponse
    {
        $customers = User::query()
            ->where('role', UserRole::Customer)
            ->withCount('orders')
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'service' => 'IT Selling RESTful Web Service',
            'platform' => 'Web Control Panel (Admin)',
            'total' => $customers->total(),
            'data' => $customers->items(),
        ]);
    }

    /**
     * Daftar Percakapan Chat untuk Web Admin via RESTful Web Service.
     */
    public function chats(): JsonResponse
    {
        return response()->json([
            'service' => 'IT Selling RESTful Web Service',
            'platform' => 'Web Control Panel (Admin)',
            'data' => [
                'order_chats_count' => Order::whereHas('messages')->count(),
                'product_inquiries_count' => ProductInquiry::whereHas('messages')->count(),
                'unread_order_messages' => OrderMessage::where('sender_type', 'customer')->whereNull('read_at')->count(),
                'unread_product_messages' => ProductInquiryMessage::where('sender_type', 'customer')->whereNull('read_at')->count(),
            ],
        ]);
    }

    /**
     * Daftar Ulasan Produk untuk Web Admin via RESTful Web Service.
     */
    public function reviews(): JsonResponse
    {
        return response()->json([
            'service' => 'IT Selling RESTful Web Service',
            'platform' => 'Web Control Panel (Admin)',
            'data' => [
                'total' => ProductReview::count(),
                'average_rating' => round((float) (ProductReview::avg('rating') ?? 0), 1),
                'needs_reply' => ProductReview::whereNull('admin_reply')->count(),
            ],
        ]);
    }

    /**
     * Ringkasan Laporan Penjualan untuk Web Admin via RESTful Web Service.
     */
    public function salesReport(): JsonResponse
    {
        return response()->json([
            'service' => 'IT Selling RESTful Web Service',
            'platform' => 'Web Control Panel (Admin)',
            'data' => [
                'revenue_total' => (int) Order::revenue()->sum('total'),
                'items_sold_total' => (int) OrderItem::whereHas('order', fn ($q) => $q->revenue())->sum('quantity'),
            ],
        ]);
    }
}
