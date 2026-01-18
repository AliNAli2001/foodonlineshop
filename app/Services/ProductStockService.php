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
                SUM(available_quantity) as total_available
            ')
            ->first();

        $availableQuantity = $totals->total_available ?? 0;

        // Update or create ProductStock record
        return ProductStock::updateOrCreate(
            ['product_id' => $productId],
            [
                'available_quantity' => $availableQuantity,
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
            ['available_quantity' => 0]
        );

        DB::transaction(function () use ($stock, $availableChange, $reservedChange) {
            if ($availableChange !== 0) {
                $stock->increment('available_quantity', $availableChange);
            }
            $stock->refresh();
        });

        return $stock;
    }


    /**
     * Deduct stock (e.g., when order is confirmed/shipped).
     */
    public function deductStock(int $productId, int $quantity): ProductStock
    {
        // Deduct from available quantity
        return $this->updateProductStock($productId, -$quantity);
    }

    /**
     * Add stock (e.g., when new inventory batch is created).
     */
    public function addStock(int $productId, int $quantity): ProductStock
    {
        return $this->updateProductStock($productId, $quantity);
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
    public function getProductStock(int $productId): ProductStock
    {
        $stock = ProductStock::where('product_id', $productId)->first();



        return $stock;
    }

    /**
     * Check if product has sufficient available stock.
     */
    public function hasAvailableStock(int $productId, int $quantity): bool
    {
        $stock = $this->getProductStock($productId);
        if (!$stock) {
            return false;
        }
        return $stock->available_quantity >= $quantity;
    }
}
