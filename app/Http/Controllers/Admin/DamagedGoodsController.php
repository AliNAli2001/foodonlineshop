<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DamagedGoods;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ReturnItem;
use Illuminate\Http\Request;

class DamagedGoodsController extends Controller
{
    /**
     * Show all damaged goods.
     */
    public function index()
    {
        $damagedGoods = DamagedGoods::with(['product', 'returnItem'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.damaged-goods.index', compact('damagedGoods'));
    }

    /**
     * Show create damaged goods form.
     */
    public function create()
    {
        $products = Product::all();
        $returnItems = ReturnItem::all();

        return view('admin.damaged-goods.create', compact('products', 'returnItems'));
    }

    /**
     * Store a new damaged goods record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'source' => 'required|in:inventory,external,returned',
            'return_item_id' => 'nullable|exists:return_items,id',
            'reason' => 'required|string|max:255',
        ]);

        $inventoryTransactionId = null;
        $product = Product::findOrFail($validated['product_id']);

        if ($validated['source'] === 'inventory') {
            // Create inventory transaction
            $transaction = InventoryTransaction::create([
                'product_id' => $validated['product_id'],
                'quantity_change' => -$validated['quantity'],
                'transaction_type' => 'damaged',
                'reason' => $validated['reason'],
            ]);

            $inventoryTransactionId = $transaction->id;

            // Update inventory
            $product->inventory->update([
                'stock_quantity' => $product->inventory->stock_quantity - $validated['quantity'],
            ]);
        }

        // Create damaged goods record with transaction ID
        $validated['inventory_transaction_id'] = $inventoryTransactionId;
        DamagedGoods::create($validated);

        if ($validated['source'] === 'returned') {
            $returnItem = ReturnItem::findOrFail($validated['return_item_id']);
            $returnItem->update(['quantity' => $returnItem->quantity - $validated['quantity']]);
        }

        if ($validated['source'] === 'external') {
            $product->update(['quantity' => $product->quantity - $validated['quantity']]);
        }

        return redirect()->route('admin.damaged-goods.index')
            ->with('success', 'Damaged goods record created successfully.');
    }

    /**
     * Show damaged goods details.
     */
    public function show($damagedGoodsId)
    {
        $damagedGoods = DamagedGoods::with(['product', 'returnItem'])
            ->findOrFail($damagedGoodsId);

        return view('admin.damaged-goods.show', compact('damagedGoods'));
    }

    /**
     * Delete a damaged goods record.
     */
    public function destroy($damagedGoodsId)
    {
        $damagedGoods = DamagedGoods::findOrFail($damagedGoodsId);
        $damagedGoods->delete();

        return redirect()->route('admin.damaged-goods.index')
            ->with('success', 'Damaged goods record deleted successfully.');
    }
}

