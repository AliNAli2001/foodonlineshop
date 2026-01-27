<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'minimum_alert_quantity',
        'company_id',
        'category_id',
        'featured',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
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
    //         'inventory_batch_id',             // Foreign key on InventoryTransaction table
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
     * Get category that this product belongs to.
     */


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }



    /**
     * Get all tags this product belongs to.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tags', 'product_id', 'tag_id');
    }

    /**
     * Get product company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
        return ($language === 'ar' ? $this->description_ar : $this->description_en) ?? "";
    }


    /**
     * Get total stock quantity (available + reserved).
     */
    public function getStockAvailableQuantityAttribute(): int
    {
        // Use ProductStock if available
        if ($this->relationLoaded('stock') && $this->stock) {
            return $this->stock->available_quantity;
        }

        // Try to load from database
        $stock = $this->stock;
        if ($stock) {
            return $stock->available_quantity;
        }

        return 0;
    }


    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->getStockAvailableQuantityAttribute() > 0;
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
