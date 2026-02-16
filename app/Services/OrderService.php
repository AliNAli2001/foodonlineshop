<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\OrderItemBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Brick\Math\BigDecimal;
use Exception;
use Illuminate\Support\Facades\Log;

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
     * Create a new admin order in pending state (requires confirmation).
     */
    public function createAdminOrder(array $validated, int $adminId): Order
    {
        $totalAmount = BigDecimal::zero();
        $totalCostPrice = BigDecimal::zero();

        // 1️⃣ Pre-check stock availability
        foreach ($validated['products'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->stock_available_quantity < $item['quantity']) {
                throw ValidationException::withMessages([
                    'products' => "الكمية المطلوبة غير متوفرة للمنتج {$product->name_ar} ذو الرقم {$item['product_id']}. " .
                        "المتاح: {$product->stock_available_quantity}, " .
                        "المطلوب: {$item['quantity']}",
                ]);
            }

            $totalAmount = $totalAmount->plus(BigDecimal::of($product->selling_price)->multipliedBy($item['quantity']));
        }

        return DB::transaction(function () use ($validated, $totalAmount, $adminId, &$totalCostPrice) {

            // 2️⃣ Create order as pending (requires confirmation)
            $order = Order::create([
                'created_by_admin_id' => $adminId,
                'client_id' => $validated['client_id'] ?? null,
                'client_name' => $validated['client_name'] ?? null,
                'client_phone_number' => $validated['client_phone_number'] ?? null,
                'total_amount' => $totalAmount,
                'cost_price' => 0,
                'status' => 'pending',
                'order_source' => $validated['order_source'],
                'delivery_method' => $validated['delivery_method'],
                'address_details' => $validated['address_details'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'shipping_notes' => $validated['shipping_notes'] ?? null,
                'admin_order_client_notes' => $validated['admin_order_client_notes'] ?? null,
            ]);

            // 3️⃣ Create order items only (no stock deduction on creation)
            foreach ($validated['products'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->selling_price,
                ]);
            }

            return $order;

            // Legacy immediate-deduction flow below is now unreachable.
            // 3️⃣ Process each product
            foreach ($validated['products'] as $item) {

                $product = Product::findOrFail($item['product_id']);
                $quantityToSell = $item['quantity'];
                $unitPrice = $product->selling_price;

                // 3.1️⃣ Create ONE order item (logical line)
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantityToSell,
                    'unit_price' => $unitPrice,
                ]);

                // 3.2️⃣ Get eligible batches (FEFO)
                $batches = $product->inventoryBatches()
                    ->where(function ($q) {
                        $q->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>=', now()->toDateString());
                    })
                    ->where('status', 'active')
                    ->orderByRaw('expiry_date IS NULL DESC')
                    ->orderBy('expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                $totalDeducted = 0;

                foreach ($batches as $batch) {
                    if ($quantityToSell <= 0) break;
                    if ($batch->available_quantity <= 0) continue;

                    $sellFromThisBatch = min($quantityToSell, $batch->available_quantity);

                    // 3.3️⃣ Deduct stock from batch
                    $batch->decrement('available_quantity', $sellFromThisBatch);

                    if ($batch->available_quantity === 0) {
                        $batch->update(['status' => 'depleted']);
                    }

                    // 3.4️⃣ Create pivot record (order_item_batches)
                    OrderItemBatch::create([
                        'order_item_id' => $orderItem->id,
                        'inventory_batch_id' => $batch->id,
                        'quantity' => $sellFromThisBatch,
                        'cost_price' => $batch->cost_price,
                    ]);

                    // 3.5️⃣ Log inventory movement
                    $this->inventoryMovementService->logMovement([
                        'product_id' => $product->id,
                        'inventory_batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'expiry_date' => $batch->expiry_date,
                        'transaction_type' => 'sale',
                        'available_change' => -$sellFromThisBatch,
                        'cost_price' => $batch->cost_price,
                        'reference' => "Admin Order #{$order->id}",
                        'reason' => 'Direct sale by admin',
                    ]);

                    
                    $totalCostPrice = $totalCostPrice->plus(BigDecimal::of($batch->cost_price)->multipliedBy($sellFromThisBatch));
                    $totalDeducted += $sellFromThisBatch;
                    $quantityToSell -= $sellFromThisBatch;
                }

                // 3.6️⃣ Safety check
                if ($quantityToSell > 0) {
                    throw new Exception(
                        "الكمية المطلوبة من المنتج {$product->name_ar} ذو الرقم {$product->id} غير متوفرة."
                    );
                }

                // 3.7️⃣ Update product stock summary
                $this->productStockService->deductStock($product->id, $totalDeducted);
            }

            // 4️⃣ Update total cost price
            $order->update(['cost_price' => $totalCostPrice->toFloat()]);

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

            if ($product->stock_available_quantity < $cartItem['quantity']) {
                throw new Exception(
                    "الكمية المطلوبة غير متوفرة للمنتج {$product->name_ar} ذو الرقم {$productId}. " .
                        "المتاح: {$product->stock_available_quantity}, " .
                        "المطلوب: {$cartItem['quantity']}"
                );
            }
        }

        return DB::transaction(function () use ($client, $validated, $cart) {
            $totalAmount = BigDecimal::zero();

            // Create the order
            $order = Order::create([
                'client_id' => $client->id,
                'client_name' => $client->client_name ?? null,
                'client_phone_number' => $client->client_phone_number ?? null,
                'order_source' => $validated['order_source'],
                'delivery_method' => $validated['delivery_method'],
                'address_details' => $validated['address_details'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'shipping_notes' => $validated['shipping_notes'] ?? null,
                'general_notes' => $validated['general_notes'] ?? null,
                'status' => 'pending',
                'total_amount' => 0,

            ]);

            foreach ($cart as $productId => $cartItem) {
                $quantityRequested = $cartItem['quantity'];

                $product = Product::findOrFail($productId);
                $unitPrice = $product->selling_price;
               $subtotal = BigDecimal::of($unitPrice)->multipliedBy($quantityRequested);
            $totalAmount = $totalAmount->plus($subtotal);

                // Create order item (no batch assigned yet — only on confirmation)
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantityRequested,
                    'unit_price' => $unitPrice,
                ]);
            }

            // Update order totals
            $order->update([
                'total_amount' => $totalAmount->toFloat(),

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

            $totalOrderCost = BigDecimal::zero();

            foreach ($order->items as $item) {

                $product = $item->product;
                $quantityToDeduct = $item->quantity;
                $totalDeducted = 0;

                // Get eligible batches (FEFO)
                $batches = $product->inventoryBatches()
                    ->where(function ($q) {
                        $q->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>=', now()->toDateString());
                    })
                    ->where('status', 'active')
                    ->orderByRaw('expiry_date IS NULL DESC')
                    ->orderBy('expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($batches as $batch) {
                    if ($quantityToDeduct <= 0) break;
                    if ($batch->available_quantity <= 0) continue;

                    $deductAmount = min($quantityToDeduct, $batch->available_quantity);

                    // Deduct from batch
                    $batch->decrement('available_quantity', $deductAmount);

                    if ($batch->available_quantity === 0) {
                        $batch->update(['status' => 'depleted']);
                    }

                    // Create pivot record
                    OrderItemBatch::create([
                        'order_item_id' => $item->id,
                        'inventory_batch_id' => $batch->id,
                        'quantity' => $deductAmount,
                        'cost_price' => $batch->cost_price,
                    ]);

                    // Log inventory movement
                    $this->inventoryMovementService->logMovement([
                        'product_id' => $item->product_id,
                        'inventory_batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'expiry_date' => $batch->expiry_date,
                        'transaction_type' => 'sale',
                        'available_change' => -$deductAmount,
                        'cost_price' => $batch->cost_price,
                        'reference' => "Order #{$order->id} confirmed",
                        'reason' => 'Order confirmed by admin',
                    ]);

                 $totalOrderCost = $totalOrderCost->plus(BigDecimal::of($batch->cost_price)->multipliedBy($deductAmount));
                    $totalDeducted += $deductAmount;
                    $quantityToDeduct -= $deductAmount;
                }

                // Safety check
                if ($quantityToDeduct > 0) {
                    throw new Exception(
                        "Insufficient stock to confirm order for product {$product->name_ar} ({$product->id})."
                    );
                }

                // Update product stock summary
                $this->productStockService->deductStock($product->id, $totalDeducted);
            }

            // Update order status + cost
            $order->update([
                'status' => 'confirmed',
                'cost_price' => $totalOrderCost->toFloat(),
            ]);
        });

        return $order->fresh(['items.batches.inventoryBatch']);
    }


    /**
     * Reject a pending order → release reservation.
     */
    public function rejectOrder(int $orderId, string $reason): Order
    {
        $order = Order::with(['items.product'])->findOrFail($orderId);

        if ($order->status !== 'pending') {
            throw new Exception('الرفض فقط للطلبات المعلقة.');
        }

        $order->update([
            'status' => 'canceled',
            'general_notes' => trim(($order->general_notes ?? '') . "\nRejected: " . $reason),
        ]);

        return $order;
    }

    /**
     * Return stock for canceled / returned confirmed orders.
     */
    public function restockFromOrderItems(
        Order $order,
        string $transactionType,
        string $reasonPrefix
    ): void {

        // Make sure batches are loaded
        $order->loadMissing('items.batches.inventoryBatch');

        foreach ($order->items as $item) {

            if ($item->batches->isEmpty()) {
                throw new Exception("No inventory batches linked for order item {$item->id}.");
            }

            foreach ($item->batches as $itemBatch) {

                $batch = $itemBatch->inventoryBatch;
                $quantity = $itemBatch->quantity;

                // Restore batch quantity
                $batch->increment('available_quantity', $quantity);

                // Reactivate batch if needed
                if ($batch->status === 'depleted') {
                    $batch->update(['status' => 'active']);
                }

                // Log inventory movement
                $this->inventoryMovementService->logMovement([
                    'product_id' => $item->product_id,
                    'inventory_batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date,
                    'transaction_type' => $transactionType, // return / cancel
                    'available_change' => $quantity,
                    'cost_price' => $itemBatch->cost_price, // snapshot
                    'reference' => "Order #{$order->id} {$reasonPrefix}",
                    'reason' => $reasonPrefix,
                ]);

                // Update product stock summary
                $this->productStockService->addStock($item->product_id, $quantity);
            }
        }
    }


    /**
     * Update order status with proper inventory handling.
     */
    public function updateOrderStatus(int $orderId, string $newStatus, ?int $deliveryId = null): Order
    {
        $order = Order::with(['items.product', 'items.batches'])->findOrFail($orderId);

        $previousStatus = $order->status;

        $validTransitions = $this->getValidStatusTransitions($previousStatus, $order);
        if (!in_array($newStatus, $validTransitions)) {
            throw new Exception("Cannot change status from {$previousStatus} to {$newStatus} for this order.");
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
                in_array($previousStatus, ['shipped', 'delivered', 'done']) && $newStatus === 'returned' =>
                $this->restockFromOrderItems($order, 'return', 'returned to inventory'),
                default => null,
            };

            $order->update(['status' => $newStatus]);
        });

        return $order;
    }

    /**
     * Valid status transitions based on order source and delivery method.
     */
    private function getValidStatusTransitions(string $currentStatus, ?Order $order = null): array
    {
        // Base transitions that apply to all orders
        $baseTransitions = [
            'pending' => ['confirmed', 'rejected'],
            'confirmed' => ['done', 'shipped', 'delivered', 'canceled'],
            'shipped' => ['done', 'returned'],
            'delivered' => ['done', 'returned'],
            'done' => ['returned'],
            'canceled' => [],
            'returned' => [],
        ];

        // If no order context, return base transitions
        if (!$order) {
            return $baseTransitions[$currentStatus] ?? [];
        }

        // Transitions for confirmed orders depend on order_source and delivery_method
        if ($currentStatus === 'confirmed') {
            if ($order->order_source === 'inside_city') {
                if ($order->delivery_method === 'hand_delivered') {
                    // hand_delivered: confirmed -> done or canceled
                    return ['done', 'canceled'];
                } elseif ($order->delivery_method === 'delivery') {
                    // delivery: confirmed -> delivered or canceled
                    return ['delivered', 'canceled'];
                }
            } elseif ($order->order_source === 'outside_city') {
                // outside_city: confirmed -> shipped or canceled
                return ['shipped', 'canceled'];
            }
        }

        // Transitions for delivered orders (inside_city with delivery method)
        if ($currentStatus === 'delivered') {
            return ['done', 'returned'];
        }

        // Transitions for shipped orders (outside_city)
        if ($currentStatus === 'shipped') {
            return ['done', 'returned'];
        }

        return $baseTransitions[$currentStatus] ?? [];
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
        $order = $client->orders()->with(['delivery', 'items.product'])->findOrFail($orderId);

        return $order;
    }

    /**
     * Get available status transitions for an order (for UI display).
     */
    public function getAvailableStatusTransitions(Order $order): array
    {
        return $this->getValidStatusTransitions($order->status, $order);
    }
}
