<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryBatch;

class InventoryService
{
    protected InventoryMovementService $inventoryMovementService;

    public function __construct(InventoryMovementService $inventoryMovementService)
    {
        $this->inventoryMovementService = $inventoryMovementService;
    }

    /**
     * Create a new inventory batch (restock).
     */
    public function createBatch(int $productId, array $data): InventoryBatch
    {
        $product = Product::findOrFail($productId);

        $batch = InventoryBatch::create([
            'product_id' => $product->id,
            'batch_number' => $data['batch_number'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'available_quantity' => $data['available_quantity'],
            'reserved_quantity' => 0,
            'cost_price' => $data['cost_price'],
            'status' => 'active',
        ]);

        // Log restock movement
        $this->inventoryMovementService->logMovement([
            'product_id' => $product->id,
            'inventory_batch_id' => $batch->id,
            'transaction_type' => 'restock',
            'quantity_change' => $data['available_quantity'],
            'reserved_change' => 0,
            'cost_price' => $data['cost_price'],
            'reason' => $data['reason'] ?? 'New stock received',
            'reference' => "Batch #{$batch->batch_number}",
        ]);

        return $batch;
    }

    /**
     * Update an existing batch (manual adjustment).
     */
    public function updateBatch(int $batchId, array $data): InventoryBatch
    {
        $batch = InventoryBatch::findOrFail($batchId);

        $oldQuantity = $batch->available_quantity;
        $newQuantity = $data['available_quantity'];
        $quantityChange = $newQuantity - $oldQuantity;

        // Update batch details
        $batch->update([
            'batch_number' => $data['batch_number'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'available_quantity' => $newQuantity,
            'cost_price' => $data['cost_price'],
        ]);

        // Only log movement if quantity changed
        if ($quantityChange !== 0) {
            $this->inventoryMovementService->logMovement([
                'product_id' => $batch->product_id,
                'inventory_batch_id' => $batch->id,
                'transaction_type' => 'adjustment',
                'quantity_change' => $quantityChange,
                'reserved_change' => 0,
                'cost_price' => $data['cost_price'],
                'reason' => $data['reason'],
                'reference' => 'Manual stock adjustment',
            ]);
        }

        return $batch;
    }

    /**
     * Get all products with inventory batches.
     */
    public function getAllProductsWithBatches(int $perPage = 10)
    {
        return Product::with(['inventoryBatches' => function ($q) {
            $q->orderByRaw('expiry_date IS NULL ASC')
              ->orderBy('expiry_date', 'asc');
        }])
            ->has('inventoryBatches')
            ->paginate($perPage);
    }

    /**
     * Get batches for a specific product.
     */
    public function getProductBatches(int $productId)
    {
        return Product::with([
            'inventoryBatches' => fn($q) => $q->orderByRaw('expiry_date IS NULL ASC')->orderBy('expiry_date', 'asc'),
            'inventoryMovements' => fn($q) => $q->orderByDesc('created_at')
        ])->findOrFail($productId);
    }

    /**
     * Get a specific batch with details.
     */
    public function getBatch(int $batchId): InventoryBatch
    {
        return InventoryBatch::with('product')->findOrFail($batchId);
    }
}
