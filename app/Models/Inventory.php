<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'product_id',
        'stock_quantity',
        'reserved_quantity',
        'minimum_alert_quantity',
        'version',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product this inventory belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get all transactions for this inventory.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'product_id', 'product_id');
    }

    /**
     * Get available stock quantity.
     */
    public function getAvailableStock(): int
    {
        return $this->stock_quantity - $this->reserved_quantity;
    }

    /**
     * Check if stock is below minimum alert quantity.
     */
    public function isBelowMinimum(): bool
    {
        return $this->stock_quantity <= $this->minimum_alert_quantity;
    }
}

