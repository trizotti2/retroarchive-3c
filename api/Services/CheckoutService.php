<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public const SHIPPING_CENTS = 1500;

    public function placeOrder(User $user, int $addressId): Order
    {
        return DB::transaction(function () use ($user, $addressId) {
            $address = $user->addresses()->findOrFail($addressId);

            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            $items = $cart?->items()
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            if ($items === null || $items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Seu carrinho está vazio.',
                ]);
            }

            $products = Product::withTrashed()
                ->whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0;

            foreach ($items as $item) {
                $product = $products[$item->product_id];

                if ($product->trashed()
                    || ! $product->is_active
                    || $product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "A quantidade solicitada de {$product->name} está indisponível.",
                    ]);
                }

                $lineSubtotal = $product->price_cents * $item->quantity;
                $subtotal += $lineSubtotal;

                $lines[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item->quantity,
                    'unit_price_cents' => $product->price_cents,
                    'subtotal_cents' => $lineSubtotal,
                ];
            }

            $order = Order::query()->create([
                'user_id' => $user->id,
                'status' => Order::PENDING_PAYMENT,
                'shipping_address' => $address->only(Address::FIELDS),
                'subtotal_cents' => $subtotal,
                'shipping_cents' => self::SHIPPING_CENTS,
                'total_cents' => $subtotal + self::SHIPPING_CENTS,
            ]);

            $order->items()->createMany($lines);

            foreach ($items as $item) {
                $products[$item->product_id]->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();

            return $order->load('items');
        }, 3);
    }
}