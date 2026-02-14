<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class DamagedGoods extends Model
{
    protected $table = 'damaged_goods';

    protected $fillable = [
        'product_id',
        'quantity',

        'inventory_batch_id',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    /**
     * Get all adjustments for this damaged goods record.
     */
    public function adjustment(): MorphOne
    {
        return $this->morphOne(Adjustment::class, 'adjustable');
    }
}
