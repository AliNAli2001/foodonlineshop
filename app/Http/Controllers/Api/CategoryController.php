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
        $language = $request->query('lang', 'en');
        $categories = Category::paginate(12);

        return response()->json($categories);
    }

    /**
     * Get featured categories.
     */
    public function featured(Request $request)
    {
        $language = $request->query('lang', 'en');
        $categories = Category::where('featured', true)->with('image')->get();

        return response()->json($categories);
    }

    /**
     * Get category details with products.
     */
    public function show(Request $request, $category)
    {
        $language = $request->query('lang', 'en');
        $category = Category::with('image')->findOrFail($category);
        $products = $category->products()->with(['inventory', 'primaryImage'])->paginate(12);

        return response()->json([
            'category' => $category,
            'products' => $products,
        ]);
    }
}