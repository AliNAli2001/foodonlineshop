<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Tag;
class ProductTagController extends Controller
{
      /**
     * Show categories for a product.
     */
    public function index($productId)
    {
        $product = Product::with('tags')->findOrFail($productId);
        $allTags = Tag::all();
        $selectedTagIds = $product->tags->pluck('id')->toArray();

        return view('admin.products.tags.index', compact('product', 'allTags', 'selectedTagIds'));
    }

    /**
     * Update product tags.
     */
    public function update(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $product->tags()->sync($validated['tags'] ?? []);

        return redirect()->route('admin.products.tags.index', $productId)
            ->with('success', 'تم تحديث وسوم المنتج بنجاح');
    }
}
