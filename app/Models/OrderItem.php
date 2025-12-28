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
        'status',
        'inventory_id'
    ];

    public const STATUSES = [
        'normal' => 'Normal',
        'returned' => 'Returned',
    ];

    protected $casts = [
        'unit_price' => 'decimal:3',
        'subtotal' => 'decimal:3',
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
     * Get all returned items for this order item.
     */
    public function returnedItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'order_item_id');
    }

    /**
     * Get the subtotal for this item.
     */
    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    /**
     * Get the inventory batch for this order item.
     */
    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_id');
    }
}