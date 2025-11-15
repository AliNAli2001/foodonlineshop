<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories.
     */
    public function index(Request $request)
    {
        $categories = Category::with('image')
            ->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $categories->items(),
            'pagination' => [
                'total' => $categories->total(),
                'per_page' => $categories->perPage(),
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get category details with products.
     */
    public function show($categoryId)
    {
        $category = Category::with('image')->findOrFail($categoryId);
        $products = $category->products()
            ->with(['inventories', 'primaryImage'])
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name_ar' => $category->name_ar,
                'name_en' => $category->name_en,
                'type' => $category->type,
                'featured' => $category->featured,
                'image' => $category->image ? asset('storage/' . $category->image->image_path) : null,
                'products' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ],
        ], 200);
    }
}

