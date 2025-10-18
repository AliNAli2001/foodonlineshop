<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
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
            $inventory = $item->product->inventory;
            $inventory->update([
                'reserved_quantity' => max(0, $inventory->reserved_quantity - $item->quantity),
            ]);

            InventoryTransaction::create([
                'product_id' => $item->product_id,
                'quantity_change' => 0,
                'reserved_change' => -$item->quantity,
                'transaction_type' => 'adjustment',
                'reason' => "Order #{$order->id} rejected: {$validated['reason']}",
            ]);
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
     * Update order status.
     */
    public function updateStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,canceled,returned',
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Order status updated successfully.');
    }
}

