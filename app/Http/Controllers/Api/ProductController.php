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
        $language = $request->query('lang', 'en');
        $query = Product::query();

        // Search by name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $nameColumn = $language === 'ar' ? 'name_ar' : 'name_en';
            $query->where($nameColumn, 'like', "%{$search}%");
        }

        // Filter by categories
        if ($request->filled('categories')) {
            $categories = $request->input('categories');
            if (!is_array($categories)) {
                $categories = explode(',', $categories);
            }
            $query->whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('category_id', $categories);
            });
        }

        // Filter featured products
        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        $products = $query->with(['inventory', 'primaryImage', 'categories'])->paginate(12);

        return response()->json($products);
    }

    /**
     * Get single product details.
     */
    public function show(Request $request, $product)
    {
        $language = $request->query('lang', 'en');
        $product = Product::with(['inventory', 'images', 'categories'])->findOrFail($product);

        return response()->json($product);
    }

    /**
     * Get products by category.
     */
    public function byCategory(Request $request, $category)
    {
        $language = $request->query('lang', 'en');
        $category = Category::findOrFail($category);
        $products = $category->products()->with(['inventory', 'primaryImage'])->paginate(12);

        return response()->json($products);
    }

    /**
     * Get featured products.
     */
    public function featured(Request $request)
    {
        $language = $request->query('lang', 'en');
        $products = Product::where('featured', true)->with(['inventory', 'primaryImage'])->paginate(12);

        return response()->json($products);
    }
}