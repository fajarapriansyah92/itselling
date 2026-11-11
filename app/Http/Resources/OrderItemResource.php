<?php

namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $review = $this->relationLoaded('review') ? $this->review : $this->review()->first();

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'price' => (float) $this->price,
            'quantity' => $this->quantity,
            'subtotal' => (float) $this->subtotal,
            'image_url' => $this->product?->image_url,
            'license_key' => $this->license_keys[0] ?? $this->license_key,
            'license_keys' => $this->license_keys,
            'is_reviewed' => $review !== null,
            'review' => $review ? [
                'id' => $review->id,
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'admin_reply' => $review->admin_reply,
                'created_at' => $review->created_at?->toIso8601String(),
            ] : null,
        ];
    }
}
