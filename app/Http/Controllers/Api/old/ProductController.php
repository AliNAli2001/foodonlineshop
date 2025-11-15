<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products with filtering and search.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Search by name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        // Filter by categories
        if ($request->filled('categories')) {
            $categories = $request->input('categories');
            $query->whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('category_id', $categories);
            });
        }

        // Filter featured products
        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        $products = $query->with(['inventories', 'primaryImage', 'categories'])
            ->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get single product details.
     */
    public function show($productId)
    {
        $product = Product::with(['inventories', 'images', 'categories'])
            ->findOrFail($productId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name_ar' => $product->name_ar,
                'name_en' => $product->name_en,
                'description_ar' => $product->description_ar,
                'description_en' => $product->description_en,
                'price' => $product->price,
                'max_order_item' => $product->max_order_item,
                'featured' => $product->featured,
                'total_stock' => $product->getTotalStock(),
                'available_stock' => $product->getTotalAvailableStock(),
                'inventories' => $product->inventories->map(function ($inv) {
                    return [
                        'id' => $inv->id,
                        'batch_number' => $inv->batch_number,
                        'expiry_date' => $inv->expiry_date,
                        'stock_quantity' => $inv->stock_quantity,
                        'reserved_quantity' => $inv->reserved_quantity,
                        'available' => $inv->getAvailableStock(),
                        'is_expired' => $inv->isExpired(),
                        'is_expiring_soon' => $inv->isExpiringSoon(),
                        'days_until_expiry' => $inv->getDaysUntilExpiry(),
                    ];
                }),
                'images' => $product->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'url' => asset('storage/' . $img->image_path),
                    ];
                }),
                'categories' => $product->categories->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name_ar' => $cat->name_ar,
                        'name_en' => $cat->name_en,
                    ];
                }),
            ],
        ], 200);
    }
}

