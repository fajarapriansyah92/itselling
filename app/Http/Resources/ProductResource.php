<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avgRating = $this->reviews_avg_rating ?? ($this->relationLoaded('reviews') ? $this->reviews->avg('rating') : null);
        if ($avgRating === null && $this->exists) {
            $avgRating = $this->reviews()->avg('rating');
        }

        $reviewsCount = $this->reviews_count ?? ($this->relationLoaded('reviews') ? $this->reviews->count() : null);
        if ($reviewsCount === null && $this->exists) {
            $reviewsCount = $this->reviews()->count();
        }

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'brand' => $this->brand,
            'unit' => $this->unit,
            'short_description' => $this->short_description ?: Str::limit(strip_tags((string) $this->description), 200),
            'description' => $this->description,
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'is_available' => $this->hasStockFor(1),
            'rating_average' => round((float) ($avgRating ?? 0), 1),
            'reviews_count' => (int) ($reviewsCount ?? 0),
            'image_url' => $this->image_url,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
