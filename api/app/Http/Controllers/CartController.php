<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $carts)
    {
    }

    public function show(Request $request): CartResource
    {
        return new CartResource($this->carts->current($request)?->load('items.product'));
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $cart = $this->carts->add($request, (int) $data['product_id'], (int) $data['quantity']);

        return (new CartResource($cart->load('items.product')))->response()->setStatusCode(200);
    }

    public function update(UpdateCartItemRequest $request, string $id): CartResource
    {
        $cart = $this->carts->setQuantity($request, $this->positiveId($id), (int) $request->validated('quantity'));

        return new CartResource($cart->load('items.product'));
    }

    public function destroy(Request $request, string $id): CartResource
    {
        $cart = $this->carts->setQuantity($request, $this->positiveId($id), 0);

        return new CartResource($cart->load('items.product'));
    }
}
