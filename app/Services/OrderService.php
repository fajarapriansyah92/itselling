<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Membuat pesanan baru dari keranjang pelanggan dan mengurangi stok produk.
     *
     * @param  array{items: list<array{product_id: int, quantity: int}>, recipient_name: string, recipient_phone: string, shipping_address: string, notes?: ?string, payment_method?: ?string, payment_channel?: ?string}  $data
     *
     * @throws ValidationException
     */
    public function place(User $customer, array $data): Order
    {
        // Pakai database transaction biar kalau ada error di tengah jalan, data gak masuk setengah-setengah
        return DB::transaction(function () use ($customer, $data): Order {
            // Hitung total qty per product_id dari keranjang
            $quantities = collect($data['items'])
                ->groupBy('product_id')
                ->map(fn ($items): int => (int) $items->sum('quantity'));

            // Kunci baris produk (lockForUpdate) supaya stok tidak bentrok kalau ada yang beli bersamaan
            $products = Product::query()
                ->whereKey($quantities->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $errors = [];

            // Cek apakah produk masih aktif dan stoknya cukup
            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);

                if ($product === null || ! $product->is_active) {
                    $errors['items'][] = 'Produk yang dipilih sudah tidak tersedia.';
                } elseif (! $product->hasStockFor($quantity)) {
                    $errors['items'][] = "Stok {$product->name} tidak mencukupi (tersisa {$product->stock}).";
                }
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            // Kalau alamat kosong (misal beli software/lisensi digital), isi keterangan pengiriman digital otomatis
            $shippingAddress = trim($data['shipping_address'] ?? '');
            if ($shippingAddress === '') {
                $isAllDigital = $products->every(fn ($p) => $p->type === ProductType::Software);
                $shippingAddress = $isAllDigital
                    ? "Produk Digital (Dikirim langsung ke akun & email: {$customer->email})"
                    : '-';
            }

            $paymentMethod = $data['payment_method'] ?? 'bank_transfer';
            $paymentChannel = $data['payment_channel'] ?? null;
            $isCreditCard = $paymentMethod === 'credit_card';

            // Generate nomor Virtual Account sesuai bank yang dipilih pembeli
            $vaNumber = null;
            $paymentBank = null;
            if ($paymentMethod === 'bank_transfer' && ! empty($paymentChannel)) {
                $paymentBank = $paymentChannel;
                $vaNumber = self::generateVaNumber($paymentChannel, $data['recipient_phone'] ?? null);
            }

            // Simpan data header pesanan ke tabel orders
            $order = $customer->orders()->create([
                'order_number' => Order::generateOrderNumber(),
                'status' => $isCreditCard ? OrderStatus::Paid : OrderStatus::Pending,
                'total' => 0,
                'payment_method' => $paymentMethod,
                'payment_channel' => $paymentChannel,
                'va_number' => $vaNumber,
                'payment_bank' => $paymentBank,
                'recipient_name' => $data['recipient_name'],
                'recipient_phone' => $data['recipient_phone'],
                'shipping_address' => $shippingAddress,
                'notes' => $data['notes'] ?? null,
                'paid_at' => $isCreditCard ? now() : null,
            ]);

            $total = 0;

            // Simpan rincian barang/jasa yang dibeli ke tabel order_items dan kurangi stok produk
            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);
                $subtotal = (float) $product->price * $quantity;
                $total += $subtotal;

                $item = $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ]);

                // Kalau bayar pakai kartu kredit (langsung lunas) dan produknya software, langsung terbitkan kode lisensi
                if ($isCreditCard && $product->type === ProductType::Software) {
                    $keys = OrderItem::generateLicenseKeys($item->product_name, $item->quantity);
                    $item->update([
                        'license_key' => json_encode($keys),
                    ]);
                }

                // Kurangi stok barang kalau produk punya stok fisik/lisensi
                if ($product->stock !== null) {
                    $product->decrement('stock', $quantity);
                }
            }

            // Update total harga keseluruhan pesanan
            $order->update(['total' => $total]);

            if ($isCreditCard) {
                AdminNotification::notifyPaymentReceived($order);
            }

            return $order->load('items');
        });
    }

    /**
     * Mengubah status pesanan sesuai alur yang diizinkan.
     *
     * @throws ValidationException
     */
    public function changeStatus(Order $order, OrderStatus $status, ?string $trackingNumber = null): Order
    {
        if (! $order->status->canTransitionTo($status)) {
            throw ValidationException::withMessages([
                'status' => "Status tidak dapat diubah dari \"{$order->status->label()}\" ke \"{$status->label()}\".",
            ]);
        }

        return DB::transaction(function () use ($order, $status, $trackingNumber): Order {
            if ($status === OrderStatus::Cancelled) {
                $this->restoreStock($order);
            }

            if (in_array($status, [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Completed], true)) {
                $order->loadMissing('items.product');
                foreach ($order->items as $item) {
                    if ($item->license_key === null && $item->product?->type === ProductType::Software) {
                        $keys = OrderItem::generateLicenseKeys($item->product_name, $item->quantity);
                        $item->update([
                            'license_key' => json_encode($keys),
                        ]);
                    }
                }
            }

            $updateData = [
                'status' => $status,
                'paid_at' => $order->paid_at ?? (in_array($status, [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered, OrderStatus::Completed], true) ? now() : null),
                'completed_at' => $status === OrderStatus::Completed ? ($order->completed_at ?? now()) : $order->completed_at,
                'cancelled_at' => $status === OrderStatus::Cancelled ? ($order->cancelled_at ?? now()) : $order->cancelled_at,
            ];

            if ($status === OrderStatus::Shipped && empty($order->tracking_number)) {
                if ($trackingNumber !== null && trim($trackingNumber) !== '') {
                    $trackingNumber = trim($trackingNumber);
                } else {
                    $couriers = ['JNE', 'J&T', 'SiCepat', 'AnterAja', 'TIKI'];
                    $selectedCourier = $couriers[array_rand($couriers)];
                    $trackingNumber = $selectedCourier.'-'.rand(1000000000, 9999999999);
                }
            }

            if ($trackingNumber !== null && trim($trackingNumber) !== '') {
                $updateData['tracking_number'] = trim($trackingNumber);
            }

            $order->update($updateData);

            return $order;
        });
    }

    private function restoreStock(Order $order): void
    {
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            if ($item->product?->stock !== null) {
                $item->product->increment('stock', $item->quantity);
            }
        }
    }

    /**
     * Buat nomor Virtual Account sesuai kode bank tujuan dan nomor telepon pelanggan.
     */
    public static function generateVaNumber(?string $bank, ?string $phone = null): string
    {
        $cleanPhone = preg_replace('/\D/', '', (string) $phone);
        if (empty($cleanPhone) || strlen($cleanPhone) < 8) {
            $cleanPhone = '8123456789';
        }
        $suffix = substr($cleanPhone, -8);
        $bankUpper = strtoupper(trim((string) $bank));

        if (str_contains($bankUpper, 'BCA')) {
            return '12345'.$suffix;
        }
        if (str_contains($bankUpper, 'MANDIRI')) {
            return '70012'.$suffix;
        }
        if (str_contains($bankUpper, 'BNI')) {
            return '9880'.$suffix.'12';
        }
        if (str_contains($bankUpper, 'BRI')) {
            return '10777'.$suffix;
        }
        if (str_contains($bankUpper, 'PERMATA')) {
            return '8778'.$suffix;
        }

        return '9900'.$suffix;
    }
}
