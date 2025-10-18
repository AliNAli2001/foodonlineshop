<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Show all inventory.
     */
    public function index()
    {
        $inventory = Inventory::with('product')
            ->paginate(15);

        return view('admin.inventory.index', compact('inventory'));
    }

    /**
     * Show inventory for a specific product.
     */
    public function show($productId)
    {
        $product = Product::findOrFail($productId);
        $inventory = Inventory::where('product_id', $productId)->firstOrFail();

        // Get recent transactions
        $transactions = InventoryTransaction::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.inventory.show', compact('product', 'inventory', 'transactions'));
    }

    /**
     * Show form to adjust inventory.
     */
    public function edit($productId)
    {
        $product = Product::findOrFail($productId);
        $inventory = Inventory::where('product_id', $productId)->firstOrFail();

        return view('admin.inventory.edit', compact('product', 'inventory'));
    }

    /**
     * Update inventory.
     */
    public function update(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $inventory = Inventory::where('product_id', $productId)->firstOrFail();

        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'minimum_alert_quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $oldStock = $inventory->stock_quantity;
        $newStock = $validated['stock_quantity'];
        $quantityChange = $newStock - $oldStock;

        $inventory->update([
            'stock_quantity' => $newStock,
            'minimum_alert_quantity' => $validated['minimum_alert_quantity'],
        ]);

        // Record transaction
        if ($quantityChange != 0) {
            InventoryTransaction::create([
                'product_id' => $productId,
                'quantity_change' => $quantityChange,
                'transaction_type' => 'adjustment',
                'reason' => $validated['reason'] ?? 'Manual adjustment',
            ]);
        }

        return redirect()->route('admin.inventory.show', $productId)
            ->with('success', 'Inventory updated successfully.');
    }
}

