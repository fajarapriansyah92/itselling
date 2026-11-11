<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    /**
     * Dapatkan Snap Token & URL pembayaran Midtrans untuk sebuah pesanan.
     */
    public function snapToken(Request $request, Order $order, MidtransService $midtrans): JsonResponse
    {
        Gate::authorize('view', $order);

        if ($order->status !== OrderStatus::Pending) {
            return response()->json([
                'message' => 'Pesanan ini sudah tidak berstatus menunggu pembayaran.',
                'order' => new OrderResource($order),
            ], 422);
        }

        try {
            $channel = $request->input('payment_channel') ?? $order->payment_channel;
            $force = $request->boolean('force', false);
            $snapData = $midtrans->createSnapTransaction($order, $channel, $force);

            return response()->json([
                'token' => $snapData['token'],
                'redirect_url' => $snapData['redirect_url'],
                'order' => new OrderResource($order->fresh()->load('items')),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error generating Snap Token: '.$e->getMessage());

            return response()->json([
                'message' => 'Gagal menghubungkan ke Midtrans: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sinkronkan status pembayaran pesanan secara realtime dari server Midtrans.
     * Sangat berguna saat testing lokal tanpa ngrok/webhook.
     */
    public function syncStatus(Request $request, Order $order, MidtransService $midtrans): JsonResponse
    {
        Gate::authorize('view', $order);

        // Jika sudah lunas, langsung kembalikan
        if ($order->status === OrderStatus::Paid || $order->status === OrderStatus::Completed || $order->status === OrderStatus::Shipped) {
            return response()->json([
                'status' => $order->status->value,
                'is_paid' => true,
                'order' => new OrderResource($order->load('items')),
            ]);
        }

        $statusData = $midtrans->checkStatus($order->order_number);

        if (! empty($statusData['transaction_status'])) {
            $order = $midtrans->processNotification($statusData) ?? $order;
        }

        $freshOrder = $order->fresh()->load('items');

        return response()->json([
            'status' => $freshOrder->status->value,
            'is_paid' => $freshOrder->status === OrderStatus::Paid,
            'order' => new OrderResource($freshOrder),
        ]);
    }

    /**
     * Webhook notifikasi HTTP POST resmi dari Midtrans.
     */
    public function webhook(Request $request, MidtransService $midtrans): JsonResponse
    {
        $payload = $request->all();

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        // Verifikasi signature keamanan dari Midtrans
        if (! $midtrans->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans Webhook Invalid Signature', $payload);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = $midtrans->processNotification($payload);

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json(['message' => 'Notification processed successfully']);
    }

    /**
     * Endpoint konfirmasi pembayaran Virtual Account secara langsung.
     */
    public function mockPay(Request $request, MidtransService $midtrans): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['nullable', 'string'],
            'va_number' => ['nullable', 'string'],
            'payment_type' => ['nullable', 'string'],
            'bank' => ['nullable', 'string'],
        ]);

        $query = Order::query();
        if (! empty($validated['order_number'])) {
            $query->where('order_number', $validated['order_number']);
        } elseif (! empty($validated['va_number'])) {
            $query->where('va_number', $validated['va_number']);
        } else {
            $query->where('status', OrderStatus::Pending)->latest();
        }

        $order = $query->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan atau sudah dibayar.',
            ], 404);
        }

        $bank = strtolower($validated['bank'] ?? $order->payment_bank ?? 'bca');
        $paymentType = $validated['payment_type'] ?? ($order->va_number ? 'bank_transfer' : 'gopay');

        $paymentPayload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => number_format((float) $order->total, 2, '.', ''),
            'transaction_status' => 'settlement',
            'payment_type' => $paymentType,
            'va_numbers' => [
                [
                    'bank' => $bank,
                    'va_number' => $order->va_number ?? '123456789012',
                ],
            ],
            'settlement_time' => now()->toIso8601String(),
        ];

        $updatedOrder = $midtrans->processNotification($paymentPayload);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diverifikasi!',
            'order_number' => $updatedOrder?->order_number,
            'total' => (float) ($updatedOrder?->total ?? 0),
            'status' => $updatedOrder?->status->value,
        ]);
    }
}
