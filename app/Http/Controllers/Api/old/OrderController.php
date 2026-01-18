<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Get client's orders.
     */
    public function index(Request $request)
    {
        $clientId = auth('client')->id();
        $orders = Order::where('client_id', $clientId)
            ->with(['items.product', 'delivery'])
            ->latest()
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get single order details.
     */
    public function show($orderId)
    {
        $clientId = auth('client')->id();
        $order = Order::where('client_id', $clientId)
            ->with(['items.product', 'delivery'])
            ->findOrFail($orderId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'delivery_method' => $order->delivery_method,
                'delivery_address' => $order->delivery_address,
                'latitude' => $order->latitude,
                'longitude' => $order->longitude,
                'created_at' => $order->created_at,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name_en,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                        'status' => $item->status,
                    ];
                }),
                'delivery' => $order->delivery ? [
                    'id' => $order->delivery->id,
                    'name' => $order->delivery->name,
                    'phone' => $order->delivery->phone,
                ] : null,
            ],
        ], 200);
    }

    /**
     * Create a new order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cart_data' => 'required|json',
            'delivery_method' => 'required|in:delivery,hand_delivered,shipping',
            'delivery_address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $clientId = auth('client')->id();
        $client = Client::find($clientId);

        // Parse cart data
        $cartData = json_decode($validated['cart_data'], true);
        if (!is_array($cartData) || empty($cartData)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 422);
        }

        $totalAmount = 0;

        // Create order
        $order = Order::create([
            'client_id' => $clientId,
            'delivery_method' => $validated['delivery_method'],
            'delivery_address' => $validated['delivery_address'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status' => 'pending',
            'total_amount' => 0, // Will be updated
        ]);

        // Create order items and update inventory
        foreach ($cartData as $productId => $cartItem) {
            $product = Product::with('inventories')->find($productId);
            $subtotal = $product->price * $cartItem['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $cartItem['quantity'],
                'unit_price' => $product->price,
            ]);

            // Reserve inventory using FIFO
            $quantityToReserve = $cartItem['quantity'];
            $inventories = $product->getInventoriesByExpiry();

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

                    InventoryTransaction::create([
                        'product_id' => $productId,
                        'available_change' => 0,
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

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
            ],
        ], 201);
    }
}

