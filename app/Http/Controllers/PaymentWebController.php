<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebController extends Controller
{
    /**
     * Tampilkan halaman web pembayaran / simulasi untuk pesanan.
     */
    public function show(Order $order): View
    {
        $order->loadMissing(['items.product', 'user']);

        // Jika VA belum digenerate dan pesanan memiliki channel bank, generate sekarang
        if (empty($order->va_number) && ! empty($order->payment_channel)) {
            $order->update([
                'va_number' => OrderService::generateVaNumber($order->payment_channel, $order->recipient_phone),
                'payment_bank' => $order->payment_channel,
            ]);
        }

        return view('payment.pay', [
            'order' => $order,
        ]);
    }

    /**
     * Eksekusi konfirmasi pembayaran pesanan.
     */
    public function pay(Request $request, Order $order): JsonResponse
    {
        if ($order->status === OrderStatus::Paid || $order->status === OrderStatus::Completed || $order->status === OrderStatus::Shipped) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan ini sudah berstatus lunas.',
                'order' => $order,
            ]);
        }

        $order->loadMissing('items.product');

        $vaInput = $request->input('va_number');
        if (! empty($vaInput)) {
            $order->va_number = $vaInput;
        }

        $order->update([
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
            'va_number' => $order->va_number,
        ]);

        // Generate lisensi dummy untuk software
        foreach ($order->items as $item) {
            if ($item->license_key === null && $item->product?->type === ProductType::Software) {
                $keys = OrderItem::generateDummyKeys($item->product_name, $item->quantity);
                $item->update([
                    'license_key' => json_encode($keys),
                ]);
            }
        }

        AdminNotification::notifyPaymentReceived($order);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dikonfirmasi!',
            'order' => $order->fresh(),
        ]);
    }
}
