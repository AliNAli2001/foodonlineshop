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
        'cost_price',
        'expiry_date',
        'batch_number',
    ];

    protected $casts = [
        'cost_price' => 'decimal:3',
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
        'expiry_date' => 'date',
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
        return $this->hasMany(InventoryTransaction::class, 'inventory_id');
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

    /**
     * Check if inventory has expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return now()->toDate() > $this->expiry_date;
    }

    /**
     * Check if inventory is expiring soon (within 7 days).
     */
    public function isExpiringsoon(): bool
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
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}

