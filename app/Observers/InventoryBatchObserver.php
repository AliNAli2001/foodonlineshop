<?php

namespace App\Observers;

use App\Models\InventoryBatch;
use App\Models\DamagedGoods;
use App\Models\OrderItemBatch;

class InventoryBatchObserver
{
    /**
     * Handle the InventoryBatch "updated" event.
     * When cost_price changes, update related adjustment from damaged goods.
     */
    public function updated(InventoryBatch $batch): void
    {
        // Check if cost_price has changed
        if ($batch->isDirty('cost_price')) {
            // Find all damaged goods records that used this inventory batch
            $damagedGoods = DamagedGoods::where('inventory_batch_id', $batch->id)
                ->where('source', 'inventory')
                ->with('adjustment')
                ->get();

            // Update the adjustment quantity for each damaged goods
            foreach ($damagedGoods as $damaged) {
                // Calculate new loss amount based on updated cost price
                $newLossAmount = $damaged->quantity * $batch->cost_price;

                // Update all adjustments related to this damaged goods
                $damaged->adjustment
                    ->where('adjustment_type', 'loss')
                    ->update([
                        'quantity' => $newLossAmount,
                        'reason' => 'بضاعة تالفة: ' . $damaged->reason . ' (تم تحديث سعر التكلفة)',
                    ]);
            }

            $orderItemBatches = OrderItemBatch::where('inventory_batch_id', $batch->id)
                ->with('orderItem')
                ->get();

            if ($orderItemBatches->isEmpty()) {
                return;
            }

            // Update snapshot cost price
            foreach ($orderItemBatches as $itemBatch) {
                $itemBatch->update([
                    'cost_price' => $batch->cost_price
                ]);
            }

            /* =========================
            * 3️⃣ Recalculate ONLY affected orders
            * ========================= */
            $affectedOrderIds = $orderItemBatches
                ->pluck('orderItem.order_id')
                ->unique()
                ->filter();

            foreach ($affectedOrderIds as $orderId) {

                $newOrderCost = OrderItemBatch::whereHas('orderItem', function ($q) use ($orderId) {
                    $q->where('order_id', $orderId);
                })
                    ->selectRaw('SUM(quantity * cost_price) as total_cost')
                    ->value('total_cost');

                \App\Models\Order::where('id', $orderId)->update([
                    'cost_price' => $newOrderCost ?? 0
                ]);
            }
        }
    }
}
