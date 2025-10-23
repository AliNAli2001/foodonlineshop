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
            'order_source' => 'required|in:inside_city,outside_city',
            'delivery_method' => 'required|in:delivery,shipping,hand_delivered',
            'address_details' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'shipping_notes' => 'nullable|string|max:500',
            'admin_order_client_notes' => 'nullable|string|max:500',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;
        $orderItems = [];

        // Calculate total and prepare items
        foreach ($validated['products'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;

            $orderItems[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
            ];
        }

        // Create order with confirmed status
        $order = Order::create([
            'created_by_admin_id' => auth('admin')->id(),
            'client_id' => $validated['client_id'] ?? null,
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

        // Create order items
        foreach ($orderItems as $item) {
            $order->items()->create($item);

            // Reserve inventory using FIFO (First In First Out)
            $product = Product::findOrFail($item['product_id']);
            $quantityToReserve = $item['quantity'];
            $inventories = $product->getInventoriesByExpiry(); // Sorted by expiry date

            foreach ($inventories as $inventory) {
                if ($quantityToReserve <= 0) {
                    break;
                }

                $availableToReserve = $inventory->stock_quantity - $inventory->reserved_quantity;
                $reserveAmount = min($quantityToReserve, $availableToReserve);

                if ($reserveAmount > 0) {
                    $inventory->update([
                        'reserved_quantity' => $inventory->reserved_quantity + $reserveAmount,
                    ]);

                    // Log transaction
                    InventoryTransaction::create([
                        'product_id' => $item['product_id'],
                        'quantity_change' => 0,
                        'reserved_change' => $reserveAmount,
                        'transaction_type' => 'reservation',
                        'reason' => "Order #{$order->id} created by admin",
                        'expiry_date' => $inventory->expiry_date,
                        'batch_number' => $inventory->batch_number,
                    ]);

                    $quantityToReserve -= $reserveAmount;
                }
            }
        }

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
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be confirmed.');
        }

        $order->update(['status' => 'confirmed']);

        return back()->with('success', 'Order confirmed successfully.');
    }

    /**
     * Reject order.
     */
    public function reject(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be rejected.');
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        // Release reserved inventory
        foreach ($order->items as $item) {
            $product = $item->product;
            $quantityToRelease = $item->quantity;

            // Release from inventories in reverse FIFO order (most recently added first)
            $inventories = $product->inventories()
                ->where(function ($query) {
                    $query->whereNull('expiry_date')
                        ->orWhere('expiry_date', '>=', now()->toDate());
                })
                ->orderBy('expiry_date', 'desc')
                ->get();

            foreach ($inventories as $inventory) {
                if ($quantityToRelease <= 0) {
                    break;
                }

                $reservedAmount = min($quantityToRelease, $inventory->reserved_quantity);

                if ($reservedAmount > 0) {
                    $inventory->update([
                        'reserved_quantity' => max(0, $inventory->reserved_quantity - $reservedAmount),
                    ]);

                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'quantity_change' => 0,
                        'reserved_change' => -$reservedAmount,
                        'transaction_type' => 'adjustment',
                        'reason' => "Order #{$order->id} rejected: {$validated['reason']}",
                        'expiry_date' => $inventory->expiry_date,
                        'batch_number' => $inventory->batch_number,
                    ]);

                    $quantityToRelease -= $reservedAmount;
                }
            }
        }

        $order->update([
            'status' => 'canceled',
            'general_notes' => ($order->general_notes ? $order->general_notes . "\n" : "") . "Rejected: {$validated['reason']}",
        ]);

        return back()->with('success', 'Order rejected successfully.');
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
        $order = Order::findOrFail($orderId);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,done,canceled',
        ]);

        $newStatus = $validated['status'];

        // Validate status transitions
        $validTransitions = $this->getValidStatusTransitions($order->status);
        if (!in_array($newStatus, $validTransitions)) {
            return back()->with('error', "Cannot transition from {$order->status} to {$newStatus}");
        }

        // Handle inventory changes based on status
        if ($newStatus === 'shipped') {
            // Convert reserved to actual stock deduction using FIFO
            foreach ($order->items as $item) {
                $product = $item->product;
                $quantityToDeduct = $item->quantity;

                // Deduct from inventories in FIFO order (expiring soon first)
                $inventories = $product->getInventoriesByExpiry();

                foreach ($inventories as $inventory) {
                    if ($quantityToDeduct <= 0) {
                        break;
                    }

                    $deductAmount = min($quantityToDeduct, $inventory->reserved_quantity);

                    if ($deductAmount > 0) {
                        $inventory->update([
                            'stock_quantity' => max(0, $inventory->stock_quantity - $deductAmount),
                            'reserved_quantity' => max(0, $inventory->reserved_quantity - $deductAmount),
                        ]);

                        InventoryTransaction::create([
                            'product_id' => $item->product_id,
                            'quantity_change' => -$deductAmount,
                            'reserved_change' => -$deductAmount,
                            'transaction_type' => 'sale',
                            'reason' => "Order #{$order->id} shipped",
                            'expiry_date' => $inventory->expiry_date,
                            'batch_number' => $inventory->batch_number,
                        ]);

                        $quantityToDeduct -= $deductAmount;
                    }
                }
            }
        } elseif ($newStatus === 'canceled') {
            // Release reserved inventory
            foreach ($order->items as $item) {
                $product = $item->product;
                $quantityToRelease = $item->quantity;

                // Release from inventories in reverse FIFO order
                $inventories = $product->inventories()
                    ->where(function ($query) {
                        $query->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>=', now()->toDate());
                    })
                    ->orderBy('expiry_date', 'desc')
                    ->get();

                foreach ($inventories as $inventory) {
                    if ($quantityToRelease <= 0) {
                        break;
                    }

                    $releaseAmount = min($quantityToRelease, $inventory->reserved_quantity);

                    if ($releaseAmount > 0) {
                        $inventory->update([
                            'reserved_quantity' => max(0, $inventory->reserved_quantity - $releaseAmount),
                        ]);

                        InventoryTransaction::create([
                            'product_id' => $item->product_id,
                            'quantity_change' => 0,
                            'reserved_change' => -$releaseAmount,
                            'transaction_type' => 'adjustment',
                            'reason' => "Order #{$order->id} canceled",
                            'expiry_date' => $inventory->expiry_date,
                            'batch_number' => $inventory->batch_number,
                        ]);

                        $quantityToRelease -= $releaseAmount;
                    }
                }
            }
        }

        $order->update(['status' => $newStatus]);

        return back()->with('success', "Order status updated to {$newStatus} successfully.");
    }

    /**
     * Get valid status transitions for an order.
     */
    private function getValidStatusTransitions($currentStatus)
    {
        $transitions = [
            'pending' => ['confirmed', 'canceled'],
            'confirmed' => ['shipped', 'canceled'],
            'shipped' => ['delivered', 'done', 'canceled'],
            'delivered' => ['done', 'canceled'],
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

