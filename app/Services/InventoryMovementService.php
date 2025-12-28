<?php

namespace App\Services;

use App\Models\InventoryMovement;

class InventoryMovementService
{
    /**
     * Log an inventory movement transaction.
     */
    public function logMovement(array $data): InventoryMovement
    {
        return InventoryMovement::create($data);
    }

    /**
     * Get movements for a product.
     */
    public function getProductMovements(int $productId, int $limit = 15)
    {
        return InventoryMovement::where('product_id', $productId)
            ->orderByDesc('created_at')
            ->paginate($limit);
    }

    /**
     * Get movements for a batch.
     */
    public function getBatchMovements(int $batchId, int $limit = 15)
    {
        return InventoryMovement::where('inventory_batch_id', $batchId)
            ->orderByDesc('created_at')
            ->paginate($limit);
    }
}
