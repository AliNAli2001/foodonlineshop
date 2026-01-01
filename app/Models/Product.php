<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\InventoryBatch;
use App\Models\InventoryMovement;

class Product extends Model
{
    protected $table = 'products';
    static $i = 0;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'selling_price',
        'max_order_item',
        'featured',
    ];

    protected $casts = [
        'selling_price' => 'decimal:3',
        'featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all inventory records for this product (multiple rows per product with different expiry dates).
     */
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class, 'product_id');
    }

    /**
     * Get all inventory transactions for this product.
     */
    // public function transactions()
    // {
    //     return $this->hasManyThrough(
    //         InventoryTransaction::class, // Target model
    //         Inventory::class,           // Intermediate model
    //         'product_id',               // Foreign key on Inventory table
    //         'inventory_id',             // Foreign key on InventoryTransaction table
    //         'id',                       // Local key on Product table
    //         'id'                        // Local key on Inventory table
    //     );
    // }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'product_id');
    }


    /**
     * Get all images for this product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * Get the primary image for this product.
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class, 'product_id')->where('is_primary', true);
    }

    /**
     * Get all categories this product belongs to.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id');
    }

    /**
     * Get all order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    /**
     * Get the name in the specified language.
     */
    public function getName(string $language = 'en'): string
    {
        return $language === 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * Get the description in the specified language.
     */
    public function getDescription(string $language = 'en'): string
    {
        return $language === 'ar' ? $this->description_ar : $this->description_en;
    }

    /**
     * Get total available stock quantity across all expiry dates (excluding expired).
     */
    /**
     * Get total available stock quantity across all expiry dates (excluding expired).
     */
    public function getTotalAvailableStockAttribute(): int
    {
        // Use the loaded inventories relationship if available
        $inventories = $this->relationLoaded('inventoryBatches') ? $this->getRelation('inventoryBatches') : $this->inventoryBatches()->get();

        return $inventories
            ->filter(function ($inventory) {
                return is_null($inventory->expiry_date) || $inventory->expiry_date >= now()->toDate();
            })
            ->sum(function ($inventory) {
                return $inventory->stock_quantity - $inventory->reserved_quantity;
            });
    }

    /**
     * Get total reserved stock quantity across all expiry dates (excluding expired).
     */
    public function getTotalReservedStockAttribute(): int
    {
        // Use the loaded inventories relationship if available
        $inventories = $this->getRelation('inventoryBatches') ?? $this->inventoryBatches()->get();

        return $inventories
            ->filter(function ($inventory) {
                return is_null($inventory->expiry_date) || $inventory->expiry_date >= now()->toDate();
            })
            ->sum('reserved_quantity');
    }

    /**
     * Get total stock quantity across all expiry dates (excluding expired).
     */
    public function getTotalStockAttribute(): int
    {

        // Use the loaded inventories relationship if available
        $inventories = $this->relationLoaded('inventoryBatches') ? $this->getRelation('inventoryBatches') : $this->inventoryBatches()->get();
        return $inventories
            ->filter(function ($inventory) {
                return is_null($inventory->expiry_date) || $inventory->expiry_date >= now()->toDate();
            })
            ->sum('stock_quantity');
    }

    /**
     * Get total reserved quantity across all expiry dates. for delete (we optimized on above)
     * optimized version getTotalReservedStockAttribute
     */
    public function getTotalReserved(): int
    {
        return $this->inventories()
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now()->toDate());
            })
            ->sum('reserved_quantity');
    }

    /**
     * Get available stock quantity (for backward compatibility).
     */
    public function getAvailableStock(): int
    {
        return $this->getTotalAvailableStock();
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->getTotalAvailableStock() > 0;
    }

    /**
     * Get inventory sorted by expiry date (FIFO - First In First Out).
     */
    public function getInventoriesByExpiry()
    {
        return $this->inventories()
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now()->toDate());
            })
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Get expired inventory records.
     */
    public function getExpiredInventories()
    {
        return $this->inventories()
            ->where('expiry_date', '<', now()->toDate())
            ->get();
    }
}
