<?php

namespace App\Services;

use App\Models\DamagedGoods;
use App\Models\InventoryBatch;
use App\Models\Product;


class DamagedGoodsService
{
    protected InventoryMovementService $inventoryMovementService;
    protected ProductStockService $productStockService;

    public function __construct(
        InventoryMovementService $inventoryMovementService,
        ProductStockService $productStockService
    ) {
        $this->inventoryMovementService = $inventoryMovementService;
        $this->productStockService = $productStockService;
    }

    /**
     * Create a damaged goods record.
     */
    public function createDamagedGoods(array $data): DamagedGoods
    {
        $movementId = null;
        // Verify product exists
        Product::findOrFail($data['product_id']);

        if ($data['source'] === 'inventory') {
            $batch = InventoryBatch::findOrFail($data['inventory_batch_id']);

            if ($batch->available_quantity < $data['quantity']) {
                throw new \Exception(
                    "Insufficient available quantity in the selected batch. " .
                    "Available: {$batch->available_quantity}, Requested: {$data['quantity']}"
                );
            }

            // Deduct from available quantity
            $batch->decrement('available_quantity', $data['quantity']);

            // Create inventory movement record
            $movement = $this->inventoryMovementService->logMovement([
                'product_id' => $data['product_id'],
                'inventory_batch_id' => $batch->id,
                'transaction_type' => 'damaged',
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date,
                'available_change' => -$data['quantity'],
                
                'cost_price' => $batch->cost_price,
                'reason' => $data['reason'],
                'reference' => 'Damaged goods reported',
            ]);

            $movementId = $movement->id;

            // Update ProductStock: deduct from available
            $this->productStockService->deductStock($data['product_id'], $data['quantity'], false);
        }

        // Create damaged goods record
        $damagedGoods = DamagedGoods::create([
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'source' => $data['source'],
            'inventory_batch_id' => $data['source'] === 'inventory' ? $data['inventory_batch_id'] : null,
            'reason' => $data['reason'],
        ]);

       

        return $damagedGoods;
    }

    /**
     * Get all damaged goods records.
     */
    public function getAllDamagedGoods(int $perPage = 15)
    {
        return DamagedGoods::with(['product', 'inventoryBatch'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get a specific damaged goods record.
     */
    public function getDamagedGoods(int $damagedGoodsId): DamagedGoods
    {
        return DamagedGoods::with([
            'product',
            'inventoryBatch'
        ])->findOrFail($damagedGoodsId);
    }

    /**
     * Delete a damaged goods record.
     */
    public function deleteDamagedGoods(int $damagedGoodsId): bool
    {
        $damagedGoods = DamagedGoods::findOrFail($damagedGoodsId);
        return $damagedGoods->delete();
    }

    /**
     * Get available batches for a product.
     */
    public function getAvailableBatches(Product $product)
    {
        return $product->inventoryBatches()
            ->where('available_quantity', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now()->toDateString());
            })
            ->where('status', 'active')
            ->select(['id', 'batch_number', 'expiry_date', 'available_quantity', 'cost_price'])
            ->get();
    }
}
