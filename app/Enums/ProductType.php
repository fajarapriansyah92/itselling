<?php

namespace App\Enums;

enum ProductType: string
{
    case Hardware = 'hardware';
    case Software = 'software';
    case Service = 'service';

    public function label(): string
    {
        return match ($this) {
            self::Hardware => 'Hardware',
            self::Software => 'Software / Lisensi',
            self::Service => 'Jasa',
        };
    }

    public function skuPrefix(): string
    {
        return match ($this) {
            self::Hardware => 'HDW',
            self::Software => 'LIC',
            self::Service => 'SRV',
        };
    }

    /**
     * Jasa tidak memiliki stok fisik, sehingga stoknya tidak dibatasi.
     */
    public function tracksStock(): bool
    {
        return $this !== self::Service;
    }
}
