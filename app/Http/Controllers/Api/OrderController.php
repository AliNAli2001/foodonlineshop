<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * List client orders.
     */
    public function index(Request $request)
    {
        $client = $request->user();
        $orders = $client->orders()->latest()->paginate(10);

        return response()->json($orders);
    }

    /**
     * Show single order details.
     */
    public function show(Request $request, $order)
    {
        $client = $request->user();
        $order = $client->orders()->findOrFail($order);
        $items = $order->items()->with('product')->get();


        return response()->json([
            'order' => $order,
            'items' => $items,
        ]);
    }

    /**
     * Store a new order.
     */
    public function store(Request $request)
    {
        $client = $request->user();

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

        // Parse cart data from request
        $cartData = json_decode($validated['cart_data'], true);
        if (empty($cartData)) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }

        $cart = [];
        foreach ($cartData as $productId => $item) {
            $cart[$productId] = [
                'quantity' => $item['quantity'],
            ];
        }

        // Check stock availability before proceeding
        foreach ($cart as $productId => $cartItem) {
            $product = Product::findOrFail($productId);
            if ($product->getTotalAvailableStockAttribute() < $cartItem['quantity']) {
                return response()->json([
                    'message' => "Insufficient stock for product ID {$productId}. Available: {$product->getTotalAvailableStockAttribute()}, Requested: {$cartItem['quantity']}",
                ], 400);
            }
        }

        return DB::transaction(function () use ($request, $client, $validated, $cart) {
            $totalAmount = 0;
            $costPrice = 0;

            // Create order
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
            ]);

            // Create order items and reserve inventory
            foreach ($cart as $productId => $cartItem) {
                $product = Product::with('inventories')->findOrFail($productId);
                $subtotal = $product->price * $cartItem['quantity'];
                $totalAmount += $subtotal;

                // Create a single order item for the total quantity (no inventory_id yet, since pending)
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $cartItem['quantity'],
                    'unit_price' => $product->price,
                ]);

                // Reserve inventory using FIFO - oldest expiring first
                $quantityToReserve = $cartItem['quantity'];
                $inventories = $product->getInventoriesByExpiry(); // Assuming sorted by expiry_date ASC (oldest first)

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
// Accumulate estimated cost price (optional, for profit calc later)
                        $costPrice += $inventory->cost_price * $reserveAmount;

                        // Log transaction
                        InventoryTransaction::create([
                            'inventory_id' => $inventory->id,
                            'product_id' => $productId,
                            'quantity_change' => 0,
                            'reserved_change' => $reserveAmount,
                            'cost_price' => $costPrice,
                            'transaction_type' => 'reservation',
                            'reason' => "Order #{$order->id} created",
                            'expiry_date' => $inventory->expiry_date,
                            'batch_number' => $inventory->batch_number,
                        ]);

                        
                        $quantityToReserve -= $reserveAmount;
                    }
                }

                if ($quantityToReserve > 0) {
                    throw new \Exception("Insufficient available stock to reserve for product ID {$productId}.");
                }
            }

            $order->update([
                'total_amount' => $totalAmount,
                'cost_price' => $costPrice,
            ]);

            return response()->json([
                'message' => 'Order created successfully. Awaiting admin confirmation.',
                'order' => $order,
            ], 201);
        });
    }
}