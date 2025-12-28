<?php

namespace App\Services;

use App\Models\ReturnItem;
use App\Models\OrderItem;
use App\Models\Order;

class ReturnService
{
    /**
     * Create a new return.
     */
    public function createReturn(array $data): ReturnItem
    {
        $orderItem = OrderItem::findOrFail($data['order_item_id']);

        if ($data['quantity'] > $orderItem->quantity) {
            throw new \Exception('Return quantity cannot exceed order quantity.');
        }

        return ReturnItem::create($data);
    }

    /**
     * Get all returns.
     */
    public function getAllReturns(int $perPage = 15)
    {
        return ReturnItem::with(['order', 'orderItem.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get a specific return.
     */
    public function getReturn(int $returnId): ReturnItem
    {
        return ReturnItem::with(['order', 'orderItem.product', 'damagedGoods'])
            ->findOrFail($returnId);
    }

    /**
     * Delete a return.
     */
    public function deleteReturn(int $returnId): bool
    {
        $return = ReturnItem::findOrFail($returnId);
        return $return->delete();
    }

    /**
     * Get returns for an order.
     */
    public function getOrderReturns(int $orderId)
    {
        $order = Order::with('items.product')->findOrFail($orderId);
        return $order;
    }
}
