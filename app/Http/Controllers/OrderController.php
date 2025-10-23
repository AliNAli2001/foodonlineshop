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

        $client = Client::find($clientId);

        // Cart is now handled via localStorage on the frontend
        // We just need to pass the client info
        return view('orders.checkout', compact('client'));
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
            'cart_data' => 'required|json',
        ]);

        // Parse cart data from localStorage
        $cartData = json_decode($validated['cart_data'], true);
        if (empty($cartData)) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty.');
        }

        $cart = [];
        foreach ($cartData as $productId => $item) {
            $cart[$productId] = [
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ];
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
            $product = Product::with('inventories')->find($productId);
            $subtotal = $product->price * $cartItem['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $cartItem['quantity'],
                'unit_price' => $product->price,
            ]);

            // Reserve inventory using FIFO (First In First Out) - prioritize items expiring soon
            $quantityToReserve = $cartItem['quantity'];
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
                        'product_id' => $productId,
                        'quantity_change' => 0,
                        'reserved_change' => $reserveAmount,
                        'transaction_type' => 'reservation',
                        'reason' => "Order #{$order->id} created",
                        'expiry_date' => $inventory->expiry_date,
                        'batch_number' => $inventory->batch_number,
                    ]);

                    $quantityToReserve -= $reserveAmount;
                }
            }

            $totalAmount += $subtotal;
        }

        $order->update(['total_amount' => $totalAmount]);

        // Return success with JavaScript to clear localStorage
        return redirect()->route('client.order-details', $order->id)
            ->with('success', 'Order created successfully. Awaiting admin confirmation.')
            ->with('clearCart', true);
    }
}

