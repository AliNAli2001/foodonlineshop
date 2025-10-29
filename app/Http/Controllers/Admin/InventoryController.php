<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Setting;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function create($productId)
    {
        $product = Product::findOrFail($productId);
        $generalMinimumAlertQuantity = Setting::get('general_minimum_alert_quantity');
        return view('admin.inventory.create', compact('product', 'generalMinimumAlertQuantity'));
    }

    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $validated = $request->validate([
            'batch_number' => 'required|string|max:100',
            'expiry_date' => 'required|date|after_or_equal:today',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_alert_quantity' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0.001',
        ]);
        $inventory = Inventory::create([
            'product_id' => $product->id,
            'batch_number' => $validated['batch_number'],
            'expiry_date' => $validated['expiry_date'],
            'stock_quantity' => $validated['stock_quantity'],
            'minimum_alert_quantity' => $validated['minimum_alert_quantity'],
            'cost_price' => $validated['cost_price'],
        ]);
        InventoryTransaction::create([
            'inventory_id' => $inventory->id,
            'quantity_change' => $validated['stock_quantity'],
            'transaction_type' => 'restock',
            'cost_price' => $validated['cost_price'],
            'reason' => 'Initial stock',
        ]);
        return redirect()->route('admin.inventory.product', $product->id)
            ->with('success', 'Inventory created successfully.');
    }
    /**
     * Show all inventory grouped by product.
     */
    public function index()
    {
        // Get all products with their inventories
        $products = Product::with(['inventories' => function ($query) {
            $query->orderBy('expiry_date', 'asc');
        }])
            ->whereHas('inventories')
            ->paginate(10);

        return view('admin.inventory.index', compact('products'));
    }

    /**
     * Show inventories for a specific product.
     */
    public function product($productId)
    {
        $product = Product::with('inventories')->findOrFail($productId);
        $transactions = $product->transactions()->orderByDesc('created_at')->paginate(15);


        return view('admin.inventory.product.show', [
            'product' => $product,
            'inventories' => $product->inventories,
            'transactions' => $transactions,
        ]);
    }

    public function show($inventoryId)
    {
        $inventory = Inventory::findOrFail($inventoryId);

        $product = $inventory->product;
        // Get recent transactions
        $transactions = $inventory->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.inventory.show', compact('product', 'inventory', 'transactions'));
    }

    /**
     * Show form to adjust inventory.
     */
    public function edit($inventoryId)
    {
        $inventory = Inventory::findOrFail($inventoryId);
        $product = $inventory->product;

        return view('admin.inventory.edit', compact('product', 'inventory'));
    }

    /**
     * Update inventory.
     */
    public function update(Request $request, $inventoryId)
    {
        $inventory = Inventory::findOrFail($inventoryId);

        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'minimum_alert_quantity' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0.001',
            'batch_number' => 'required|string|max:100',
            'expiry_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);


        $inventory = Inventory::findOrFail($inventoryId)
            ->first();

        if ($inventory) {
            // Update existing inventory record
            $oldStock = $inventory->stock_quantity;
            $newStock = $validated['stock_quantity'];
            $quantityChange = $newStock - $oldStock;


            $inventory->update([
                'stock_quantity' => $newStock,
                'minimum_alert_quantity' => $validated['minimum_alert_quantity'],
                'cost_price' => $validated['cost_price'],
                'expiry_date' => $validated['expiry_date'],
                'batch_number' => $validated['batch_number'],
            ]);

            // // Record transaction
            // if ($quantityChange != 0) {

            $inventoryTransaction = InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'quantity_change' => $quantityChange,
                'transaction_type' => 'adjustment',
                'cost_price' => $validated['cost_price'],
                'reason' => $validated['reason'] ?? 'Manual adjustment',
                'expiry_date' => $inventory->expiry_date,
                'batch_number' => $inventory->batch_number,
            ]);
            // }
        }
        // else {
        //     // Create new inventory record with expiry date
        //     $inventory = Inventory::create([
        //         'product_id' => $productId,
        //         'stock_quantity' => $validated['stock_quantity'],
        //         'minimum_alert_quantity' => $validated['minimum_alert_quantity'],
        //         'expiry_date' => $expiryDate,
        //         'batch_number' => $batchNumber,
        //     ]);

        //     // Record transaction
        //     InventoryTransaction::create([
        //         'product_id' => $productId,
        //         'quantity_change' => $validated['stock_quantity'],
        //         'transaction_type' => 'restock',
        //         'reason' => $validated['reason'] ?? 'New inventory batch',
        //         'expiry_date' => $expiryDate,
        //         'batch_number' => $batchNumber,
        //     ]);
        // }

        return redirect()->route('admin.inventory.show', $inventoryId)
            ->with('success', 'Inventory updated successfully.');
    }



    // public function edit($productId)
    // {
    //     $product = Product::findOrFail($productId);
    //     $inventory = Inventory::where('product_id', $productId)->firstOrFail();

    //     return view('admin.inventory.edit', compact('product', 'inventory'));
    // }

    // /**
    //  * Update inventory.
    //  */
    // public function update(Request $request, $productId)
    // {
    //     $product = Product::findOrFail($productId);

    //     $validated = $request->validate([
    //         'stock_quantity' => 'required|integer|min:0',
    //         'minimum_alert_quantity' => 'required|integer|min:0',
    //         'expiry_date' => 'nullable|date|after_or_equal:today',
    //         'batch_number' => 'nullable|string|max:100',
    //         'reason' => 'nullable|string|max:255',
    //     ]);

    //     // Check if inventory with same expiry_date and batch_number exists
    //     $expiryDate = $validated['expiry_date'] ?? null;
    //     $batchNumber = $validated['batch_number'] ?? null;

    //     $inventory = Inventory::where('product_id', $productId)
    //         ->where('expiry_date', $expiryDate)
    //         ->where('batch_number', $batchNumber)
    //         ->first();

    //     if ($inventory) {
    //         // Update existing inventory record
    //         $oldStock = $inventory->stock_quantity;
    //         $newStock = $validated['stock_quantity'];
    //         $quantityChange = $newStock - $oldStock;

    //         $inventory->update([
    //             'stock_quantity' => $newStock,
    //             'minimum_alert_quantity' => $validated['minimum_alert_quantity'],
    //         ]);

    //         // Record transaction
    //         if ($quantityChange != 0) {
    //             InventoryTransaction::create([
    //                 'product_id' => $productId,
    //                 'quantity_change' => $quantityChange,
    //                 'transaction_type' => 'adjustment',
    //                 'reason' => $validated['reason'] ?? 'Manual adjustment',
    //                 'expiry_date' => $expiryDate,
    //                 'batch_number' => $batchNumber,
    //             ]);
    //         }
    //     } else {
    //         // Create new inventory record with expiry date
    //         $inventory = Inventory::create([
    //             'product_id' => $productId,
    //             'stock_quantity' => $validated['stock_quantity'],
    //             'minimum_alert_quantity' => $validated['minimum_alert_quantity'],
    //             'expiry_date' => $expiryDate,
    //             'batch_number' => $batchNumber,
    //         ]);

    //         // Record transaction
    //         InventoryTransaction::create([
    //             'product_id' => $productId,
    //             'quantity_change' => $validated['stock_quantity'],
    //             'transaction_type' => 'restock',
    //             'reason' => $validated['reason'] ?? 'New inventory batch',
    //             'expiry_date' => $expiryDate,
    //             'batch_number' => $batchNumber,
    //         ]);
    //     }

    //     return redirect()->route('admin.inventory.show', $productId)
    //         ->with('success', 'Inventory updated successfully.');
    // }
}
