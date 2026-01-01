<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class OrderService
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
     * Create a new admin order (immediately confirmed + stock deducted).
     */
    public function createAdminOrder(array $validated, int $adminId): Order
    {
        $totalAmount = 0;
        $totalCostPrice = 0;

        // Pre-check stock availability
        foreach ($validated['products'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->getTotalAvailableStockAttribute() < $item['quantity']) {
                throw ValidationException::withMessages([
                    'products' => "Insufficient stock for product ID {$item['product_id']}. " .
                                  "Available: {$product->getTotalAvailableStockAttribute()}, " .
                                  "Requested: {$item['quantity']}",
                ]);
            }
            $totalAmount += $product->selling_price * $item['quantity'];
        }

        return DB::transaction(function () use ($validated, $totalAmount, $adminId, &$totalCostPrice) {
            $order = Order::create([
                'created_by_admin_id' => $adminId,
                'client_id' => $validated['client_id'] ?? null,
                'client_name' => $validated['client_name'] ?? null,
                'client_phone_number' => $validated['client_phone_number'] ?? null,
                'total_amount' => $totalAmount,
                'cost_price' => 0,
                'status' => 'confirmed',
                'order_source' => $validated['order_source'],
                'delivery_method' => $validated['delivery_method'],
                'address_details' => $validated['address_details'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'shipping_notes' => $validated['shipping_notes'] ?? null,
                'admin_order_client_notes' => $validated['admin_order_client_notes'] ?? null,
            ]);

            foreach ($validated['products'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantityToSell = $item['quantity'];
                $unitPrice = $product->selling_price;
                $totalDeducted = 0;

                // Get active batches: oldest expiry first (true FIFO)
                $batches = $product->inventoryBatches()
                    ->where(function ($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>=', now()->toDateString());
                    })
                    ->where('status', 'active')
                    ->orderByRaw('expiry_date IS NULL DESC')
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($quantityToSell <= 0) break;

                    $available = $batch->available_quantity - $batch->reserved_quantity;
                    if ($available <= 0) continue;

                    $sellFromThisBatch = min($quantityToSell, $available);

                    // Deduct from stock
                    $batch->decrement('available_quantity', $sellFromThisBatch);

                    // If there was reservation, release it too
                    if ($batch->reserved_quantity > 0) {
                        $releaseReserved = min($sellFromThisBatch, $batch->reserved_quantity);
                        $batch->decrement('reserved_quantity', $releaseReserved);
                    }

                    // Create order item linked to batch
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $sellFromThisBatch,
                        'unit_price' => $unitPrice,
                        'inventory_batch_id' => $batch->id,
                    ]);

                    // Log sale movement
                    $this->inventoryMovementService->logMovement([
                        'product_id' => $item['product_id'],
                        'inventory_batch_id' => $batch->id,
                        'transaction_type' => 'sale',
                        'quantity_change' => -$sellFromThisBatch,
                        'reserved_change' => 0, // nothing reserved in admin order
                        'cost_price' => $batch->cost_price,
                        'reference' => "Admin Order #{$order->id}",
                        'reason' => 'Direct sale by admin',
                    ]);

                    $totalCostPrice += $batch->cost_price * $sellFromThisBatch;
                    $totalDeducted += $sellFromThisBatch;
                    $quantityToSell -= $sellFromThisBatch;
                }

                if ($quantityToSell > 0) {
                    throw new Exception("Could not fulfill full quantity for product {$item['product_id']}.");
                }

                // Update ProductStock for this product
                $this->productStockService->deductStock($item['product_id'], $totalDeducted, false);
            }

            $order->update(['cost_price' => $totalCostPrice]);

            return $order;
        });
    }

    /**
     * Create a client order with inventory reservation.
     */
    public function createClientOrder(array $validated, $client): Order
    {
        // Parse cart data
        $cartData = json_decode($validated['cart_data'], true);
        if (empty($cartData)) {
            throw new Exception('Cart is empty.');
        }

        $cart = [];
        foreach ($cartData as $productId => $item) {
            $cart[$productId] = [
                'quantity' => (int) $item['quantity'],
            ];
        }

        // First: Check overall stock availability
        foreach ($cart as $productId => $cartItem) {
            $product = Product::findOrFail($productId);

            if ($product->getTotalAvailableStockAttribute() < $cartItem['quantity']) {
                throw new Exception(
                    "Insufficient stock for product ID {$productId}. " .
                    "Available: {$product->getTotalAvailableStockAttribute()}, " .
                    "Requested: {$cartItem['quantity']}"
                );
            }
        }

        return DB::transaction(function () use ($client, $validated, $cart) {
            $totalAmount = 0;
            $totalCostPrice = 0;

            // Create the order
            $order = Order::create([
                'client_id' => $client->id,
                'order_source' => $validated['order_source'],
                'delivery_method' => $validated['delivery_method'],
                'address_details' => $validated['address_details'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'shipping_notes' => $validated['shipping_notes'] ?? null,
                'general_notes' => $validated['general_notes'] ?? null,
                'status' => 'pending',
                'total_amount' => 0,
                'cost_price' => 0,
            ]);

            foreach ($cart as $productId => $cartItem) {
                $quantityRequested = $cartItem['quantity'];

                $product = Product::findOrFail($productId);
                $unitPrice = $product->selling_price;
                $subtotal = $unitPrice * $quantityRequested;
                $totalAmount += $subtotal;

                // Create order item (no batch assigned yet — only on confirmation)
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantityRequested,
                    'unit_price' => $unitPrice,
                    'inventory_batch_id' => null,
                ]);

                // Reserve stock using FIFO (oldest expiry first)
                $quantityToReserve = $quantityRequested;
                $totalReserved = 0;
                $batches = $product->inventoryBatches()
                    ->where(function ($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>=', now()->toDateString());
                    })
                    ->where('status', 'active')
                    ->orderByRaw("expiry_date IS NULL DESC")
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($quantityToReserve <= 0) break;

                    $availableInBatch = $batch->available_quantity - $batch->reserved_quantity;
                    if ($availableInBatch <= 0) continue;

                    $reserveThisBatch = min($quantityToReserve, $availableInBatch);

                    // Update batch reserved quantity
                    $batch->increment('reserved_quantity', $reserveThisBatch);

                    // Accumulate cost for profit tracking
                    $totalCostPrice += $batch->cost_price * $reserveThisBatch;

                    // Log movement
                    $this->inventoryMovementService->logMovement([
                        'product_id' => $productId,
                        'inventory_batch_id' => $batch->id,
                        'transaction_type' => 'reservation',
                        'quantity_change' => 0,
                        'reserved_change' => $reserveThisBatch,
                        'cost_price' => $batch->cost_price,
                        'reference' => "Order #{$order->id}",
                        'reason' => 'Order placement - stock reserved',
                    ]);

                    $totalReserved += $reserveThisBatch;
                    $quantityToReserve -= $reserveThisBatch;
                }

                if ($quantityToReserve > 0) {
                    throw new Exception("Failed to reserve sufficient stock for product ID {$productId}.");
                }

                // Update ProductStock: move from available to reserved
                $this->productStockService->reserveStock($productId, $totalReserved);
            }

            // Update order totals
            $order->update([
                'total_amount' => $totalAmount,
                'cost_price' => $totalCostPrice,
            ]);

            return $order;
        });
    }

    /**
     * Confirm a pending order → deduct stock.
     */
    public function confirmOrder(int $orderId): Order
    {
        $order = Order::with(['items.product'])->findOrFail($orderId);

        if ($order->status !== 'pending') {
            throw new Exception('Only pending orders can be confirmed.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product;
                $quantityToDeduct = $item->quantity;
                $totalDeducted = 0;

                $batches = $product->inventoryBatches()
                    ->where(function ($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>=', now()->toDateString());
                    })
                    ->where('status', 'active')
                    ->orderByRaw('expiry_date IS NULL DESC')
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($quantityToDeduct <= 0) break;

                    $available = $batch->available_quantity - $batch->reserved_quantity;
                    $deductAmount = min($quantityToDeduct, max($available, $batch->reserved_quantity));

                    if ($deductAmount > 0) {
                        $batch->decrement('available_quantity', $deductAmount);
                        $batch->decrement('reserved_quantity', $deductAmount);

                        // Link batch to order item if not already
                        if (!$item->inventory_batch_id) {
                            $item->update(['inventory_batch_id' => $batch->id]);
                        }

                        $this->inventoryMovementService->logMovement([
                            'product_id' => $item->product_id,
                            'inventory_batch_id' => $batch->id,
                            'transaction_type' => 'sale',
                            'quantity_change' => -$deductAmount,
                            'reserved_change' => -$deductAmount,
                            'cost_price' => $batch->cost_price,
                            'reference' => "Order #{$order->id} confirmed",
                            'reason' => 'Order confirmed by admin',
                        ]);

                        $totalDeducted += $deductAmount;
                        $quantityToDeduct -= $deductAmount;
                    }
                }

                if ($quantityToDeduct > 0) {
                    throw new Exception("Insufficient stock to confirm order for product {$item->product_id}.");
                }

                // Update ProductStock: deduct from reserved
                $this->productStockService->deductStock($item->product_id, $totalDeducted, true);
            }

            $order->update(['status' => 'confirmed']);
        });

        return $order;
    }

    /**
     * Reject a pending order → release reservation.
     */
    public function rejectOrder(int $orderId, string $reason): Order
    {
        $order = Order::with(['items.product'])->findOrFail($orderId);

        if ($order->status !== 'pending') {
            throw new Exception('Only pending orders can be rejected.');
        }

        DB::transaction(function () use ($order, $reason) {
            foreach ($order->items as $item) {
                $quantityToRelease = $item->quantity;
                $product = $item->product;
                $totalReleased = 0;

                // Release from newest batches first (LIFO for cancellations)
                $batches = $product->inventoryBatches()
                    ->where('reserved_quantity', '>', 0)
                    ->orderByRaw('expiry_date IS NULL ASC')
                    ->orderBy('expiry_date', 'desc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($quantityToRelease <= 0) break;

                    $releaseAmount = min($quantityToRelease, $batch->reserved_quantity);

                    if ($releaseAmount > 0) {
                        $batch->decrement('reserved_quantity', $releaseAmount);

                        $this->inventoryMovementService->logMovement([
                            'product_id' => $item->product_id,
                            'inventory_batch_id' => $batch->id,
                            'transaction_type' => 'adjustment',
                            'quantity_change' => 0,
                            'reserved_change' => -$releaseAmount,
                            'reference' => "Order #{$order->id} rejected",
                            'reason' => $reason,
                        ]);

                        $totalReleased += $releaseAmount;
                        $quantityToRelease -= $releaseAmount;
                    }
                }

                // Update ProductStock: release reserved back to available
                if ($totalReleased > 0) {
                    $this->productStockService->releaseReservedStock($item->product_id, $totalReleased);
                }
            }

            $order->update([
                'status' => 'canceled',
                'general_notes' => trim(($order->general_notes ?? '') . "\nRejected: " . $reason),
            ]);
        });

        return $order;
    }

    /**
     * Return stock for canceled/returned confirmed orders.
     */
    public function restockFromOrderItems(Order $order, string $transactionType, string $reasonPrefix): void
    {
        foreach ($order->items as $item) {
            $batch = $item->inventoryBatch;
            if (!$batch) {
                throw new Exception("No inventory batch linked for order item {$item->id}.");
            }

            $batch->increment('available_quantity', $item->quantity);

            $this->inventoryMovementService->logMovement([
                'product_id' => $item->product_id,
                'inventory_batch_id' => $batch->id,
                'transaction_type' => $transactionType,
                'quantity_change' => $item->quantity,
                'reserved_change' => 0,
                'cost_price' => $batch->cost_price,
                'reference' => "Order #{$order->id} {$reasonPrefix}",
                'reason' => $reasonPrefix,
            ]);

            // Update ProductStock: add back to available
            $this->productStockService->addStock($item->product_id, $item->quantity);
        }
    }

    /**
     * Update order status with proper inventory handling.
     */
    public function updateOrderStatus(int $orderId, string $newStatus, ?int $deliveryId = null): Order
    {
        $order = Order::with(['items.product', 'items.inventoryBatch'])->findOrFail($orderId);

        $previousStatus = $order->status;

        $validTransitions = $this->getValidStatusTransitions($previousStatus);
        if (!in_array($newStatus, $validTransitions)) {
            throw new Exception("Cannot change status from {$previousStatus} to {$newStatus}.");
        }

        DB::transaction(function () use ($order, $newStatus, $previousStatus, $deliveryId) {
            if ($deliveryId) {
                $order->delivery_id = $deliveryId;
                $order->save();
            }

            match (true) {
                $previousStatus === 'pending' && $newStatus === 'confirmed' => $this->confirmOrder($order->id),
                $previousStatus === 'pending' && $newStatus === 'canceled' => $this->rejectOrder($order->id, 'Canceled via status update'),
                in_array($previousStatus, ['confirmed', 'shipped', 'delivered']) && $newStatus === 'canceled' =>
                    $this->restockFromOrderItems($order, 'adjustment', 'canceled via status update'),
                in_array($previousStatus, ['shipped', 'delivered']) && $newStatus === 'returned' =>
                    $this->restockFromOrderItems($order, 'return', 'returned to inventory'),
                default => null,
            };

            $order->update(['status' => $newStatus]);
        });

        return $order;
    }

    /**
     * Valid status transitions.
     */
    private function getValidStatusTransitions(string $currentStatus): array
    {
        return [
            'pending' => ['confirmed', 'canceled'],
            'confirmed' => ['shipped', 'delivered', 'canceled'],
            'shipped' => ['delivered', 'done', 'returned'],
            'delivered' => ['done', 'returned'],
            'done' => [],
            'canceled' => [],
            'returned' => [],
        ][$currentStatus] ?? [];
    }

    /**
     * Assign delivery person to order.
     */
    public function assignDelivery(int $orderId, int $deliveryId): Order
    {
        $order = Order::findOrFail($orderId);
        $order->update(['delivery_id' => $deliveryId]);
        return $order;
    }

    /**
     * Update delivery method.
     */
    public function updateDeliveryMethod(int $orderId, array $data): Order
    {
        $order = Order::findOrFail($orderId);

        $updateData = ['delivery_method' => $data['delivery_method']];
        if ($data['delivery_method'] === 'delivery') {
            $updateData['delivery_id'] = $data['delivery_id'] ?? null;
        }
        if ($data['shipping_notes'] ?? false) {
            $updateData['shipping_notes'] = $data['shipping_notes'];
        }

        $order->update($updateData);
        return $order;
    }

    /**
     * Get all orders.
     */
    public function getAllOrders(int $perPage = 15)
    {
        return Order::with(['client', 'delivery'])->latest()->paginate($perPage);
    }

    /**
     * Get a single order.
     */
    public function getOrder(int $orderId): Order
    {
        return Order::with(['client', 'delivery', 'items.product', 'items.inventoryBatch'])
            ->findOrFail($orderId);
    }

    /**
     * Get client orders.
     */
    public function getClientOrders($client, int $perPage = 10)
    {
        return $client->orders()->latest()->paginate($perPage);
    }

    /**
     * Get single client order.
     */
    public function getClientOrder($client, int $orderId)
    {
        $order = $client->orders()->findOrFail($orderId);
        $items = $order->items()->with(['product', 'inventoryBatch'])->get();

        return [
            'order' => $order,
            'items' => $items,
        ];
    }
}
