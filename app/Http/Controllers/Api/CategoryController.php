<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Http\Resources\Client\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories.
     */
    public function index(Request $request)
    {

        $categories = Category::withCount('products')->paginate(12);

        return CategoryResource::collection($categories);
    }

    /**
     * Get featured categories.
     */
    public function featured(Request $request)
    {

        $categories = Category::withCount('products')->where('featured', true)->with('image')->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Get category details with products.
     */
    public function show(Request $request, $category)
    {
        $language = $request->query('lang', 'en');

        $category = Category::with(['products' => function ($query) {
            $query->with(['primaryImage', 'company', 'tags']);
        }])->findOrFail($category);
        return new CategoryResource($category);
    }
}
