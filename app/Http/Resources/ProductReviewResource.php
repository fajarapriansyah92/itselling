<?php

namespace App\Http\Resources;

use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductReview
 */
class ProductReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name ?? 'Pengguna',
            'order_id' => $this->order_id,
            'order_number' => $this->order?->order_number,
            'order_item_id' => $this->order_item_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name ?? $this->orderItem?->product_name,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'admin_reply' => $this->admin_reply,
            'admin_replied_at' => $this->admin_replied_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
