<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const PENDING_PAYMENT = 'pending_payment';
    public const PAID = 'paid';
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';

    public const STATUSES = [self::PENDING_PAYMENT, self::PAID, self::SHIPPED, self::DELIVERED, self::CANCELLED];

    protected $fillable = [
        'user_id', 'status', 'shipping_address', 'subtotal_cents', 'shipping_cents', 'total_cents', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'subtotal_cents' => 'integer',
            'shipping_cents' => 'integer',
            'total_cents' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
