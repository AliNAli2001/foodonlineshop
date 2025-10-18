<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DamagedGoods;
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

        DamagedGoods::create($validated);

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

