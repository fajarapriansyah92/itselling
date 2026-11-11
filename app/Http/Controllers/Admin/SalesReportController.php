<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    /**
     * Laporan penjualan (pesanan yang sudah dibayar) dalam rentang tanggal tertentu.
     */
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($validated['from'] ?? now()->startOfMonth())->startOfDay();
        $to = Carbon::parse($validated['to'] ?? now())->endOfDay();

        $orders = Order::revenue()
            ->with(['user', 'items.product.category'])
            ->withSum('items as quantity_total', 'quantity')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        $categorySummary = OrderItem::query()
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->whereIn('order_items.order_id', $orders->modelKeys())
            ->selectRaw('categories.name as category_name, SUM(order_items.quantity) as quantity_sold, SUM(order_items.subtotal) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->get();

        $productSummary = OrderItem::query()
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->whereIn('order_items.order_id', $orders->modelKeys())
            ->selectRaw('order_items.product_name, products.sku, products.type, categories.name as category_name, SUM(order_items.quantity) as quantity_sold, SUM(order_items.subtotal) as revenue')
            ->groupBy('order_items.product_name', 'products.sku', 'products.type', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        return view('admin.reports.sales', [
            'from' => $from,
            'to' => $to,
            'orders' => $orders,
            'categorySummary' => $categorySummary,
            'productSummary' => $productSummary,
            'revenueTotal' => $orders->sum('total'),
            'quantityTotal' => (int) $orders->sum('quantity_total'),
        ]);
    }
}
