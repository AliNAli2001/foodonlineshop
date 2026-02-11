<?php

namespace App\Observers;

use App\Models\InventoryBatch;
use App\Models\DamagedGoods;

class InventoryBatchObserver
{
    /**
     * Handle the InventoryBatch "updated" event.
     * When cost_price changes, update related adjustments from damaged goods.
     */
    public function updated(InventoryBatch $batch): void
    {
        // Check if cost_price has changed
        if ($batch->isDirty('cost_price')) {
            // Find all damaged goods records that used this inventory batch
            $damagedGoods = DamagedGoods::where('inventory_batch_id', $batch->id)
                ->where('source', 'inventory')
                ->with('adjustments')
                ->get();

            // Update the adjustment quantity for each damaged goods
            foreach ($damagedGoods as $damaged) {
                // Calculate new loss amount based on updated cost price
                $newLossAmount = $damaged->quantity * $batch->cost_price;

                // Update all adjustments related to this damaged goods
                $damaged->adjustments()
                    ->where('adjustment_type', 'loss')
                    ->update([
                        'quantity' => $newLossAmount,
                        'reason' => 'بضاعة تالفة: ' . $damaged->reason . ' (تم تحديث سعر التكلفة)',
                    ]);
            }
        }
    }
}

