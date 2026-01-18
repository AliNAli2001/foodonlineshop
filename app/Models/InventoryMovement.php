<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movements';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'inventory_batch_id',
        'transaction_type',
        'available_change',
        'reserved_change',
        'expiry_date',
        'batch_number',
        'cost_price',

        'reference',
        'reason',
    ];

    protected $casts = [
        'cost_price' => 'decimal:3',
        'expiry_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    const TYPES = [
        'sale' => 'Sale',
        'restock' => 'Restock',
        'reservation' => 'Reservation',
        'adjustment' => 'Adjustment',
        'damaged' => 'Damaged',
    ];

    /**
     * Get the product this movement belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the inventory batch this movement belongs to.
     */
    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    /**
     * Get the damaged goods record associated with this movement.
     */
    public function damagedGoods(): HasOne
    {
        return $this->hasOne(DamagedGoods::class, 'inventory_movement_id');
    }
}