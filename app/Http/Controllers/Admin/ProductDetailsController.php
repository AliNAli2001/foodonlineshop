<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\InventoryTransaction;

class ProductDetailsController extends Controller
{
    /**
     * Show product details with operations summary.
     */
    public function show($productId)
    {
        $product = Product::with(['inventory', 'categories', 'images'])->findOrFail($productId);

        // Get recent order items (recent sellings)
        $recentSellings = OrderItem::where('product_id', $productId)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get recent inventory transactions
        $recentInventory = InventoryTransaction::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate statistics
        $totalSold = OrderItem::where('product_id', $productId)->sum('quantity');
        $totalRevenue = OrderItem::where('product_id', $productId)
            ->selectRaw('SUM(quantity * unit_price) as total')
            ->value('total') ?? 0;

        return view('admin.products.details', compact(
            'product',
            'recentSellings',
            'recentInventory',
            'totalSold',
            'totalRevenue'
        ));
    }
}

