<?php

namespace App\Models;

use App\Enums\ProductType;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id', 'sku', 'name', 'slug', 'type', 'brand', 'unit',
        'short_description', 'description', 'price', 'stock', 'image_path', 'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->sku)) {
                $product->sku = static::generateSkuForType($product->type);
            }
        });

        static::saving(function (Product $product): void {
            if (blank($product->short_description) && ! blank($product->description)) {
                $product->short_description = Str::limit(strip_tags((string) $product->description), 200);
            }
        });
    }

    /**
     * Tentukan ProductType otomatis berdasarkan nama kategori atau satuan.
     */
    public static function inferTypeFromCategory(?int $categoryId, ?string $unit = null): ProductType
    {
        $categoryName = '';
        if ($categoryId !== null && $categoryId > 0) {
            $categoryName = strtolower((string) Category::whereKey($categoryId)->value('name'));
        }

        $haystack = $categoryName.' '.strtolower((string) $unit);

        if (
            str_contains($haystack, 'jasa') ||
            str_contains($haystack, 'layanan') ||
            str_contains($haystack, 'service') ||
            str_contains($haystack, 'instalasi') ||
            str_contains($haystack, 'maintenance') ||
            str_contains($haystack, 'perbaikan') ||
            str_contains($haystack, 'konsultasi') ||
            str_contains($haystack, 'support') ||
            str_contains($haystack, 'servis')
        ) {
            return ProductType::Service;
        }

        if (
            str_contains($haystack, 'software') ||
            str_contains($haystack, 'lisensi') ||
            str_contains($haystack, 'license') ||
            str_contains($haystack, 'antivirus') ||
            str_contains($haystack, 'digital') ||
            str_contains($haystack, 'aplikasi') ||
            str_contains($haystack, 'cloud')
        ) {
            return ProductType::Software;
        }

        return ProductType::Hardware;
    }

    /**
     * Generate a sequential, unique SKU based on product type.
     */
    public static function generateSkuForType(ProductType|string|null $type): string
    {
        if (is_string($type)) {
            $enumType = ProductType::tryFrom($type) ?? ProductType::Hardware;
        } elseif ($type instanceof ProductType) {
            $enumType = $type;
        } else {
            $enumType = ProductType::Hardware;
        }

        $prefix = $enumType->skuPrefix();

        $highest = static::where('sku', 'like', "{$prefix}-%")
            ->pluck('sku')
            ->map(function ($sku) {
                $parts = explode('-', (string) $sku);

                return isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : 0;
            })
            ->max() ?? 0;

        $nextNumber = max($highest + 1, static::where('sku', 'like', "{$prefix}-%")->count() + 1);
        $sku = sprintf('%s-%03d', $prefix, $nextNumber);

        while (static::where('sku', $sku)->exists()) {
            $nextNumber++;
            $sku = sprintf('%s-%03d', $prefix, $nextNumber);
        }

        return $sku;
    }

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if ($field) {
            return $this->where($field, $value)->first();
        }

        return is_numeric($value)
            ? $this->where('id', $value)->first()
            : $this->where('slug', $value)->first();
    }

    /**
     * @return HasMany<ProductReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * URL publik gambar produk, atau null jika belum ada gambar.
     *
     * @return Attribute<?string, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->image_path === null || trim((string) $this->image_path) === '') {
                return null;
            }

            $path = trim((string) $this->image_path);

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            return asset('storage/'.ltrim($path, '/'));
        });
    }

    /**
     * Stok null berarti tidak dibatasi (misalnya produk jasa).
     */
    public function hasStockFor(int $quantity): bool
    {
        return $this->stock === null || $this->stock >= $quantity;
    }
}
