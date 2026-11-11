<?php

namespace App\Http\Resources;

use App\Enums\ProductType;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->clientLabel(),
            'total' => (float) $this->total,
            'payment_method' => $this->payment_method ?? 'bank_transfer',
            'payment_channel' => $this->payment_channel,
            'recipient_name' => $this->recipient_name,
            'recipient_phone' => $this->recipient_phone,
            'shipping_address' => $this->shipping_address,
            'tracking_number' => $this->tracking_number,
            'notes' => $this->notes,
            'payment_proof_url' => $this->payment_proof_url,
            'snap_token' => $this->snap_token,
            'snap_redirect_url' => $this->snap_redirect_url,
            'va_number' => $this->va_number,
            'payment_bank' => $this->payment_bank,
            'qr_code_url' => $this->qr_code_url,
            'items_count' => (int) ($this->items_count ?? $this->items->sum('quantity')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'is_digital' => $this->items->every(fn ($item) => optional($item->product)->type === ProductType::Software),
            'is_service' => $this->items->every(fn ($item) => optional($item->product)->type === ProductType::Service),
        ];
    }
}
