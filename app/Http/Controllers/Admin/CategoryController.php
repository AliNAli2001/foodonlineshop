<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Show all categories.
     */
    public function index()
    {
        $categories = Category::with('image')->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show create category form.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'featured' => 'boolean',
        ]);

        Category::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'featured' => $validated['featured'] ?? false,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show edit category form.
     */
    public function edit($categoryId)
    {
        $category = Category::with('image')->findOrFail($categoryId);
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update category.
     */
    public function update(Request $request, $categoryId)
    {
        $category = Category::findOrFail($categoryId);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'featured' => 'boolean',
        ]);

        $category->update([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'featured' => $validated['featured'] ?? false,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete category.
     */
    public function destroy($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}

