<?php

namespace App\Http\Resources;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = ($this->resource?->items ?? collect())
            ->map(function (CartItem $item): array {
                $product = $item->product;

                return [
                    'id' => $item->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $item->quantity,
                    'stock' => $product->stock,
                    'unit_price_cents' => $product->price_cents,
                    'subtotal_cents' => $product->price_cents * $item->quantity,
                    'available' => ! $product->trashed()
                        && $product->is_active
                        && $product->stock >= $item->quantity,
                ];
            });

        return [
            'items' => $items->values(),
            'total_cents' => $items->sum('subtotal_cents'),
        ];
    }
}