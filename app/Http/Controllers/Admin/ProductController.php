<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Company;
use App\Models\Tag;
use App\Models\ProductImage;
use App\Models\Setting;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['stock', 'company', 'category', 'tags', 'primaryImage'])
            ->select('products.*')
            ->leftJoin('product_stocks', 'products.id', '=', 'product_stocks.product_id');

        $query->when($request->search, function ($q) use ($request) {
            $search = trim($request->search);
            $q->where(function ($subq) use ($search) {
                if (is_numeric($search)) {
                    $subq->where('products.id', $search);
                }
                $subq->orWhere('products.name_ar', 'like', "%{$search}%")
                    ->orWhere('products.name_en', 'like', "%{$search}%");
            });
        });

        $query->when($request->min_price, function ($q) use ($request) {
            $q->where('products.selling_price', '>=', $request->min_price);
        });

        $query->when($request->max_price, function ($q) use ($request) {
            $q->where('products.selling_price', '<=', $request->max_price);
        });

        $query->when($request->min_stock, function ($q) use ($request) {
            $q->where('product_stocks.available_quantity', '>=', $request->min_stock);
        });

        $query->when($request->max_stock, function ($q) use ($request) {
            $q->where('product_stocks.available_quantity', '<=', $request->max_stock);
        });

        $query->when($request->low_stock, function ($q) {
            $q->where('products.minimum_alert_quantity', '>', 0)
                ->where('product_stocks.available_quantity', '<', DB::raw('products.minimum_alert_quantity'));
        });

        $query->when($request->sort, function ($q) use ($request) {
            $sort = $request->sort;
            $order = $request->order ?? 'asc';
            if ($sort === 'stock') {
                $q->orderBy('product_stocks.available_quantity', $order);
            } elseif ($sort === 'price') {
                $q->orderBy('products.selling_price', $order);
            } elseif ($sort === 'created_at') {
                $q->orderBy('products.created_at', $order);
            }
        }, function ($q) {
            $q->latest();
        });

        $products = $query->paginate(15)->withQueryString();

        return Inertia::render('admin.products.index', compact('products'));
    }
    // public function index(Request $request)
    // {
    //     if ($request->search) {
    //         $search = trim($request->search);

    //         $products = Product::with(['stock', 'company', 'category', 'tags', 'primaryImage'])
    //             ->when($search, function ($query) use ($search) {

    //                 $query->where(function ($q) use ($search) {

    //                     // لو الرقم المدخل رقم → ابحث بالـ ID
    //                     if (is_numeric($search)) {
    //                         $q->where('id', $search);
    //                     }

    //                     // البحث بالاسم عربي أو إنجليزي
    //                     $q->orWhere('name_ar', 'like', "%{$search}%")
    //                         ->orWhere('name_en', 'like', "%{$search}%");
    //                 });
    //             })
    //             ->latest()
    //             ->paginate(15)
    //             ->withQueryString();
    //     } else {
    //         $products = Product::with(['stock', 'company', 'category', 'tags', 'primaryImage'])->paginate(15);
    //     }



    //     return Inertia::render('admin.products.index', compact('products'));
    // }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        $companies = Company::all();
        $maxOrderItems = Setting::get('max_order_items');
        $generalMinimumAlertQuantity = Setting::get('general_minimum_alert_quantity');

        return Inertia::render('admin.products.create', compact(
            'categories',
            'tags',
            'companies',
            'maxOrderItems',
            'generalMinimumAlertQuantity'
        ));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $request->merge([
            'enable_initial_stock' => $request->boolean('enable_initial_stock') ? 1 : 0,
            'featured' => $request->boolean('featured'),
        ]);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'selling_price' => 'required|numeric|min:0.001',
            'max_order_item' => 'nullable|integer|min:1',
            'minimum_alert_quantity' => 'nullable|integer|min:0',
            'featured' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'company_id' => 'nullable|exists:companies,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',

            // Initial inventory batch (optional)
            'enable_initial_stock' => 'boolean',
            'initial_stock_quantity' => 'exclude_unless:enable_initial_stock,1|required|integer|min:1',
            'initial_batch_number' => 'exclude_unless:enable_initial_stock,1|required|string|max:100',
            'initial_expiry_date' => 'exclude_unless:enable_initial_stock,1|nullable|date|after_or_equal:today',
            'initial_cost_price' => 'exclude_unless:enable_initial_stock,1|required|numeric|min:0.001',

            // Images
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:3072',
        ]);

        try {
            $product = $this->productService->createProduct($validated);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $this->storeProductImages($request->file('images'), $product);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'تمت إضافة المنتج بنجاح.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($productId)
    {
        $product = Product::with(['category', 'tags', 'images'])->findOrFail($productId);
        $categories = Category::all();
        $companies = Company::all();
        $tags = Tag::all();

        return Inertia::render('admin.products.edit', compact('product', 'tags', 'companies', 'categories'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $productId)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'selling_price' => 'required|numeric|min:0.001',
            'max_order_item' => 'nullable|integer|min:1',
            'minimum_alert_quantity' => 'nullable|integer|min:0',
            'featured' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',

            'company_id' => 'nullable|exists:companies,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',

            // Image management
            'image_ids_to_delete' => 'nullable|array',
            'image_ids_to_delete.*' => 'exists:product_images,id',
            'primary_image_id' => 'nullable|exists:product_images,id',

            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:3072',
        ]);


        try {
            $product = $this->productService->updateProduct($productId, $validated);
            $product->refresh();

            // Delete selected images
            if (!empty($validated['image_ids_to_delete'])) {
                $this->deleteProductImages($validated['image_ids_to_delete'], $product);
            }

            // Upload new images
            if ($request->hasFile('images')) {
                $this->storeProductImages($request->file('images'), $product);
            }


            /*
            |----------------------------------
            | 3️⃣ تعيين الصورة الرئيسية
            |----------------------------------
            */
            $primayWasSet = false;
            if (!empty($validated['primary_image_id'])) {


                $primaryImage = $product->images()
                    ->where('id', $validated['primary_image_id'])
                    ->first();





                if ($primaryImage) {
                    // إلغاء أي صورة رئيسية حالية
                    $product->images()->where('is_primary', true)->update(['is_primary' => false]);
                    $product->load('images');
                    // تعيين المختارة كرئيسية
                    $product->images()
                        ->where('id', $validated['primary_image_id'])
                        ->first()->update(['is_primary' => true]);
                    $primayWasSet = true;
                }
            }



            /*
            |----------------------------------
            | 4️⃣ ضمان وجود صورة رئيسية دائماً
            |----------------------------------
            */
            if (!$primayWasSet) {
                //dd($validated['primary_image_id']);
                $firstImage = $product->images()->first();
                if ($firstImage) {
                    $firstImage->update(['is_primary' => true]);
                }
            }


            return redirect()->route('admin.products.show', $product->id)
                ->with('success', 'تم تحديث بيانات المنتج بنجاح.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified product.
     */
    public function show($productId)
    {
        $product = Product::with([
            'inventoryBatches' => fn($q) => $q->orderBy('expiry_date'),
            'category',
            'company',
            'tags',
            'images' => fn($q) => $q->orderBy('is_primary', 'desc')->orderBy('id')
        ])->findOrFail($productId);


        $previousProduct = Product::where('id', '<', $product->id)
            ->orderBy('id', 'desc')
            ->first();

        $nextProduct = Product::where('id', '>', $product->id)
            ->orderBy('id', 'asc')
            ->first();
        return Inertia::render('admin.products.show', compact('product', 'previousProduct', 'nextProduct'));
    }

    /**
     * Remove the specified product.
     */
    public function destroy($productId)
    {
        try {
            $product = Product::findOrFail($productId);

            // Delete all images from storage
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_url);
                $image->delete();
            }

            // Delete product directory (in case any leftover files)
            Storage::disk('public')->deleteDirectory("products/{$product->id}");

            // Delete the product (cascades to batches via foreign key)
            $this->productService->deleteProduct($productId);

            return redirect()->route('admin.products.index')
                ->with('success', 'تم حذف المنتج بنجاح.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Helper: Store uploaded images for a product.
     */
    private function storeProductImages(array $images, Product $product): void
    {
        $folder = "products/{$product->id}";

        foreach ($images as $index => $image) {
            $isPrimary = $index === 0 && $product->images()->where('is_primary', true)->doesntExist();

            $imageName = uniqid('img_', true) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs($folder, $imageName, 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => $path,
                'is_primary' => $isPrimary,
            ]);
        }
    }

    /**
     * Helper: Delete product images.
     */
    private function deleteProductImages(array $imageIds, Product $product): void
    {
        foreach ($imageIds as $imageId) {
            $image = ProductImage::find($imageId);

            if ($image && $image->product_id === $product->id) {
                Storage::disk('public')->delete($image->image_url);
                $image->delete();
            }
        }
    }

    public function batches($productId)
    {
        $product = Product::with(['inventoryBatches' => fn($q) => $q->orderBy('expiry_date')])->findOrFail($productId);
        return response()->json($product);
    }
}



