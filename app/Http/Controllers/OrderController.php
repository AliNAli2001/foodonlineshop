<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Show checkout form.
     */
    public function checkout()
    {
        $clientId = session('client_id');
        if (!$clientId) {
            return redirect()->route('login');
        }

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty.');
        }

        $client = Client::find($clientId);
        $items = [];
        $total = 0;

        foreach ($cart as $productId => $cartItem) {
            $product = Product::find($productId);
            $subtotal = $product->price * $cartItem['quantity'];
            $items[] = [
                'product' => $product,
                'quantity' => $cartItem['quantity'],
                'unit_price' => $product->price,
                'subtotal' => $subtotal,
            ];
            $total += $subtotal;
        }

        return view('orders.checkout', compact('client', 'items', 'total'));
    }

    /**
     * Store a new order.
     */
    public function store(Request $request)
    {
        $clientId = session('client_id');
        if (!$clientId) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'order_source' => 'required|in:inside_city,outside_city',
            'delivery_method' => 'required|in:delivery,shipping,hand_delivered',
            'address_details' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'shipping_notes' => 'nullable|string',
            'general_notes' => 'nullable|string',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty.');
        }

        $client = Client::find($clientId);
        $totalAmount = 0;

        // Create order
        $order = Order::create([
            'client_id' => $clientId,
            'order_source' => $validated['order_source'],
            'delivery_method' => $validated['delivery_method'],
            'address_details' => $validated['address_details'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'shipping_notes' => $validated['shipping_notes'] ?? null,
            'general_notes' => $validated['general_notes'] ?? null,
            'status' => 'pending',
        ]);

        // Create order items and update inventory
        foreach ($cart as $productId => $cartItem) {
            $product = Product::with('inventory')->find($productId);
            $subtotal = $product->price * $cartItem['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $cartItem['quantity'],
                'unit_price' => $product->price,
            ]);

            // Reserve inventory
            $inventory = $product->inventory;
            $inventory->update([
                'reserved_quantity' => $inventory->reserved_quantity + $cartItem['quantity'],
            ]);

            // Log transaction
            InventoryTransaction::create([
                'product_id' => $productId,
                'quantity_change' => 0,
                'reserved_change' => $cartItem['quantity'],
                'transaction_type' => 'reservation',
                'reason' => "Order #{$order->id} created",
            ]);

            $totalAmount += $subtotal;
        }

        $order->update(['total_amount' => $totalAmount]);

        // Clear cart
        session()->forget('cart');

        return redirect()->route('client.order-details', $order->id)
            ->with('success', 'Order created successfully. Awaiting admin confirmation.');
    }
}

