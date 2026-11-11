<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function paginateForUser(User $user): LengthAwarePaginator
    {
        return $user->orders()->latest('id')->paginate(15);
    }

    /**
     * @param  array{status?: string}  $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $query = Order::query()->latest('id');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function visibleTo(User $user, int $orderId): Order
    {
        $query = Order::query();
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return $query->with('items')->findOrFail($orderId);
    }

    public function pay(User $user, int $orderId): Order
    {
        return DB::transaction(function () use ($user, $orderId) {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            abort_unless((int) $order->user_id === (int) $user->id, 403, 'Somente o dono pode pagar este pedido.');
            abort_unless($order->status === Order::PENDING_PAYMENT, 409, 'O pedido precisa estar aguardando pagamento.');

            $order->update(['status' => Order::PAID, 'paid_at' => now()]);

            return $order->load('items');
        }, 3);
    }

    public function cancel(User $user, int $orderId): Order
    {
        return DB::transaction(function () use ($user, $orderId) {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            abort_unless(
                $user->role === 'admin' || (int) $order->user_id === (int) $user->id,
                403,
                'Você não tem acesso a este pedido.',
            );
            abort_unless($order->status === Order::PENDING_PAYMENT, 409, 'O pedido precisa estar aguardando pagamento.');

            $items = $order->items()->orderBy('product_id')->get();
            foreach ($items as $item) {
                $product = Product::withTrashed()->lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock', $item->quantity);
            }

            $order->update(['status' => Order::CANCELLED]);

            return $order->setRelation('items', $items);
        }, 3);
    }

    public function advance(int $orderId, string $status): Order
    {
        return DB::transaction(function () use ($orderId, $status) {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            $allowed = match ($order->status) {
                Order::PAID => Order::SHIPPED,
                Order::SHIPPED => Order::DELIVERED,
                default => null,
            };

            abort_unless($allowed === $status, 409, 'Esta transição de status não é permitida.');
            $order->update(['status' => $status]);

            return $order->load('items');
        }, 3);
    }
}
