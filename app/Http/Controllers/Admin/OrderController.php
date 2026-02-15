<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\Client;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    /**
     * Show all orders.
     */
    public function index()
    {
        $orders = Order::with(['client', 'delivery'])->latest()->paginate(15);
        return Inertia::render('admin.orders.index', compact('orders'));
    }

    /**
     * Show create order form.
     */
    public function create()
    {
        $products = Product::all();
        $clients = Client::all();
        $deliveryPersons = Delivery::all();

        return Inertia::render('admin.orders.create', compact('products', 'clients', 'deliveryPersons'));
    }

    /**
     * Store a new admin-created order (immediately confirmed + stock deducted).
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

        try {
            $order = $this->orderService->createAdminOrder($validated, auth('admin')->id());

            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'تم إنشاء طلب يدوي بنجاح.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show order details.
     */
    public function show($orderId)
    {
        $order = Order::with(['client', 'delivery', 'items.product', 'items.batches', 'createdByAdmin'])->findOrFail($orderId);
        $deliveryPersons = Delivery::all();

        // Get available status transitions for this order
        $availableTransitions = $this->orderService->getAvailableStatusTransitions($order);

        return Inertia::render('admin.orders.show', compact('order', 'deliveryPersons', 'availableTransitions'));
    }

    /**
     * Confirm a pending client order → deduct stock.
     */
    public function confirm($orderId)
    {
        try {
            $this->orderService->confirmOrder($orderId);
            return back()->with('success', 'تم تأكيد الطلب و إنقاص الكمية من المستودع.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject pending order → release reservation.
     */
    public function reject(Request $request, $orderId)
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);

        try {
            $this->orderService->rejectOrder($orderId, $validated['reason']);
            return back()->with('success', 'تم رفض الطلب و تحرير البضاعة المحجوزة.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update order status with proper inventory handling.
     */
    public function updateStatus(Request $request, $orderId)
    {
       
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,rejected,shipped,delivered,done,canceled,returned',
            'delivery_id' => 'nullable|exists:delivery,id',
        ]);



        try {
            $this->orderService->updateOrderStatus(
                $orderId,
                $validated['status'],
                $validated['delivery_id'] ?? null
            );

            return back()->with('success', "تم تحديث حالة الطلب إلى {$validated['status']}.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Assign delivery person.
     */
    public function assignDelivery(Request $request, $orderId)
    {
        $validated = $request->validate(['delivery_id' => 'required|exists:delivery,id']);

        try {
            $this->orderService->assignDelivery($orderId, $validated['delivery_id']);
            return back()->with('success', 'تم إسناد الطلب لعامل التوصيل بنجاح.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update delivery method.
     */
    public function updateDeliveryMethod(Request $request, $orderId)
    {
        $validated = $request->validate([
            'delivery_method' => 'required|in:delivery,shipping,hand_delivered',
            'delivery_id' => 'nullable|required_if:delivery_method,delivery|exists:delivery,id',
            'shipping_notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->orderService->updateDeliveryMethod($orderId, $validated);
            return back()->with('success', 'تم تحديث طريقة التوصيل بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Search clients for autocomplete.
     */
    public function searchClients(Request $request)
    {
        $search = $request->get('q', '');

        $clients = Client::where(function($query) use ($search) {
            $query->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
        })
        ->limit(20)
        ->get()
        ->map(function($client) {
            return [
                'id' => $client->id,
                'text' => "{$client->first_name} {$client->last_name} ({$client->phone})",
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'phone' => $client->phone,
            ];
        });

        return response()->json([
            'results' => $clients
        ]);
    }

    /**
     * Search products for autocomplete with availability status.
     */
    public function searchProducts(Request $request)
{
    $search = $request->get('q', '');
    $exclude = $request->get('exclude', []); // 👈 استلام المنتجات المختارة

    $products = Product::with('stock')
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'LIKE', "%{$search}%")
                  ->orWhere('name_ar', 'LIKE', "%{$search}%");
            });
        })
        ->when(!empty($exclude), function ($query) use ($exclude) {
            $query->whereNotIn('id', $exclude); // 👈 منع التكرار
        })
        ->limit(30)
        ->get()
        ->map(function ($product) {
            $availableStock = $product->stock_available_quantity;
            $isAvailable = $availableStock > 0;

            return [
                'id' => $product->id,
                'text' => "{$product->name_en} - \${$product->selling_price} (متاح: {$availableStock})",
                'price' => $product->selling_price,
                'available_stock' => $availableStock,
                'disabled' => !$isAvailable,
            ];
        });

    return response()->json([
        'results' => $products
    ]);
}

}


