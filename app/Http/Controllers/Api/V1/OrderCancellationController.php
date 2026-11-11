<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Gate;

class OrderCancellationController extends Controller
{
    public function store(Order $order, OrderService $orderService): OrderResource
    {
        Gate::authorize('cancel', $order);

        $orderService->changeStatus($order, OrderStatus::Cancelled);

        return (new OrderResource($order->load('items')))
            ->additional(['message' => 'Pesanan berhasil dibatalkan.']);
    }
}
