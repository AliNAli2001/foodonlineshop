<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    /**
     * Show all Tag.
     */
    public function index()
    {
        $tags = Tag::paginate(15);
        return Inertia::render('admin.tags.index', compact('tags'));
    }

    /**
     * Show create tag form.
     */
    public function create()
    {
        return Inertia::render('admin.tags.create');
    }

    /**
     * Store a new tag.
     */
    public function store(Request $request)
    {
        if (is_array($request->input('entries'))) {
            $validated = $request->validate([
                'entries' => 'required|array|min:1',
                'entries.*.name_ar' => 'required|string|max:255',
                'entries.*.name_en' => 'required|string|max:255',
            ]);

            $createdCount = 0;
            foreach ($validated['entries'] as $entry) {
                Tag::create([
                    'name_ar' => $entry['name_ar'],
                    'name_en' => $entry['name_en'],
                ]);
                $createdCount++;
            }

            return redirect()->route('admin.tags.index')
                ->with('success', "Created {$createdCount} tags successfully.");
        }

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

 

        $tag = Tag::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'tag' => $tag->only(['id', 'name_ar', 'name_en']),
            ], 201);
        }

        return redirect()->route('admin.tags.index')
            ->with('success', 'تم إنشاء الوسم بنجاح.');
    }

    /**
     * Show edit tag form.
     */
    public function edit($tagId)
    {
        $tag = Tag::findOrFail($tagId);
        return Inertia::render('admin.tags.edit', compact('tag'));
    }

    /**
     * Show tag details with products.
     */
    public function show($tagId)
    {
        $tag = Tag::with('products')->findOrFail($tagId);
        $products = $tag->products()->paginate(15);

        return Inertia::render('admin.tags.show', compact('tag', 'products'));
    }

    /**
     * Update tag.
     */
    public function update(Request $request, $tagId)
    {
        $tag = Tag::findOrFail($tagId);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        $tag->update([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
        ]);

        return redirect()->route('admin.tags.index')
            ->with('success', 'تم تحديث الوسم بنجاح.');
    }

    /**
     * Delete tag.
     */
    public function destroy($tagId)
    {
        $tag = Tag::findOrFail($tagId);
        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'تم حذف الوسم بنجاح.');
    }
}



