<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return OrderResource::collection($request->user()->orders()->latest('id')->paginate(15));
    }

    public function show(Request $request, string $id): OrderResource
    {
        return new OrderResource($this->orders->visibleTo($request->user(), $this->positiveId($id)));
    }

    public function store(StoreOrderRequest $request, CheckoutService $checkout): JsonResponse
    {
        $order = $checkout->placeOrder($request->user(), (int) $request->validated('address_id'));

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function pay(Request $request, string $id): OrderResource
    {
        return new OrderResource($this->orders->pay($request->user(), $this->positiveId($id)));
    }

    public function cancel(Request $request, string $id): OrderResource
    {
        return new OrderResource($this->orders->cancel($request->user(), $this->positiveId($id)));
    }
}
