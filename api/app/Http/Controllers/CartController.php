<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartItemRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateCartItemRequest;

class CartController extends Controller
{
    public function __construct(private CartService $carts)
    {
    }

    public function show(Request $request): CartResource
    {
        $cart = $this->carts->current($request);

        return new CartResource($cart?->load('items.product'));
    }

    public function store(AddCartItemRequest $request): CartResource
    {
        $data = $request->validated();

        $cart = $this->carts->add(
            $request,
            (int) $data['product_id'],
            (int) $data['quantity'],
        );

        return new CartResource($cart->load('items.product'));
    }
    public function update(UpdateCartItemRequest $request, string $id): CartResource
    {
        $data = $request->validated();
        $cart = $this->carts->setQuantity($request, $this->itemId($id), (int) $data['quantity']);

        return new CartResource($cart->load('items.product'));
    }

    public function destroy(Request $request, string $id): CartResource
    {
        $cart = $this->carts->setQuantity($request, $this->itemId($id), 0);

        return new CartResource($cart->load('items.product'));
    }

    private function itemId(string $id): int
    {
        $value = filter_var($id, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        abort_if($value === false, 404);

        return $value;
    }
}