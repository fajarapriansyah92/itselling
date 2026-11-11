<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = ['type', 'title', 'message', 'link', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Create a "new order placed" notification.
     */
    public static function notifyNewOrder(Order $order): self
    {
        return self::create([
            'type' => 'new_order',
            'title' => 'Pesanan Baru Masuk!',
            'message' => "Pesanan #{$order->order_number} dari {$order->recipient_name} senilai Rp ".number_format($order->total, 0, ',', '.').' telah dibuat.',
            'link' => route('admin.orders.show', $order),
        ]);
    }

    /**
     * Create a "payment received" notification.
     */
    public static function notifyPaymentReceived(Order $order): self
    {
        return self::create([
            'type' => 'payment',
            'title' => 'Pembayaran Diterima!',
            'message' => "Pesanan #{$order->order_number} dari {$order->recipient_name} sudah dibayar. Segera proses pesanan ini.",
            'link' => route('admin.orders.show', $order),
        ]);
    }

    /**
     * Create an "order received by customer" notification.
     */
    public static function notifyOrderReceived(Order $order): self
    {
        return self::create([
            'type' => 'order_received',
            'title' => 'Barang Sudah Diterima Pembeli!',
            'message' => "Pesanan #{$order->order_number} telah dikonfirmasi diterima oleh {$order->recipient_name}. Selesaikan pesanan ini.",
            'link' => route('admin.orders.show', $order),
        ]);
    }
}
