<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\InventoryBatch;
use Illuminate\Support\Facades\DB;

class ProductStockService
{
    /**
     * Sync ProductStock from all inventory batches for a product.
     * This recalculates the total available and reserved quantities.
     */
    public function syncProductStock(int $productId): ProductStock
    {
        $product = Product::findOrFail($productId);

        // Calculate totals from all non-expired batches
        $totals = $product->inventoryBatches()
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now()->toDate());
            })
            ->where('status', '!=', 'expired')
            ->selectRaw('
                SUM(available_quantity) as total_available,
                SUM(reserved_quantity) as total_reserved
            ')
            ->first();

        $availableQuantity = $totals->total_available ?? 0;
        $reservedQuantity = $totals->total_reserved ?? 0;

        // Update or create ProductStock record
        return ProductStock::updateOrCreate(
            ['product_id' => $productId],
            [
                'available_quantity' => $availableQuantity,
                'reserved_quantity' => $reservedQuantity,
            ]
        );
    }

    /**
     * Update ProductStock by adding/subtracting quantities.
     * This is more efficient than recalculating from all batches.
     */
    public function updateProductStock(
        int $productId,
        int $availableChange = 0,
        int $reservedChange = 0
    ): ProductStock {
        $stock = ProductStock::firstOrCreate(
            ['product_id' => $productId],
            ['available_quantity' => 0, 'reserved_quantity' => 0]
        );

        DB::transaction(function () use ($stock, $availableChange, $reservedChange) {
            if ($availableChange !== 0) {
                $stock->increment('available_quantity', $availableChange);
            }
            if ($reservedChange !== 0) {
                $stock->increment('reserved_quantity', $reservedChange);
            }
            $stock->refresh();
        });

        return $stock;
    }

    /**
     * Reserve stock for an order.
     */
    public function reserveStock(int $productId, int $quantity): ProductStock
    {
        return $this->updateProductStock($productId, -$quantity, $quantity);
    }

    /**
     * Release reserved stock (e.g., when order is cancelled).
     */
    public function releaseReservedStock(int $productId, int $quantity): ProductStock
    {
        return $this->updateProductStock($productId, $quantity, -$quantity);
    }

    /**
     * Deduct stock (e.g., when order is confirmed/shipped).
     */
    public function deductStock(int $productId, int $quantity, bool $fromReserved = false): ProductStock
    {
        if ($fromReserved) {
            // Deduct from reserved quantity only
            return $this->updateProductStock($productId, 0, -$quantity);
        } else {
            // Deduct from available quantity
            return $this->updateProductStock($productId, -$quantity, 0);
        }
    }

    /**
     * Add stock (e.g., when new inventory batch is created).
     */
    public function addStock(int $productId, int $quantity): ProductStock
    {
        return $this->updateProductStock($productId, $quantity, 0);
    }

    /**
     * Sync all products' stock from their inventory batches.
     * Useful for data migration or fixing inconsistencies.
     */
    public function syncAllProductStocks(): int
    {
        $products = Product::has('inventoryBatches')->get();
        $count = 0;

        foreach ($products as $product) {
            $this->syncProductStock($product->id);
            $count++;
        }

        return $count;
    }

    /**
     * Get or create ProductStock for a product.
     */
    public function getOrCreateProductStock(int $productId): ProductStock
    {
        $stock = ProductStock::where('product_id', $productId)->first();

        if (!$stock) {
            return $this->syncProductStock($productId);
        }

        return $stock;
    }

    /**
     * Check if product has sufficient available stock.
     */
    public function hasAvailableStock(int $productId, int $quantity): bool
    {
        $stock = $this->getOrCreateProductStock($productId);
        return $stock->available_quantity >= $quantity;
    }
}

