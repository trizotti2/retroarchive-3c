<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function store(
        StoreOrderRequest $request,
        CheckoutService $checkout,
    ): JsonResponse {
        $data = $request->validated();

        $order = $checkout->placeOrder(
            $request->user(),
            (int) $data['address_id'],
        );

        return (new OrderResource($order))->response()->setStatusCode(201);
    }
}