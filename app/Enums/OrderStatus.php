<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid => 'Pembayaran Diterima',
            self::Processing => 'Sedang Diproses',
            self::Shipped => 'Sedang Dikirim',
            self::Delivered => 'Pesanan Diterima',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function clientLabel(): string
    {
        return match ($this) {
            self::Paid => 'Pembayaran Berhasil',
            default => $this->label(),
        };
    }

    /**
     * Status yang dihitung sebagai pendapatan (penjualan sah).
     *
     * @return list<self>
     */
    public static function revenueStatuses(): array
    {
        return [self::Paid, self::Processing, self::Shipped, self::Delivered, self::Completed];
    }

    /**
     * Status tujuan yang boleh dipilih admin dari status saat ini.
     *
     * @return list<self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::Pending => [self::Paid, self::Cancelled],
            self::Paid => [self::Processing, self::Completed, self::Cancelled],
            self::Processing => [self::Shipped, self::Completed, self::Cancelled],
            self::Shipped => [self::Delivered, self::Completed, self::Cancelled],
            self::Delivered => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->nextStatuses(), true);
    }
}
