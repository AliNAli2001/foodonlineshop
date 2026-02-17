<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Show all categories.
     */
    public function index()
    {
        $categories = Category::paginate(15);
        return Inertia::render('admin.categories.index', compact('categories'));
    }

    /**
     * Show create category form.
     */
    public function create()
    {
        return Inertia::render('admin.categories.create');
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        if (is_array($request->input('entries'))) {
            $validated = $request->validate([
                'entries' => 'required|array|min:1',
                'entries.*.name_ar' => 'required|string|max:255',
                'entries.*.name_en' => 'required|string|max:255',
                'entries.*.featured' => 'nullable|boolean',
            ]);

            $createdCount = 0;
            foreach ($validated['entries'] as $entry) {
                Category::create([
                    'name_ar' => $entry['name_ar'],
                    'name_en' => $entry['name_en'],
                    'featured' => (bool) ($entry['featured'] ?? false),
                    'category_image' => null,
                ]);
                $createdCount++;
            }

            return redirect()->route('admin.categories.index')
                ->with('success', "Created {$createdCount} categories successfully.");
        }

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'featured' => 'boolean',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('category_image')) {
            $imagePath = $request->file('category_image')->store('categories', 'public');
        }

        Category::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'featured' => $validated['featured'] ?? false,
            'category_image' => $imagePath,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم إنشاء الصنف بنجاح.');
    }

    /**
     * Show edit category form.
     */
    public function edit($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        return Inertia::render('admin.categories.edit', compact('category'));
    }

    /**
     * Show category details with products.
     */
    public function show($categoryId)
    {
        $category = Category::with('products')->findOrFail($categoryId);
        $products = $category->products()->paginate(15);

        return Inertia::render('admin.categories.show', compact('category', 'products'));
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
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $category->category_image;
        if ($request->hasFile('category_image')) {
            $imagePath = $request->file('category_image')->store('categories', 'public');
        }

        $category->update([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'featured' => $validated['featured'] ?? false,
            'category_image' => $imagePath,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم تحديث الصنف بنجاح.');
    }

    /**
     * Delete category.
     */
    public function destroy($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم حذف الصنف بنجاح.');
    }
}




