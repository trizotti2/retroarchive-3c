<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate(['status' => ['sometimes', 'required', Rule::in(Order::STATUSES)]]);

        return OrderResource::collection($this->orders->paginateForAdmin($filters));
    }

    public function update(Request $request, string $id): OrderResource
    {
        $data = $request->validate(['status' => ['required', Rule::in(Order::STATUSES)]]);

        return new OrderResource($this->orders->advance($this->positiveId($id), $data['status']));
    }
}
