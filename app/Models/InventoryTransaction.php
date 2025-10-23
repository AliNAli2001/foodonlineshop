<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $table = 'inventory_transactions';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'quantity_change',
        'reserved_change',
        'transaction_type',
        'reason',
        'expiry_date',
        'batch_number',
    ];

    protected $casts = [
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
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the damaged goods record associated with this transaction.
     */
    public function damagedGoods()
    {
        return $this->hasOne(DamagedGoods::class, 'inventory_transaction_id');
    }
}

