<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function current(Request $request): ?Cart
    {
        abort_unless($request->hasSession(), 400, 'A sessão do navegador é necessária.');

        $user = $request->user('web');

        if ($user !== null) {
            return Cart::query()->where('user_id', $user->id)->first();
        }

        $cartId = $request->session()->get('cart.guest_id');

        if ($cartId === null) {
            return null;
        }

        return Cart::query()->whereNull('user_id')->find($cartId);
    }

    public function add(Request $request, int $productId, int $quantity): Cart
    {
        $cart = DB::transaction(function () use ($request, $productId, $quantity) {
            $cart = $this->current($request);

            if ($cart === null) {
                $userId = $request->user('web')?->id;

                $cart = $userId === null
                    ? Cart::query()->create()
                    : $this->lockAccountCart($userId);
            } else {
                $cart = Cart::query()->lockForUpdate()->findOrFail($cart->id);
            }
            $cart = Cart::query()->lockForUpdate()->findOrFail($cart->id);
            $product = Product::query()->active()->lockForUpdate()->find($productId);

            if ($product === null) {
                throw ValidationException::withMessages([
                    'product_id' => 'Este produto está indisponível.',
                ]);
            }

            $item = $cart->items()
                ->lockForUpdate()
                ->firstOrNew(['product_id' => $product->id]);
            $newQuantity = ($item->quantity ?? 0) + $quantity;

            if ($newQuantity > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade total no carrinho ultrapassa o estoque.',
                ]);
            }

            $item->quantity = $newQuantity;
            $item->save();

            return $cart;
        }, 3);

        if ($cart->user_id === null) {
            $request->session()->put('cart.guest_id', $cart->id);
        }

        return $cart;
    }
    public function setQuantity(Request $request, int $itemId, int $quantity): Cart
    {
        return DB::transaction(function () use ($request, $itemId, $quantity) {
            $cart = $this->current($request);
            abort_if($cart === null, 404, 'Item não encontrado neste carrinho.');

            $cart = Cart::query()->lockForUpdate()->findOrFail($cart->id);
            $item = $cart->items()->lockForUpdate()->findOrFail($itemId);
            if ($quantity === 0) {
                $item->delete();

                return $cart;
            }

            $product = Product::query()
                ->active()
                ->lockForUpdate()
                ->find($item->product_id);

            if ($product === null || $quantity > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade solicitada está indisponível para este produto.',
                ]);
            }

            $item->quantity = $quantity;
            $item->save();

            return $cart;
        }, 3);
    }
    public function mergeGuest(?int $guestId, int $userId): array
    {
        if ($guestId === null) {
            return [];
        }

        return DB::transaction(function () use ($guestId, $userId) {
            $guest = Cart::query()
                ->whereNull('user_id')
                ->lockForUpdate()
                ->find($guestId);

            if ($guest === null) {
                return [];
            }

            $account = $this->lockAccountCart($userId);
            $adjustments = [];

            $guestItems = $guest->items()
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            foreach ($guestItems as $guestItem) {
                $product = Product::withTrashed()
                    ->lockForUpdate()
                    ->findOrFail($guestItem->product_id);

                $accountItem = $account->items()
                    ->lockForUpdate()
                    ->firstOrNew(['product_id' => $product->id]);

                $requested = ($accountItem->quantity ?? 0) + $guestItem->quantity;
                $stock = $product->is_active && !$product->trashed()
                    ? $product->stock
                    : 0;

                $accepted = min($requested, $stock);

                if ($accepted > 0) {
                    $accountItem->quantity = $accepted;
                    $accountItem->save();
                } elseif ($accountItem->exists) {
                    $accountItem->delete();
                }

                if ($accepted < $requested) {
                    $adjustments[] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'requested' => $requested,
                        'accepted' => $accepted,
                    ];
                }
            }

            $guest->delete();

            return $adjustments;
        }, 3);
    }

    private function lockAccountCart(int $userId): Cart
    {
        Cart::query()->upsert(
            [['user_id' => $userId]],
            ['user_id'],
            ['user_id'],
        );

        return Cart::query()
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}