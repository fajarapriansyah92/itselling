<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Pelanggan hanya boleh melihat pesanannya sendiri.
     */
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    /**
     * Bukti bayar hanya bisa diunggah selama pesanan menunggu pembayaran.
     */
    public function uploadPaymentProof(User $user, Order $order): bool
    {
        return $this->view($user, $order) && $order->status === OrderStatus::Pending;
    }

    /**
     * Pelanggan hanya boleh membatalkan pesanan yang belum dibayar.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $this->view($user, $order) && $order->status === OrderStatus::Pending;
    }
}
