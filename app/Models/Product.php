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
use App\Models\ProductStock;

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
     * Get the product stock record (aggregated stock information).
     */
    public function stock(): HasOne
    {
        return $this->hasOne(ProductStock::class, 'product_id');
    }

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
     * Get all tags this product belongs to.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tags', 'product_id', 'tag_id');
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
     * Get total available stock quantity (from ProductStock table if available, otherwise calculate).
     */
    public function getTotalAvailableStockAttribute(): int
    {
        // Use ProductStock if relationship is loaded or exists
        if ($this->relationLoaded('stock') && $this->stock) {
            return $this->stock->available_quantity;
        }

        // Try to load from database
        $stock = $this->stock;
        if ($stock) {
            return $stock->available_quantity;
        }

        // Fallback: calculate from inventory batches
        $inventories = $this->relationLoaded('inventoryBatches')
            ? $this->getRelation('inventoryBatches')
            : $this->inventoryBatches()->get();

        return $inventories
            ->filter(function ($inventory) {
                return is_null($inventory->expiry_date) || $inventory->expiry_date >= now()->toDate();
            })
            ->sum(function ($inventory) {
                return $inventory->available_quantity - $inventory->reserved_quantity;
            });
    }

    /**
     * Get total reserved stock quantity (from ProductStock table if available, otherwise calculate).
     */
    public function getTotalReservedStockAttribute(): int
    {
        // Use ProductStock if relationship is loaded or exists
        if ($this->relationLoaded('stock') && $this->stock) {
            return $this->stock->reserved_quantity;
        }

        // Try to load from database
        $stock = $this->stock;
        if ($stock) {
            return $stock->reserved_quantity;
        }

        // Fallback: calculate from inventory batches
        $inventories = $this->relationLoaded('inventoryBatches')
            ? $this->getRelation('inventoryBatches')
            : $this->inventoryBatches()->get();

        return $inventories
            ->filter(function ($inventory) {
                return is_null($inventory->expiry_date) || $inventory->expiry_date >= now()->toDate();
            })
            ->sum('reserved_quantity');
    }

    /**
     * Get total stock quantity (available + reserved).
     */
    public function getTotalStockAttribute(): int
    {
        // Use ProductStock if available
        if ($this->relationLoaded('stock') && $this->stock) {
            return $this->stock->available_quantity + $this->stock->reserved_quantity;
        }

        // Try to load from database
        $stock = $this->stock;
        if ($stock) {
            return $stock->available_quantity + $stock->reserved_quantity;
        }

        // Fallback: calculate from inventory batches
        $inventories = $this->relationLoaded('inventoryBatches')
            ? $this->getRelation('inventoryBatches')
            : $this->inventoryBatches()->get();

        return $inventories
            ->filter(function ($inventory) {
                return is_null($inventory->expiry_date) || $inventory->expiry_date >= now()->toDate();
            })
            ->sum('available_quantity');
    }

    /**
     * Get total reserved quantity across all expiry dates. for delete (we optimized on above)
     * optimized version getTotalReservedStockAttribute
     */
    public function getTotalReserved(): int
    {
        return $this->inventoryBatches()
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
        return $this->getTotalAvailableStockAttribute();
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->getTotalAvailableStockAttribute() > 0;
    }

    /**
     * Get inventory sorted by expiry date (FIFO - First In First Out).
     */
    public function getInventoriesByExpiry()
    {
        return $this->inventoryBatches()
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
        return $this->inventoryBatches()
            ->where('expiry_date', '<', now()->toDate())
            ->get();
    }
}
