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
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate(['status' => ['sometimes', 'required', Rule::in(Order::STATUSES)]]);
        $query = Order::query()->latest('id');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return OrderResource::collection($query->paginate(20)->withQueryString());
    }

    public function update(Request $request, string $id, OrderService $orders): OrderResource
    {
        $data = $request->validate(['status' => ['required', Rule::in(Order::STATUSES)]]);

        return new OrderResource($orders->advance($this->positiveId($id), $data['status']));
    }
}
