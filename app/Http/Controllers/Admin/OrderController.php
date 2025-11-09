<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Show all orders.
     */
    public function index()
    {
        $orders = Order::with(['client', 'delivery'])->latest()->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show create order form.
     */
    public function create()
    {
        $products = Product::all();
        $clients = Client::all();
        $deliveryPersons = Delivery::all();

        return view('admin.orders.create', compact('products', 'clients', 'deliveryPersons'));
    }

    /**
     * Store a new admin-created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'nullable|string|max:255',
            'client_phone_number' => 'nullable|string|max:20',
            'order_source' => 'required|in:inside_city,outside_city',
            'delivery_method' => 'required|in:delivery,shipping,hand_delivered',
            'address_details' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'shipping_notes' => 'nullable|string|max:500',
            'admin_order_client_notes' => 'nullable|string|max:500',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;

        // Calculate total and check stock availability
        foreach ($validated['products'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            // Check if sufficient stock is available
            if ($product->getTotalAvailableStockAttribute() < $item['quantity']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'products' => "Insufficient stock for product ID {$item['product_id']}. Available: {$product->getTotalAvailableStockAttribute()}, Requested: {$item['quantity']}",
                ]);
            }

            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;
        }

        $order = null;
        // Wrap in transaction for atomicity
        DB::transaction(function () use (&$order, $validated, $totalAmount) {
            // Create order with confirmed status
            $order = Order::create([
                'created_by_admin_id' => auth('admin')->id(),
                'client_id' => $validated['client_id'] ?? null,
                'client_name' => $validated['client_name'] ?? null,
                'client_phone_number' => $validated['client_phone_number'] ?? null,
                'total_amount' => $totalAmount,
                'status' => 'confirmed', // Admin orders are confirmed from the beginning
                'order_source' => $validated['order_source'],
                'delivery_method' => $validated['delivery_method'],
                'address_details' => $validated['address_details'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'shipping_notes' => $validated['shipping_notes'] ?? null,
                'admin_order_client_notes' => $validated['admin_order_client_notes'] ?? null,
            ]);

            // Create order items and subtract from stock using FIFO
            foreach ($validated['products'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantityToSubtract = $item['quantity'];
                $inventories = $product->getInventoriesByExpiry(); // Sorted by expiry date ASC (oldest first)

                foreach ($inventories as $inventory) {
                    if ($quantityToSubtract <= 0) {
                        break;
                    }

                    $available = $inventory->stock_quantity - $inventory->reserved_quantity;
                    $subtractAmount = min($quantityToSubtract, $available);

                    if ($subtractAmount > 0) {
                        $inventory->update([
                            'stock_quantity' => $inventory->stock_quantity - $subtractAmount,
                        ]);

                        // Create order item linked to this inventory batch
                        $order->items()->create([
                            'product_id' => $item['product_id'],
                            'quantity' => $subtractAmount,
                            'unit_price' => $product->price,
                            'inventory_id' => $inventory->id,
                        ]);

                        // Log transaction as sale
                        InventoryTransaction::create([
                            'inventory_id' => $inventory->id,
                            'product_id' => $item['product_id'],
                            'quantity_change' => -$subtractAmount,
                            'reserved_change' => 0,
                            'transaction_type' => 'sale',
                            'cost_price' => $inventory->cost_price,
                            'reason' => "Order #{$order->id} created and confirmed by admin",
                            'expiry_date' => $inventory->expiry_date,
                            'batch_number' => $inventory->batch_number,
                        ]);

                        $quantityToSubtract -= $subtractAmount;
                    }
                }

                // If still quantity left (should not happen due to prior check), but just in case
                if ($quantityToSubtract > 0) {
                    throw new \Exception("Failed to subtract full quantity for product ID {$item['product_id']}. Remaining: $quantityToSubtract");
                }
            }
        });

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order created successfully.');
    }

    /**
     * Show order details.
     */
    public function show($orderId)
    {
        $order = Order::with(['client', 'delivery', 'items.product', 'returnedItems'])->findOrFail($orderId);
        $deliveryPersons = Delivery::all();

        return view('admin.orders.show', compact('order', 'deliveryPersons'));
    }

    /**
     * Confirm order.
     */
    public function confirm($orderId)
    {
        $order = Order::with(['items.product'])->findOrFail($orderId);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be confirmed.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product;
                $quantityToDeduct = $item->quantity;

                // Deduct stock using FIFO (by expiry date ascending)
                $inventories = $product->getInventoriesByExpiry();

                foreach ($inventories as $inventory) {
                    if ($quantityToDeduct <= 0) break;

                    $available = $inventory->stock_quantity - $inventory->reserved_quantity;
                    $deductAmount = min($quantityToDeduct, $available > 0 ? $available : $inventory->reserved_quantity);

                    if ($deductAmount > 0) {
                        // Reduce both stock and reserved (if applicable)
                        $inventory->update([
                            'stock_quantity' => max(0, $inventory->stock_quantity - $deductAmount),
                            'reserved_quantity' => max(0, $inventory->reserved_quantity - $deductAmount),
                        ]);

                        InventoryTransaction::create([
                            'inventory_id' => $inventory->id,
                            'product_id' => $item->product_id,
                            'quantity_change' => -$deductAmount,
                            'reserved_change' => -$deductAmount,
                            'transaction_type' => 'sale',
                            'cost_price' => $inventory->cost_price,
                            'reason' => "Order #{$order->id} confirmed by admin",
                            'expiry_date' => $inventory->expiry_date,
                            'batch_number' => $inventory->batch_number,
                        ]);

                        $quantityToDeduct -= $deductAmount;
                    }
                }

                if ($quantityToDeduct > 0) {
                    throw new \Exception("Insufficient stock for product ID {$item->product_id} during confirmation.");
                }
            }

            $order->update(['status' => 'confirmed']);
        });

        return back()->with('success', 'Order confirmed successfully.');
    }


    /**
     * Reject order.
     */
    public function reject(Request $request, $orderId)
    {
        $order = Order::with(['items.product'])->findOrFail($orderId);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be rejected.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($order, $validated) {
            foreach ($order->items as $item) {
                $product = $item->product;
                $quantityToRelease = $item->quantity;

                // Release reserved stock (reverse FIFO - newest first)
                $inventories = $product->inventories()
                    ->where(function ($query) {
                        $query->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>=', now()->toDate());
                    })
                    ->orderBy('expiry_date', 'desc')
                    ->get();

                foreach ($inventories as $inventory) {
                    if ($quantityToRelease <= 0) break;

                    $releaseAmount = min($quantityToRelease, $inventory->reserved_quantity);

                    if ($releaseAmount > 0) {
                        $inventory->update([
                            'reserved_quantity' => max(0, $inventory->reserved_quantity - $releaseAmount),
                        ]);

                        InventoryTransaction::create([
                            'inventory_id' => $inventory->id,
                            'product_id' => $item->product_id,
                            'quantity_change' => 0,
                            'reserved_change' => -$releaseAmount,
                            'transaction_type' => 'adjustment',
                            'reason' => "Order #{$order->id} rejected: {$validated['reason']}",
                            'expiry_date' => $inventory->expiry_date,
                            'batch_number' => $inventory->batch_number,
                        ]);

                        $quantityToRelease -= $releaseAmount;
                    }
                }

                if ($quantityToRelease > 0) {
                    throw new \Exception("Failed to release all reserved quantities for product ID {$item->product_id}.");
                }
            }

            $order->update([
                'status' => 'canceled',
                'general_notes' => trim(($order->general_notes ? $order->general_notes . "\n" : "") . "Rejected: {$validated['reason']}"),
            ]);
        });

        return back()->with('success', 'Order rejected successfully.');
    }

    /**
     * Cancel confirmed/shipped/delivered order → Return to inventory
     */
    private function cancelConfirmedOrder(Order $order, string $reasonSuffix = '')
    {
        foreach ($order->items as $item) {
            $inventory = $item->inventory;
            if (!$inventory) {
                throw new \Exception("Missing inventory for product ID {$item->product_id}.");
            }

            $inventory->update([
                'stock_quantity' => $inventory->stock_quantity + $item->quantity,
            ]);

            InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'product_id' => $item->product_id,
                'quantity_change' => $item->quantity,
                'reserved_change' => 0,
                'transaction_type' => 'adjustment',
                'cost_price' => $inventory->cost_price,
                'reason' => "Order #{$order->id} canceled (returned to stock) {$reasonSuffix}",
                'expiry_date' => $inventory->expiry_date,
                'batch_number' => $inventory->batch_number,
            ]);
        }
    }

    /**
     * Returned order → Return items to inventory (like cancelConfirmedOrder but with 'returned' reason)
     */
    private function returnOrderToInventory(Order $order, string $reasonSuffix = '')
    {
        foreach ($order->items as $item) {
            $inventory = $item->inventory;
            if (!$inventory) {
                throw new \Exception("Missing inventory for product ID {$item->product_id}.");
            }

            $inventory->update([
                'stock_quantity' => $inventory->stock_quantity + $item->quantity,
            ]);

            InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'product_id' => $item->product_id,
                'quantity_change' => $item->quantity,
                'reserved_change' => 0,
                'transaction_type' => 'return',
                'cost_price' => $inventory->cost_price,
                'reason' => "Order #{$order->id} returned to inventory {$reasonSuffix}",
                'expiry_date' => $inventory->expiry_date,
                'batch_number' => $inventory->batch_number,
            ]);
        }
    }


    /**
     * Assign delivery person.
     */
    public function assignDelivery(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $validated = $request->validate([
            'delivery_id' => 'required|exists:delivery,id',
        ]);

        $order->update(['delivery_id' => $validated['delivery_id']]);

        return back()->with('success', 'Delivery person assigned successfully.');
    }

    /**
     * Update order status with validation.
     */
    public function updateStatus(Request $request, $orderId)
    {
        $order = Order::with(['items.product', 'items.inventory'])->findOrFail($orderId);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,done,canceled,returned',
            'delivery_id' => 'nullable|exists:delivery,id',
        ]);

        $newStatus = $validated['status'];
        $previousStatus = $order->status;

        $validTransitions = $this->getValidStatusTransitions($previousStatus);
        if (!in_array($newStatus, $validTransitions)) {
            return back()->with('error', "Cannot transition from {$previousStatus} to {$newStatus}.");
        }

        // Enforce delivery_id if required
        if (in_array($newStatus, ['shipped', 'delivered']) && $order->delivery_method === 'delivery' && $order->order_source === 'inside_city') {
            if (!$order->delivery_id && !isset($validated['delivery_id'])) {
                return back()->with('error', 'A delivery person must be assigned for this delivery method inside the city.');
            }
        }

        DB::transaction(function () use ($request, $order, $validated, $previousStatus, $newStatus) {
            // Update delivery_id if provided
            if (isset($validated['delivery_id'])) {
                $order->delivery_id = $validated['delivery_id'];
                $order->save();
            }

            switch (true) {
                // Pending → Confirmed
                case $previousStatus === 'pending' && $newStatus === 'confirmed':
                    $this->confirm($order->id); // Note: Pass $order->id as per your confirm method
                    break;

                // Pending → Canceled (reject)
                case $previousStatus === 'pending' && $newStatus === 'canceled':
                    $this->reject($request, $order->id); // Note: Pass $order->id as per your reject method
                    break;

                // Confirmed/Shipped/Delivered → Canceled
                case $previousStatus === 'confirmed' && $newStatus === 'canceled':
                    $this->cancelConfirmedOrder($order, "via status update");
                    break;

                // Confirmed/Shipped/Delivered → Returned
                case in_array($previousStatus, ['shipped', 'delivered']) && $newStatus === 'returned':
                    $this->returnOrderToInventory($order, "via status update");
                    break;
            }

            $order->update(['status' => $newStatus]);
        });

        return back()->with('success', "Order status updated to {$newStatus} successfully.");
    }



    /**
     * Get valid status transitions for an order.
     */
    private function getValidStatusTransitions($currentStatus)
    {
        $transitions = [
            'pending' => ['confirmed', 'canceled'],
            'confirmed' => ['shipped', 'delivered', 'canceled'],
            'shipped' => [ 'done', 'returned'],
            'delivered' => ['done', 'returned'],
            'done' => [],
            'canceled' => [],
        ];

        return $transitions[$currentStatus] ?? [];
    }

    /**
     * Update delivery method and shipping notes.
     */
    public function updateDeliveryMethod(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $validated = $request->validate([
            'delivery_method' => 'required|in:delivery,shipping,hand_delivered',
            'delivery_id' => 'nullable|required_if:delivery_method,delivery|exists:delivery,id',
            'shipping_notes' => 'nullable|string|max:500',
        ]);

        $updateData = [
            'delivery_method' => $validated['delivery_method'],
        ];

        if ($validated['delivery_method'] === 'delivery') {
            $updateData['delivery_id'] = $validated['delivery_id'];
        }

        if ($validated['shipping_notes']) {
            $updateData['shipping_notes'] = $validated['shipping_notes'];
        }

        $order->update($updateData);

        return back()->with('success', 'Delivery method updated successfully.');
    }
}
