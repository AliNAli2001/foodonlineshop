<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Exceptions\OrderItemBatchNotFoundException;
use App\Exceptions\OrderStateException;
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
    public function index(Request $request)
    {
        $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'done', 'canceled', 'returned', 'rejected'];

        $status = $request->query('status');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $totalPrice = $request->query('total_price');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $startDateValue = is_string($startDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ? $startDate : null;
        $endDateValue = is_string($endDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) ? $endDate : null;

        $baseQuery = Order::query();

        if (is_string($status) && in_array($status, $validStatuses, true)) {
            $baseQuery->where('status', $status);
        }

        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
            $baseQuery->where('total_amount', '>=', (float) $minPrice);
        }

        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
            $baseQuery->where('total_amount', '<=', (float) $maxPrice);
        }

        if ($totalPrice !== null && $totalPrice !== '' && is_numeric($totalPrice)) {
            $baseQuery->where('total_amount', '=', (float) $totalPrice);
        }

        if ($startDateValue !== null) {
            $baseQuery->whereRaw('DATE(COALESCE(order_date, created_at)) >= ?', [$startDateValue]);
        }

        if ($endDateValue !== null) {
            $baseQuery->whereRaw('DATE(COALESCE(order_date, created_at)) <= ?', [$endDateValue]);
        }

        $summaryRows = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as orders_count, COALESCE(SUM(total_amount), 0) as total_amount_sum')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $statusSummary = collect($validStatuses)->mapWithKeys(function (string $currentStatus) use ($summaryRows) {
            $row = $summaryRows->get($currentStatus);

            return [
                $currentStatus => [
                    'count' => (int) ($row->orders_count ?? 0),
                    'total' => (float) ($row->total_amount_sum ?? 0),
                ],
            ];
        });

        $orders = (clone $baseQuery)
            ->with(['client', 'delivery'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin.orders.index', [
            'orders' => $orders,
            'statusSummary' => $statusSummary,
            'filters' => [
                'status' => is_string($status) ? $status : null,
                'min_price' => is_string($minPrice) ? $minPrice : null,
                'max_price' => is_string($maxPrice) ? $maxPrice : null,
                'total_price' => is_string($totalPrice) ? $totalPrice : null,
                'start_date' => $startDateValue,
                'end_date' => $endDateValue,
            ],
        ]);
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
     * Work queue page for operational statuses.
     */
    public function work()
    {
        $workStatuses = ['pending', 'confirmed', 'shipped', 'delivered'];

        $orders = Order::with(['client', 'delivery'])
            ->whereIn('status', $workStatuses)
            ->latest()
            ->limit(400)
            ->get();

        $statusCounts = collect($workStatuses)->mapWithKeys(function (string $status) use ($orders) {
            return [$status => $orders->where('status', $status)->count()];
        });

        $ordersByStatus = collect($workStatuses)->mapWithKeys(function (string $status) use ($orders) {
            $rows = $orders
                ->where('status', $status)
                ->values()
                ->map(function (Order $order) {
                    $total = (float) ($order->total_amount ?? 0);
                    $cost = (float) ($order->cost_price ?? 0);

                    return [
                        'id' => $order->id,
                        'status' => $order->status,
                        'order_source' => $order->order_source,
                        'order_date' => $order->order_date,
                        'created_at' => $order->created_at,
                        'client_id' => $order->client_id,
                        'client_name' => $order->client_name,
                        'client_phone_number' => $order->client_phone_number,
                        'client' => $order->client ? [
                            'first_name' => $order->client->first_name,
                            'last_name' => $order->client->last_name,
                            'phone' => $order->client->phone,
                        ] : null,
                        'delivery' => $order->delivery ? [
                            'first_name' => $order->delivery->first_name,
                            'last_name' => $order->delivery->last_name,
                            'phone' => $order->delivery->phone,
                        ] : null,
                        'total_amount' => $total,
                        'cost_price' => $cost,
                        'profit' => round($total - $cost, 2),
                    ];
                });

            return [$status => $rows];
        });

        return Inertia::render('admin.orders.work', [
            'ordersByStatus' => $ordersByStatus,
            'statusCounts' => $statusCounts,
            'lastUpdatedAt' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Store a new admin-created order in pending status (requires confirmation).
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
                ->with('success', __('admin.orders.created'));
        } catch (InsufficientStockException|OrderStateException|InvalidOrderStatusTransitionException|OrderItemBatchNotFoundException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show order details.
     */
    public function show($orderId)
    {
        $order = Order::with(['client', 'delivery', 'items.product', 'items.batches.inventoryBatch', 'createdByAdmin'])->findOrFail($orderId);
        $deliveryPersons = Delivery::all();

        // Get available status transitions for this order
        $availableTransitions = $this->orderService->getAvailableStatusTransitions($order);

        $preparedMessages = $this->buildPreparedMessages($order);

        return Inertia::render('admin.orders.show', compact('order', 'deliveryPersons', 'availableTransitions', 'preparedMessages'));
    }

    private function buildPreparedMessages(Order $order): array
    {
        return [[
            'key' => $order->status ?: 'generic',
            'label' => ucfirst((string) ($order->status ?: 'order_update')),
            'suitable' => [
                'en' => $this->buildStateMessagePart($order, 'en'),
                'ar' => $this->buildStateMessagePart($order, 'ar'),
            ],
            'summary' => [
                'en' => $this->buildOrderSummaryPart($order, 'en'),
                'ar' => $this->buildOrderSummaryPart($order, 'ar'),
            ],
            'details' => [
                'en' => $this->buildOrderDetailsPart($order, 'en'),
                'ar' => $this->buildOrderDetailsPart($order, 'ar'),
            ],
        ]];
    }

    private function buildOrderSummaryPart(Order $order, string $lang): string
    {
        $customerName = $order->client_id
            ? trim((string) (($order->client->first_name ?? '') . ' ' . ($order->client->last_name ?? '')))
            : (string) ($order->client_name ?? '');
        $customerName = $customerName !== '' ? $customerName : '-';
        $phone = $order->client->phone ?? $order->client_phone_number ?? '-';
        $total = number_format((float) ($order->total_amount ?? 0), 2, '.', '');

        if ($lang === 'ar') {
            return implode("\n", [
                "الطلب #{$order->id}",
                "العميل: {$customerName}",
                "الهاتف: {$phone}",
                "الإجمالي: \${$total}",
            ]);
        }

        return implode("\n", [
            "Order #{$order->id}",
            "Client: {$customerName}",
            "Phone: {$phone}",
            "Total: \${$total}",
        ]);
    }

    private function buildOrderDetailsPart(Order $order, string $lang): string
    {
        $customerName = $order->client_id
            ? trim((string) (($order->client->first_name ?? '') . ' ' . ($order->client->last_name ?? '')))
            : (string) ($order->client_name ?? '');
        $customerName = $customerName !== '' ? $customerName : '-';

        $dateValue = $order->order_date ?? $order->created_at;
        $date = $dateValue ? $dateValue->format('Y-m-d H:i:s') : '-';
        $source = (string) ($order->order_source ?? '-');
        $method = (string) ($order->delivery_method ?? '-');
        $address = (string) ($order->address_details ?? '-');
        $notes = (string) ($order->admin_order_client_notes ?? '-');

        $lines = $lang === 'ar'
            ? [
                "تفاصيل الطلب #{$order->id}",
                "الحالة: " . (string) ($order->status ?? '-'),
                "العميل: {$customerName}",
                "الهاتف: " . (string) ($order->client->phone ?? $order->client_phone_number ?? '-'),
                "التاريخ: {$date}",
                "المصدر: {$source}",
                "طريقة التوصيل: {$method}",
                "العنوان: {$address}",
                "ملاحظات الإدارة: {$notes}",
                "العناصر:",
            ]
            : [
                "Order #{$order->id} Details",
                "Status: " . (string) ($order->status ?? '-'),
                "Customer: {$customerName}",
                "Phone: " . (string) ($order->client->phone ?? $order->client_phone_number ?? '-'),
                "Date: {$date}",
                "Source: {$source}",
                "Delivery Method: {$method}",
                "Address: {$address}",
                "Admin Notes: {$notes}",
                "Items:",
            ];

        foreach ($order->items ?? [] as $item) {
            $name = (string) ($item->product->name_en ?? $item->product->name_ar ?? '-');
            $qty = (int) ($item->quantity ?? 0);
            $unit = number_format((float) ($item->unit_price ?? 0), 2, '.', '');
            $subtotal = number_format((float) ($item->unit_price ?? 0) * (float) ($item->quantity ?? 0), 2, '.', '');
            $lines[] = $lang === 'ar'
                ? "- {$name} | الكمية: {$qty} | سعر الوحدة: \${$unit} | المجموع: \${$subtotal}"
                : "- {$name} | qty: {$qty} | unit: \${$unit} | subtotal: \${$subtotal}";
        }

        $total = number_format((float) ($order->total_amount ?? 0), 2, '.', '');
        $lines[] = $lang === 'ar' ? "الإجمالي: \${$total}" : "Total: \${$total}";

        return implode("\n", $lines);
    }

    private function buildStateMessagePart(Order $order, string $lang): string
    {
        if ($lang === 'ar') {
            return match ((string) $order->status) {
                'pending' => 'لديك طلب جديد بانتظار التأكيد.',
                'confirmed' => 'تم تأكيد طلبك وهو قيد التجهيز.',
                'shipped' => 'تم تسليم طلبك إلى شركة الشحن.',
                'delivered' => 'تم تعيين طلبك لمندوب توصيل وهو في الطريق.',
                'done' => 'اكتمل طلبك بنجاح.',
                'canceled' => 'تم إلغاء طلبك.',
                'rejected' => 'تم رفض طلبك.',
                'returned' => 'تم إرجاع طلبك.',
                default => 'تم تحديث حالة الطلب.',
            };
        }

        return match ((string) $order->status) {
            'pending' => 'You have a new order pending confirmation.',
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'shipped' => 'Your order was handed to the shipping company.',
            'delivered' => 'Your order is assigned to a delivery person and out for delivery.',
            'done' => 'Your order is completed successfully.',
            'canceled' => 'Your order has been canceled.',
            'rejected' => 'Your order has been rejected.',
            'returned' => 'Your order has been returned.',
            default => 'Order status has been updated.',
        };
    }

    /**
     * Confirm a pending client order → deduct stock.
     */
    public function confirm($orderId)
    {
        try {
            $this->orderService->confirmOrder($orderId);
            return back()->with('success', __('admin.orders.confirmed'));
        } catch (InsufficientStockException|OrderStateException|InvalidOrderStatusTransitionException|OrderItemBatchNotFoundException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
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
            return back()->with('success', __('admin.orders.rejected'));
        } catch (InsufficientStockException|OrderStateException|InvalidOrderStatusTransitionException|OrderItemBatchNotFoundException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
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

            return back()->with('success', __('admin.orders.status_updated', [
                'status' => __('admin.order_statuses.' . $validated['status']),
            ]));
        } catch (InsufficientStockException|OrderStateException|InvalidOrderStatusTransitionException|OrderItemBatchNotFoundException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
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
            return back()->with('success', __('admin.orders.delivery_assigned'));
        } catch (InsufficientStockException|OrderStateException|InvalidOrderStatusTransitionException|OrderItemBatchNotFoundException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
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
            return back()->with('success', __('admin.orders.delivery_method_updated'));
        } catch (InsufficientStockException|OrderStateException|InvalidOrderStatusTransitionException|OrderItemBatchNotFoundException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
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
