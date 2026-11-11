<?php

namespace App\Http\Resources;

use App\Models\CartItem;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = ($this->resource?->items ?? collect())->map(function (CartItem $item): array {
            $product = $item->product;
            $photoUrl = $product->photoUrl();

            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'photo_url' => $photoUrl,
                'image_url' => $photoUrl,
                'quantity' => $item->quantity,
                'stock' => $product->stock,
                'unit_price_cents' => $product->price_cents,
                'subtotal_cents' => $product->price_cents * $item->quantity,
                'available' => ! $product->trashed() && $product->is_active && $product->stock >= $item->quantity,
            ];
        });

        $subtotal = $items->sum('subtotal_cents');
        $shipping = $items->isEmpty() ? 0 : CheckoutService::SHIPPING_CENTS;

        return [
            'items' => $items->values(),
            'total_cents' => $subtotal,
            'shipping_cents' => $shipping,
            'checkout_total_cents' => $subtotal + $shipping,
        ];
    }
}
