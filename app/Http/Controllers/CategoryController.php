<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Show all categories.
     */
    public function index()
    {
        $language = session('language_preference', 'en');
        $categories = Category::with('image')->paginate(12);

        return view('categories.index', compact('categories', 'language'));
    }

    /**
     * Show featured categories.
     */
    public function featured()
    {
        $language = session('language_preference', 'en');
        $categories = Category::where('featured', true)->with('image')->get();

        return view('categories.featured', compact('categories', 'language'));
    }

    /**
     * Show category details with products.
     */
    public function show($categoryId)
    {
        $language = session('language_preference', 'en');
        $category = Category::with('image')->findOrFail($categoryId);
        $products = $category->products()->with(['inventory', 'primaryImage'])->paginate(12);

        return view('categories.show', compact('category', 'products', 'language'));
    }
}

