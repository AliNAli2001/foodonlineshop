<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * Show categories for a product.
     */
    public function index($productId)
    {
        $product = Product::with('categories')->findOrFail($productId);
        $allCategories = Category::all();
        $selectedCategoryIds = $product->categories->pluck('id')->toArray();

        return view('admin.products.categories.index', compact('product', 'allCategories', 'selectedCategoryIds'));
    }

    /**
     * Update product categories.
     */
    public function update(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
        ]);

        $product->categories()->sync($validated['categories'] ?? []);

        return redirect()->route('admin.products.categories.index', $productId)
            ->with('success', 'تم تحديث تصنيفات المنتج بنجاح');
    }
}

