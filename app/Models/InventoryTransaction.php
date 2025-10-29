<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $table = 'inventory_transactions';

    public $timestamps = false;

    protected $fillable = [
        'inventory_id',
        'quantity_change',
        'reserved_change',
        'transaction_type',
        'reason',
        'expiry_date',
        'batch_number',
        'cost_price',
    ];

    protected $casts = [
        'cost_price' => 'decimal:3',
        'created_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    const TYPES = [
        'sale' => 'Sale',
        'restock' => 'Restock',
        'reservation' => 'Reservation',
        'adjustment' => 'Adjustment',
        'damaged' => 'Damaged',
    ];

    /**
     * Get the product this transaction belongs to.
     */
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,            // Target model
            Inventory::class,          // Intermediate model
            'id',                      // Foreign key on Inventory table
            'id',                      // Foreign key on Product table
            'inventory_id',            // Local key on InventoryTransaction table
            'product_id'               // Local key on Inventory table
        );
    }

    /**
     * Get the damaged goods record associated with this transaction.
     */
    public function damagedGoods()
    {
        return $this->hasOne(DamagedGoods::class, 'inventory_transaction_id');
    }

    /**
     * Get the inventory record associated with this transaction.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}

