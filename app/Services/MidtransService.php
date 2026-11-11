<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\AdminNotification;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MidtransService
{
    /**
     * Dapatkan server key dari config.
     */
    protected function getServerKey(): string
    {
        return (string) config('midtrans.server_key');
    }

    /**
     * Dapatkan base authorization header untuk request Midtrans.
     */
    protected function getAuthHeader(): string
    {
        return 'Basic '.base64_encode($this->getServerKey().':');
    }

    /**
     * Tentukan metode pembayaran spesifik untuk Midtrans Snap (hanya menampilkan 1 channel yang dipilih pelanggan).
     *
     * @return list<string>|null
     */
    public function resolveEnabledPayments(Order $order, ?string $channelOverride = null): ?array
    {
        $channel = strtolower(trim((string) ($channelOverride ?: $order->payment_channel ?: '')));
        $method = strtolower(trim((string) ($order->payment_method ?: '')));

        if (str_contains($channel, 'bca')) {
            return ['bca_va'];
        }
        if (str_contains($channel, 'bni')) {
            return ['bni_va'];
        }
        if (str_contains($channel, 'bri')) {
            return ['bri_va'];
        }
        if (str_contains($channel, 'mandiri')) {
            return ['echannel'];
        }
        if (str_contains($channel, 'permata')) {
            return ['permata_va'];
        }
        if (str_contains($channel, 'cimb')) {
            return ['cimb_va'];
        }
        if (str_contains($channel, 'danamon') || str_contains($channel, 'jenius')) {
            return ['other_va'];
        }
        if (str_contains($channel, 'gopay')) {
            return ['gopay', 'qris'];
        }
        if (str_contains($channel, 'shopee')) {
            return ['shopeepay', 'qris'];
        }
        if (str_contains($channel, 'qris') || str_contains($channel, 'ovo') || str_contains($channel, 'dana') || str_contains($channel, 'linkaja')) {
            return ['qris', 'gopay', 'shopeepay'];
        }
        if (str_contains($method, 'credit') || str_contains($channel, 'kartu kredit') || str_contains($channel, 'credit')) {
            return ['credit_card'];
        }

        return null;
    }

    /**
     * Buat Snap Transaction Token untuk sebuah pesanan.
     *
     * @return array{token: string, redirect_url: string}
     */
    public function createSnapTransaction(Order $order, ?string $channelOverride = null, bool $force = false): array
    {
        $enabledPayments = $this->resolveEnabledPayments($order, $channelOverride);

        // Jika token sudah pernah dibuat dan status masih pending, serta pilihan channel pembayaran cocok, gunakan yang ada
        if (! $force && ! empty($order->snap_token) && ! empty($order->snap_redirect_url) && $order->status === OrderStatus::Pending) {
            $existingPayload = (array) ($order->payment_gateway_payload ?? []);
            $existingEnabled = $existingPayload['request_payload']['enabled_payments'] ?? null;
            if ($existingEnabled === $enabledPayments) {
                return [
                    'token' => $order->snap_token,
                    'redirect_url' => $order->snap_redirect_url,
                ];
            }
        }

        $order->loadMissing(['items.product', 'user']);

        $itemDetails = [];
        $totalItemsAmount = 0;

        foreach ($order->items as $item) {
            $price = (int) round((float) ($item->price ?? 0));
            $qty = (int) $item->quantity;
            $subtotal = $price * $qty;
            $totalItemsAmount += $subtotal;

            $itemDetails[] = [
                'id' => (string) ($item->product_id ?? $item->id),
                'price' => $price,
                'quantity' => $qty,
                'name' => mb_substr((string) ($item->product_name ?? 'Produk'), 0, 50),
            ];
        }

        $grossAmount = (int) round((float) $order->total);

        // Jika ada selisih pembulatan, sesuaikan item details agar Midtrans tidak menolak
        if ($totalItemsAmount !== $grossAmount) {
            $itemDetails = [
                [
                    'id' => 'ITEM-TOTAL',
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Pesanan '.$order->order_number,
                ],
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => mb_substr((string) ($order->recipient_name ?? $order->user?->name ?? 'Pelanggan'), 0, 50),
                'email' => (string) ($order->user?->email ?? 'customer@example.com'),
                'phone' => (string) ($order->recipient_phone ?? $order->user?->phone ?? '08123456789'),
                'shipping_address' => [
                    'first_name' => mb_substr((string) ($order->recipient_name ?? 'Pelanggan'), 0, 50),
                    'phone' => (string) ($order->recipient_phone ?? '08123456789'),
                    'address' => (string) ($order->shipping_address ?? 'Alamat Pengiriman'),
                ],
            ],
            'callbacks' => [
                'finish' => route('api.v1.orders.show', $order),
            ],
        ];

        if (! empty($enabledPayments)) {
            $payload['enabled_payments'] = $enabledPayments;
        }

        $snapUrl = (string) config('midtrans.snap_url');

        $response = Http::withHeaders([
            'Authorization' => $this->getAuthHeader(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($snapUrl, $payload);

        if (! $response->successful()) {
            Log::error('Midtrans Snap Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order' => $order->order_number,
            ]);

            throw new RuntimeException('Gagal membuat transaksi pembayaran: '.$response->json('error_messages.0', $response->body()));
        }

        $data = $response->json();
        $token = (string) ($data['token'] ?? '');
        $redirectUrl = (string) ($data['redirect_url'] ?? '');
        $data['request_payload'] = $payload;

        $bank = $channelOverride ?: $order->payment_channel;
        $vaNumber = $order->va_number;
        $paymentBank = $order->payment_bank ?: $bank;

        if (empty($vaNumber) && ! empty($bank)) {
            $vaNumber = OrderService::generateVaNumber($bank, $order->recipient_phone);
        }

        $order->update([
            'snap_token' => $token,
            'snap_redirect_url' => $redirectUrl,
            'payment_method' => 'midtrans',
            'payment_channel' => $order->payment_channel ?? ($channelOverride ?: null),
            'va_number' => $vaNumber,
            'payment_bank' => $paymentBank,
            'payment_gateway_payload' => $data,
        ]);

        return [
            'token' => $token,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * Cek status transaksi langsung ke server Midtrans API.
     *
     * @return array<string, mixed>
     */
    public function checkStatus(string $orderNumber): array
    {
        $apiUrl = rtrim((string) config('midtrans.api_url'), '/').'/'.$orderNumber.'/status';

        $response = Http::withHeaders([
            'Authorization' => $this->getAuthHeader(),
            'Accept' => 'application/json',
        ])->get($apiUrl);

        if (! $response->successful()) {
            return [];
        }

        return (array) $response->json();
    }

    /**
     * Validasi signature key dari webhook Midtrans.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->getServerKey());

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Proses notifikasi status transaksi dari Midtrans (baik via Webhook maupun Sync Polling).
     *
     * @param  array<string, mixed>  $notification
     */
    public function processNotification(array $notification): ?Order
    {
        $orderNumber = (string) ($notification['order_id'] ?? '');
        if (empty($orderNumber)) {
            return null;
        }

        $order = Order::where('order_number', $orderNumber)->first();
        if (! $order) {
            return null;
        }

        $transactionStatus = (string) ($notification['transaction_status'] ?? '');
        $fraudStatus = (string) ($notification['fraud_status'] ?? 'accept');
        $paymentType = (string) ($notification['payment_type'] ?? $order->payment_channel ?? 'midtrans');

        // Ekstraksi info VA atau QRIS jika ada
        $vaNumber = null;
        $bank = null;
        if (! empty($notification['va_numbers'][0]['va_number'])) {
            $vaNumber = (string) $notification['va_numbers'][0]['va_number'];
            $bank = (string) ($notification['va_numbers'][0]['bank'] ?? '');
        } elseif (! empty($notification['permata_va_number'])) {
            $vaNumber = (string) $notification['permata_va_number'];
            $bank = 'permata';
        }

        $updates = [
            'payment_method' => 'midtrans',
            'payment_channel' => $paymentType.($bank ? ' ('.strtoupper($bank).')' : ''),
            'payment_gateway_payload' => $notification,
        ];

        if ($vaNumber) {
            $updates['va_number'] = $vaNumber;
            $updates['payment_bank'] = strtoupper((string) $bank);
        }

        $wasPaid = $order->status === OrderStatus::Paid;

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $updates['status'] = OrderStatus::Paid;
                $updates['paid_at'] = $order->paid_at ?? now();
            }
        } elseif ($transactionStatus === 'settlement') {
            $updates['status'] = OrderStatus::Paid;
            $updates['paid_at'] = $order->paid_at ?? now();
        } elseif ($transactionStatus === 'pending') {
            if ($order->status === OrderStatus::Pending) {
                $updates['status'] = OrderStatus::Pending;
            }
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
            if ($order->status !== OrderStatus::Completed && $order->status !== OrderStatus::Shipped) {
                $updates['status'] = OrderStatus::Cancelled;
                $updates['cancelled_at'] = $order->cancelled_at ?? now();
            }
        }

        $order->update($updates);

        // Jika status baru saja berubah jadi Lunas, buat notifikasi admin
        if (! $wasPaid && $order->fresh()->status === OrderStatus::Paid) {
            AdminNotification::create([
                'type' => 'order_payment',
                'title' => 'Pembayaran Otomatis Diterima: '.$order->order_number,
                'message' => 'Pembayaran sebesar Rp '.number_format((float) $order->total, 0, ',', '.').' via '.strtoupper($paymentType).' telah diverifikasi otomatis oleh Midtrans.',
                'link' => route('admin.orders.show', $order),
                'read_at' => null,
            ]);
        }

        return $order->fresh();
    }
}
