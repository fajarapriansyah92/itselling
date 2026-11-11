<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    /**
     * Riwayat pesanan milik pelanggan yang sedang login.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request, OrderService $orderService): JsonResponse
    {
        $order = $orderService->place($request->user(), $request->validated());

        AdminNotification::notifyNewOrder($order);

        return (new OrderResource($order))
            ->additional(['message' => 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Order $order): OrderResource
    {
        Gate::authorize('view', $order);

        return new OrderResource($order->load('items'));
    }

    public function simulateStatus(Request $request, Order $order): OrderResource
    {
        Gate::authorize('view', $order);

        $request->validate([
            'status' => ['required', 'string', 'in:pending,paid,processing,shipped,delivered,completed,cancelled'],
        ]);

        $statusEnum = OrderStatus::from($request->input('status'));

        $order->update([
            'status' => $statusEnum,
            'tracking_number' => $statusEnum === OrderStatus::Shipped ? 'RESI-DUMMY-'.mt_rand(100000, 999999) : $order->tracking_number,
            'paid_at' => $statusEnum === OrderStatus::Paid ? now() : $order->paid_at,
            'completed_at' => $statusEnum === OrderStatus::Completed ? now() : $order->completed_at,
        ]);

        if (in_array($statusEnum, [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Completed], true)) {
            $order->loadMissing('items.product');
            foreach ($order->items as $item) {
                if ($item->license_key === null && $item->product?->type === ProductType::Software) {
                    $keys = OrderItem::generateDummyKeys($item->product_name, $item->quantity);
                    $item->update([
                        'license_key' => json_encode($keys),
                    ]);
                }
            }
        }

        if ($statusEnum === OrderStatus::Paid) {
            AdminNotification::notifyPaymentReceived($order);
        }

        return new OrderResource($order->load('items'));
    }

    public function receive(Request $request, Order $order): OrderResource
    {
        Gate::authorize('view', $order);

        if (! in_array($order->status, [OrderStatus::Shipped, OrderStatus::Delivered], true)) {
            abort(400, 'Pesanan belum dalam status pengiriman.');
        }

        $order->update([
            'status' => OrderStatus::Completed,
            'completed_at' => $order->completed_at ?? now(),
        ]);

        AdminNotification::notifyOrderReceived($order);

        return new OrderResource($order->load('items'));
    }

    /**
     * User menyelesaikan pesanan digital (lisensi/software) langsung dari status Paid.
     */
    public function completeDigital(Request $request, Order $order, OrderService $orderService): OrderResource
    {
        Gate::authorize('view', $order);

        if ($order->status !== OrderStatus::Paid) {
            abort(400, 'Pesanan belum dibayar atau sudah diproses.');
        }

        // Pastikan ini memang order digital
        $order->loadMissing('items.product');
        $isDigital = $order->items->every(fn ($item) => optional($item->product)->type === ProductType::Software);

        if (! $isDigital) {
            abort(400, 'Fitur ini hanya untuk pesanan produk digital/lisensi.');
        }

        $order = $orderService->changeStatus($order, OrderStatus::Completed);

        return new OrderResource($order->load('items'));
    }

    /**
     * User menyelesaikan pesanan jasa setelah pengerjaan selesai (status Processing).
     */
    public function completeService(Request $request, Order $order, OrderService $orderService): OrderResource
    {
        Gate::authorize('view', $order);

        if ($order->status !== OrderStatus::Processing) {
            abort(400, 'Pesanan belum dalam tahap pengerjaan.');
        }

        // Pastikan ini memang order jasa
        $order->loadMissing('items.product');
        $isService = $order->items->every(fn ($item) => optional($item->product)->type === ProductType::Service);

        if (! $isService) {
            abort(400, 'Fitur ini hanya untuk pesanan jasa.');
        }

        $order = $orderService->changeStatus($order, OrderStatus::Completed);

        return new OrderResource($order->load('items'));
    }
}
