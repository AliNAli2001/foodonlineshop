<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
    ];


    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order this item belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the product for this order item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }



    /**
     * Get the subtotal for this item.
     */
    public function getSubtotal(): float
    {
        return $this->subtotal;
    }


     /**
     * Batches used to fulfill this order item
     */
    public function batches()
    {
        return $this->hasMany(OrderItemBatch::class);
    }

    /**
     * Total fulfilled quantity (safety check)
     */
    public function fulfilledQuantity(): int
    {
        return $this->batches()->sum('quantity');
    }
}