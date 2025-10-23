<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamagedGoods extends Model
{
    protected $table = 'damaged_goods';

    protected $fillable = [
        'product_id',
        'quantity',
        'source',
        'return_item_id',
        'inventory_transaction_id',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const SOURCES = [
        'inventory' => 'Inventory',
        'external' => 'External',
        'returned' => 'Returned',
    ];

    /**
     * Get the product for this damaged goods record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the return item if this damage is from a return.
     */
    public function returnItem(): BelongsTo
    {
        return $this->belongsTo(ReturnItem::class, 'return_item_id');
    }

    /**
     * Get the inventory transaction associated with this damaged goods record.
     */
    public function inventoryTransaction(): BelongsTo
    {
        return $this->belongsTo(InventoryTransaction::class, 'inventory_transaction_id');
    }
}

