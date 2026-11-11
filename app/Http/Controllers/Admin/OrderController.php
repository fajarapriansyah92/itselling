<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    // Tampilkan daftar semua pesanan masuk dengan filter status & pencarian nomor invoice/nama pembeli
    public function index(Request $request): View
    {
        $status = OrderStatus::tryFrom($request->string('status')->toString());

        $orders = Order::query()
            ->with('user')
            ->withSum('items as items_count', 'quantity')
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('search')->trim()->toString().'%';

                $query->where(fn ($query) => $query
                    ->where('order_number', 'like', $keyword)
                    ->orWhere('recipient_name', 'like', $keyword)
                    ->orWhereRelation('user', 'name', 'like', $keyword));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'currentStatus' => $status,
        ]);
    }

    // Tampilkan halaman detail pesanan, rincian barang, dan riwayat chat pesanan
    public function show(Order $order): View
    {
        // Tandai pesan chat dari pelanggan pada pesanan ini sebagai sudah dibaca admin
        $order->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.orders.show', [
            'order' => $order->load(['user', 'items.product', 'messages.user']),
        ]);
    }

    // Ubah status pesanan (misal dari Dibayar -> Diproses -> Dikirim -> Selesai) dan simpan nomor resi
    public function update(UpdateOrderStatusRequest $request, Order $order, OrderService $orderService): RedirectResponse
    {
        $orderService->changeStatus(
            $order,
            $request->enum('status', OrderStatus::class),
            $request->filled('tracking_number') ? $request->string('tracking_number')->toString() : null
        );

        return back()->with('status', "Status pesanan diubah menjadi \"{$order->status->label()}\".");
    }
}
