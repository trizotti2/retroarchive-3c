<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const PENDING_PAYMENT = 'pending_payment';

    protected $fillable = [
        'user_id',
        'status',
        'shipping_address',
        'subtotal_cents',
        'shipping_cents',
        'total_cents',
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