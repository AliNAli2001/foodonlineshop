<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class ReturnsController extends Controller
{
    protected ReturnService $returnService;

    public function __construct(ReturnService $returnService)
    {
        $this->returnService = $returnService;
    }
    /**
     * Show all returns.
     */
    public function index()
    {
        $returns = $this->returnService->getAllReturns();
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

        try {
            $this->returnService->createReturn($validated);

            return redirect()->route('admin.returns.index')
                ->with('success', 'تم إنشاء المرتجع بنجاح.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show return details.
     */
    public function show($returnId)
    {
        try {
            $return = $this->returnService->getReturn($returnId);
            return view('admin.returns.show', compact('return'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a return.
     */
    public function destroy($returnId)
    {
        try {
            $this->returnService->deleteReturn($returnId);

            return redirect()->route('admin.returns.index')
                ->with('success', 'تم حذف المرتجع بنجاح.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

