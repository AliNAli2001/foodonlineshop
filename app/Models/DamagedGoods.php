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
        'inventory_batch_id',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const SOURCES = [
        'inventory' => 'Inventory',
        'external' => 'External',
    ];

    /**
     * Get the product for this damaged goods record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the inventory movement associated with this damaged goods record.
     */
    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }
}