<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Show all products.
     */
    public function index()
    {
        $products = Product::with(['inventory', 'categories'])->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show create product form.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a new product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'max_order_item' => 'nullable|integer|min:1',
            'featured' => 'boolean',
            'categories' => 'array',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_alert_quantity' => 'required|integer|min:0',
        ]);

        $product = Product::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'description_ar' => $validated['description_ar'],
            'description_en' => $validated['description_en'],
            'price' => $validated['price'],
            'max_order_item' => $validated['max_order_item'],
            'featured' => $validated['featured'] ?? false,
        ]);

        // Create inventory
        Inventory::create([
            'product_id' => $product->id,
            'stock_quantity' => $validated['stock_quantity'],
            'minimum_alert_quantity' => $validated['minimum_alert_quantity'],
        ]);

        // Attach categories
        if (!empty($validated['categories'])) {
            $product->categories()->attach($validated['categories']);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show edit product form.
     */
    public function edit($productId)
    {
        $product = Product::with(['inventory', 'categories'])->findOrFail($productId);
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update product.
     */
    public function update(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'max_order_item' => 'nullable|integer|min:1',
            'featured' => 'boolean',
            'categories' => 'array',
            'minimum_alert_quantity' => 'required|integer|min:0',
        ]);

        $product->update([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'description_ar' => $validated['description_ar'],
            'description_en' => $validated['description_en'],
            'price' => $validated['price'],
            'max_order_item' => $validated['max_order_item'],
            'featured' => $validated['featured'] ?? false,
        ]);

        // Update inventory
        $product->inventory->update([
            'minimum_alert_quantity' => $validated['minimum_alert_quantity'],
        ]);

        // Sync categories
        $product->categories()->sync($validated['categories'] ?? []);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete product.
     */
    public function destroy($productId)
    {
        $product = Product::findOrFail($productId);
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}

