<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'shipping_address' => $this->shipping_address,
            'subtotal_cents' => $this->subtotal_cents,
            'shipping_cents' => $this->shipping_cents,
            'total_cents' => $this->total_cents,
            'created_at' => $this->created_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map->only([
                'product_id',
                'product_name',
                'quantity',
                'unit_price_cents',
                'subtotal_cents',
            ])),
        ];
    }
}
