<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'product_name', 'price', 'quantity', 'subtotal', 'license_key'];

    protected $appends = ['license_keys'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quantity' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Mengembalikan daftar semua kode lisensi dalam bentuk array string.
     * Mendukung format single key lama maupun JSON array baru.
     *
     * @return list<string>
     */
    public function getLicenseKeysAttribute(): array
    {
        if (empty($this->license_key)) {
            return [];
        }

        // Coba decode JSON
        $decoded = json_decode($this->license_key, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, fn ($k) => is_string($k) && trim($k) !== ''));
        }

        return [trim($this->license_key)];
    }

    /**
     * Buat kode lisensi produk software otomatis sesuai jumlah qty yang dibeli.
     *
     * @return list<string>
     */
    public static function generateLicenseKeys(string $productName, int $quantity = 1): array
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $productName), 0, 4));
        if (strlen($prefix) < 4) {
            $prefix = str_pad($prefix, 4, 'KEY');
        }

        $count = max(1, $quantity);
        $keys = [];
        for ($i = 0; $i < $count; $i++) {
            $keys[] = $prefix.'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        }

        return $keys;
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasOne<ProductReview, $this>
     */
    public function review(): HasOne
    {
        return $this->hasOne(ProductReview::class);
    }
}
