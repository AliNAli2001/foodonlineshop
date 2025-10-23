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

        // Get all inventory records for this product (grouped by expiry date)
        $inventories = Inventory::where('product_id', $productId)
            ->orderBy('expiry_date', 'asc')
            ->get();

        // Get recent transactions
        $transactions = InventoryTransaction::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.inventory.show', compact('product', 'inventories', 'transactions'));
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

        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'minimum_alert_quantity' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date|after_or_equal:today',
            'batch_number' => 'nullable|string|max:100',
            'reason' => 'nullable|string|max:255',
        ]);

        // Check if inventory with same expiry_date and batch_number exists
        $expiryDate = $validated['expiry_date'] ?? null;
        $batchNumber = $validated['batch_number'] ?? null;

        $inventory = Inventory::where('product_id', $productId)
            ->where('expiry_date', $expiryDate)
            ->where('batch_number', $batchNumber)
            ->first();

        if ($inventory) {
            // Update existing inventory record
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
                    'expiry_date' => $expiryDate,
                    'batch_number' => $batchNumber,
                ]);
            }
        } else {
            // Create new inventory record with expiry date
            $inventory = Inventory::create([
                'product_id' => $productId,
                'stock_quantity' => $validated['stock_quantity'],
                'minimum_alert_quantity' => $validated['minimum_alert_quantity'],
                'expiry_date' => $expiryDate,
                'batch_number' => $batchNumber,
            ]);

            // Record transaction
            InventoryTransaction::create([
                'product_id' => $productId,
                'quantity_change' => $validated['stock_quantity'],
                'transaction_type' => 'restock',
                'reason' => $validated['reason'] ?? 'New inventory batch',
                'expiry_date' => $expiryDate,
                'batch_number' => $batchNumber,
            ]);
        }

        return redirect()->route('admin.inventory.show', $productId)
            ->with('success', 'Inventory updated successfully.');
    }
}

