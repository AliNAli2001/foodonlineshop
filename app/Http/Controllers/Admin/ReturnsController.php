<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnItem;
use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Http\Request;

class ReturnsController extends Controller
{
    /**
     * Show all returns.
     */
    public function index()
    {
        $returns = ReturnItem::with(['order', 'orderItem.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.returns.index', compact('returns'));
    }

    /**
     * Show create return form.
     */
    public function create($orderId)
    {
        $order = Order::with('items.product')->findOrFail($orderId);

        return view('admin.returns.create', compact('order'));
    }

    /**
     * Store a new return.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'required|exists:order_items,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $orderItem = OrderItem::findOrFail($validated['order_item_id']);

        if ($validated['quantity'] > $orderItem->quantity) {
            return back()->withErrors(['quantity' => 'Return quantity cannot exceed order quantity.']);
        }

        ReturnItem::create($validated);

        return redirect()->route('admin.returns.index')
            ->with('success', 'Return created successfully.');
    }

    /**
     * Show return details.
     */
    public function show($returnId)
    {
        $return = ReturnItem::with(['order', 'orderItem.product', 'damagedGoods'])
            ->findOrFail($returnId);

        return view('admin.returns.show', compact('return'));
    }

    /**
     * Delete a return.
     */
    public function destroy($returnId)
    {
        $return = ReturnItem::findOrFail($returnId);
        $return->delete();

        return redirect()->route('admin.returns.index')
            ->with('success', 'Return deleted successfully.');
    }
}

