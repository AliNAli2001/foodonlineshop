<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    /**
     * List client orders.
     */
    public function index(Request $request)
    {
        $client = $request->user();
        $orders = $this->orderService->getClientOrders($client);

        return OrderResource::collection($orders);
    }

    /**
     * Show single order details.
     */
    public function show(Request $request, $orderId)
    {
        $client = $request->user();
        $orderData = $this->orderService->getClientOrder($client, $orderId);

        return new OrderResource($orderData);
    }

    /**
     * Store a new order with inventory reservation using FIFO batches.
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

        try {
            $order = $this->orderService->createClientOrder($validated, $client);
            $order->load('items.product');

            return response()->json([
                'message' => 'Order created successfully. Awaiting admin confirmation.',
                'order' => new OrderResource($order),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}