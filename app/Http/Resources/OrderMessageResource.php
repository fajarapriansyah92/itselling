<?php

namespace App\Http\Resources;

use App\Models\OrderMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderMessage
 */
class OrderMessageResource extends JsonResource
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
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'sender_name' => $this->sender_type === 'admin' ? 'Admin IT Selling' : ($this->user?->name ?? 'Customer'),
            'sender_type' => $this->sender_type,
            'message' => $this->message,
            'is_me' => $this->sender_type === 'customer',
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }
}
