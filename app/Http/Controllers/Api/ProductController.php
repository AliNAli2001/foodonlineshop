<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ProductResource;

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

        // Filter by category (one-to-many relationship)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by company (one-to-many relationship)
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        // Filter by tags (many-to-many relationship)
        if ($request->filled('tags')) {
            $tags = $request->input('tags');
            if (!is_array($tags)) {
                $tags = explode(',', $tags);
            }
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('tags.id', $tags);
            });
        }

        // Filter featured products
        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('selling_price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('selling_price', '<=', $request->input('max_price'));
        }

        // Sort options
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $allowedSortFields = ['created_at', 'selling_price', 'name_en', 'name_ar'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Eager load relationships
        $products = $query->with(['primaryImage', 'category', 'company', 'tags'])
            ->paginate($request->input('per_page', 12));

        return ProductResource::collection($products);
    }

    /**
     * Get single product details.
     */
    public function show(Request $request, $product)
    {
        $language = $request->query('lang', 'en');
        $product = Product::with([
            
            'images',
            'category',
            'company',
            'tags',
            
        ])->findOrFail($product);

        return new ProductResource($product);
    }
}