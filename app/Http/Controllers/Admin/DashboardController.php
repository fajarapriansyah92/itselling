<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'revenueThisMonth' => Order::revenue()->where('created_at', '>=', now()->startOfMonth())->sum('total'),
            'revenueTotal' => Order::revenue()->sum('total'),
            'pendingOrderCount' => Order::where('status', OrderStatus::Pending)->count(),
            'inProgressOrderCount' => Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Processing])->count(),
            'customerCount' => User::where('role', UserRole::Customer)->count(),
            'activeProductCount' => Product::active()->count(),
            'monthlyRevenue' => $this->monthlyRevenue(),
            'topProducts' => OrderItem::query()
                ->whereHas('order', fn ($query) => $query->revenue())
                ->selectRaw('product_id, product_name, SUM(quantity) as quantity_sold, SUM(subtotal) as revenue')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('revenue')
                ->limit(5)
                ->get(),
            'lowStockProducts' => Product::active()->whereNotNull('stock')->where('stock', '<=', 5)->orderBy('stock')->limit(5)->get(),
            'latestOrders' => Order::with('user')->latest()->limit(8)->get(),
        ]);
    }

    /**
     * Total pendapatan per bulan selama 6 bulan terakhir.
     *
     * @return Collection<int, array{label: string, total: float}>
     */
    private function monthlyRevenue(): Collection
    {
        $start = now()->subMonths(5)->startOfMonth();

        $totals = Order::revenue()
            ->where('created_at', '>=', $start)
            ->get(['total', 'created_at'])
            ->groupBy(fn (Order $order): string => $order->created_at->format('Y-m'))
            ->map(fn (Collection $orders): float => (float) $orders->sum('total'));

        return collect(range(0, 5))->map(function (int $offset) use ($start, $totals): array {
            $month = Carbon::parse($start)->addMonths($offset);

            return [
                'label' => $month->translatedFormat('M Y'),
                'total' => $totals->get($month->format('Y-m'), 0.0),
            ];
        });
    }
}
