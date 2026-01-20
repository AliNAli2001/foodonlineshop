<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBatch extends Model
{
    protected $table = 'inventory_batches';

    protected $fillable = [
        'product_id',
        'batch_number',
        'expiry_date',
        'available_quantity',
        'cost_price',
        'version',
        'inventory_batch_id',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:3',
        'expiry_date' => 'date',
        'available_quantity' => 'integer',
        'version' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product this inventory batch belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get all transactions for this inventory batch.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_batch_id');
    }


    /**
     * Check if inventory batch has expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return now()->toDate() > $this->expiry_date || $this->status === 'expired';
    }

    /**
     * Check if inventory batch is expiring soon (within 7 days).
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        $daysUntilExpiry = now()->diffInDays($this->expiry_date, false);
        return $daysUntilExpiry <= 7 && $daysUntilExpiry > 0;
    }

    /**
     * Get days until expiry.
     */
    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Get all order items for this inventory batch.
     */
    public function orderItemBatches()
    {
        return $this->hasMany(OrderItemBatch::class);
    }
}
