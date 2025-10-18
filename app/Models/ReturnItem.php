<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnItem extends Model
{
    protected $table = 'return_items';

    protected $fillable = [
        'order_id',
        'order_item_id',
        'quantity',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order this return belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the order item this return is for.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    /**
     * Get all damaged goods from this return.
     */
    public function damagedGoods(): HasMany
    {
        return $this->hasMany(DamagedGoods::class, 'return_item_id');
    }
}

