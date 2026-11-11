<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'status', 'total', 'payment_method', 'payment_channel', 'recipient_name', 'recipient_phone',
        'shipping_address', 'tracking_number', 'notes', 'payment_proof_path', 'paid_at', 'completed_at', 'cancelled_at',
        'snap_token', 'snap_redirect_url', 'va_number', 'payment_bank', 'qr_code_url', 'payment_gateway_payload',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'payment_gateway_payload' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public static function generateOrderNumber(): string
    {
        return 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class);
    }

    /**
     * @return HasMany<ProductReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Pesanan yang dihitung sebagai penjualan sah (sudah dibayar).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function revenue(Builder $query): void
    {
        $query->whereIn('status', OrderStatus::revenueStatuses());
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function paymentProofUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->payment_proof_path === null
            ? null
            : asset('storage/'.$this->payment_proof_path));
    }
}
