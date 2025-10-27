<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\ProductImage;
use App\Models\InventoryTransaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Show all products.
     */
    public function index()
    {
        $products = Product::with(['inventories', 'categories'])->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show create product form.
     */
    public function create()
    {
        $categories = Category::all();

        $maxOrderItems = Setting::get('max_order_items');
        $generalMinimumAlertQuantity = Setting::get('general_minimum_alert_quantity');
        return view('admin.products.create', compact('categories', 'maxOrderItems', 'generalMinimumAlertQuantity'));
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
            'price' => 'required|numeric|min:0.001',

            'max_order_item' => 'nullable|integer|min:1',
            'featured' => 'boolean',
            'enable_inventory' => 'boolean',
            'categories' => 'array',
            'stock_quantity' => 'required_if:enable_inventory,1|nullable|integer|min:0',
            'minimum_alert_quantity' => 'required_if:enable_inventory,1|nullable|integer|min:0',
            'expiry_date' => 'required_if:enable_inventory,1|date|after_or_equal:today',
            'batch_number' => 'required_if:enable_inventory,1|string|max:100',
            'cost_price' => 'required_if:enable_inventory,1|numeric|min:0.001',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
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

        $costPrice = $validated['cost_price'] ?? 0;

        if ($request->boolean('enable_inventory')) {
            // Create inventory (without expiry date for initial stock)
            $inventory = Inventory::create([
                'product_id' => $product->id,
                'stock_quantity' => $validated['stock_quantity'],
                'minimum_alert_quantity' => $validated['minimum_alert_quantity'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'batch_number' =>  $validated['batch_number'] ?? null,
                'cost_price' => $costPrice,
            ]);



            // Log transaction
            InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'quantity_change' => $validated['stock_quantity'],
                'transaction_type' => 'restock',
                'reason' => 'Initial stock',
                'expiry_date' => $validated['expiry_date'] ?? null,
                'batch_number' =>  $validated['batch_number'] ?? null,
                'cost_price' => $costPrice,
            ]);
        }
        // Attach categories
        if (!empty($validated['categories'])) {
            $product->categories()->attach($validated['categories']);
        }

        // Store images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                // Create the ProductImage record first to get its ID
                $productImage = ProductImage::create([
                    'product_id' => $product->id,
                    'is_primary' => $index === 0,
                    'image_url' => '' // Temporary placeholder
                ]);

                // Use the Product ID as the folder name
                $folder = "products/{$product->id}";
                $imageName = uniqid('img_', true) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs($folder, $imageName, 'public');
                // Update the ProductImage record with the correct image path
                $productImage->update(['image_url' => $imagePath]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show edit product form.
     */
    public function edit($productId)
    {
        $product = Product::with(['categories'])->findOrFail($productId);
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update product.
     */
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
            'image_ids_to_delete' => 'nullable|array', // Validate array of image IDs to delete
            'image_ids_to_delete.*' => 'exists:product_images,id', // Ensure IDs exist in product_images table
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Update product details
        $product->update([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'description_ar' => $validated['description_ar'],
            'description_en' => $validated['description_en'],
            'price' => $validated['price'],
            'max_order_item' => $validated['max_order_item'],
            'featured' => $validated['featured'] ?? false,
        ]);

        // Sync categories
        $product->categories()->sync($validated['categories'] ?? []);

        // Delete selected images
        if (!empty($validated['image_ids_to_delete'])) {
            foreach ($validated['image_ids_to_delete'] as $imageId) {
                $productImage = ProductImage::find($imageId);
                if ($productImage && $productImage->product_id === $product->id) { // Ensure image belongs to this product
                    // Delete the image from storage
                    if ($productImage->image_url) {
                        Storage::disk('public')->delete($productImage->image_url);
                    }
                    // Delete the ProductImage record
                    $productImage->delete();
                }
            }
        }

        // Store new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                // Create the ProductImage record first to get its ID
                $productImage = ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => '', // Temporary placeholder
                    'is_primary' => $index === 0 && $product->images->isEmpty(), // Set as primary only if no images exist
                ]);

                // Use the ProductImage ID as the folder name
                $folder = "products/{$product->id}";
                $imageName = uniqid('img_', true) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs($folder, $imageName, 'public');

                // Update the ProductImage record with the correct image path
                $productImage->update(['image_url' => $imagePath]);
            }
        }

        return redirect()->route('admin.products.show', $product->id)
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Show product details.
     */
    public function show($productId)
    {
        $product = Product::with(['inventories', 'categories', 'images'])->findOrFail($productId);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Delete product.
     */
    public function destroy($productId)
    {
        $product = Product::findOrFail($productId);
        // Delete associated images from storage and database
        $productImages = $product->images; // Assuming 'images' is the relationship name
        foreach ($productImages as $productImage) {
            // Delete the folder containing the image (e.g., products/{image_id})
            Storage::disk('public')->deleteDirectory("products/{$productImage->id}");
            // Delete the ProductImage record
            $productImage->delete();
        }

        // Delete the product
        $product->delete();
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
