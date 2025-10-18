<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Show all products with filtering and search.
     */
    public function index(Request $request)
    {
        $language = session('language_preference', 'en');
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
            $query->whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('category_id', $categories);
            });
        }

        // Filter featured products
        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        $products = $query->with(['inventory', 'primaryImage', 'categories'])->paginate(12);
        $categories = Category::all();

        return view('products.index', compact('products', 'categories', 'language'));
    }

    /**
     * Show single product details.
     */
    public function show($productId)
    {
        $language = session('language_preference', 'en');
        $product = Product::with(['inventory', 'images', 'categories'])->findOrFail($productId);

        return view('products.show', compact('product', 'language'));
    }

    /**
     * Get products by category.
     */
    public function byCategory($categoryId)
    {
        $language = session('language_preference', 'en');
        $category = Category::findOrFail($categoryId);
        $products = $category->products()->with(['inventory', 'primaryImage'])->paginate(12);

        return view('products.by-category', compact('category', 'products', 'language'));
    }

    /**
     * Get featured products.
     */
    public function featured()
    {
        $language = session('language_preference', 'en');
        $products = Product::where('featured', true)->with(['inventory', 'primaryImage'])->paginate(12);

        return view('products.featured', compact('products', 'language'));
    }
}

