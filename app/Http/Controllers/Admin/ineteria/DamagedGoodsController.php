<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\DamagedGoodsService;
use Illuminate\Http\Request;

class DamagedGoodsController extends Controller
{
    protected DamagedGoodsService $damagedGoodsService;

    public function __construct(DamagedGoodsService $damagedGoodsService)
    {
        $this->damagedGoodsService = $damagedGoodsService;
    }
    /**
     * Display a listing of damaged goods.
     */
    public function index()
    {
        $damagedGoods = $this->damagedGoodsService->getAllDamagedGoods();
        return view('admin.damaged-goods.index', compact('damagedGoods'));
    }

    /**
     * Show the form for creating a new damaged goods record.
     */
    public function create()
    {
        $products = Product::with(['inventoryBatches' => function ($q) {
            $q->where('available_quantity', '>', 0)
              ->where(function ($q) {
                  $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now()->toDateString());
              })
              ->where('status', 'active');
        }])->get();

        return view('admin.damaged-goods.create', compact('products'));
    }

    /**
     * AJAX: Get available batches for a product.
     */
    public function getProductBatches(Product $product)
    {
        $batches = $this->damagedGoodsService->getAvailableBatches($product);
        return response()->json($batches);
    }

    /**
     * Search products for autocomplete.
     */
    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');

        $products = Product::with('stock')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_en', 'LIKE', "%{$search}%")
                      ->orWhere('name_ar', 'LIKE', "%{$search}%");
                });
            })
            ->limit(30)
            ->get()
            ->map(function ($product) {
                $availableStock = $product->stock_available_quantity;

                return [
                    'id' => $product->id,
                    'text' => "{$product->name_en} (متاح: {$availableStock})",
                    'available_stock' => $availableStock,
                ];
            });

        return response()->json([
            'results' => $products
        ]);
    }

    /**
     * Store a new damaged goods record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'source' => 'required|in:inventory,invoice',
            'inventory_batch_id' => 'required_if:source,inventory|nullable|exists:inventory_batches,id',
           
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->damagedGoodsService->createDamagedGoods($validated);

            return redirect()->route('admin.damaged-goods.index')
                ->with('success', 'تم تسجيل بيانات البضاعة التالفة بنجاح.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified damaged goods record.
     */
    public function show($damagedGoodsId)
    {
        try {
            $damagedGoods = $this->damagedGoodsService->getDamagedGoods($damagedGoodsId);
            return view('admin.damaged-goods.show', compact('damagedGoods'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified damaged goods record.
     */
    public function destroy($damagedGoodsId)
    {
        try {
            $this->damagedGoodsService->deleteDamagedGoods($damagedGoodsId);

            return redirect()->route('admin.damaged-goods.index')
                ->with('success', 'تم حذف بيانات البضاعة التالفة بنجاح.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}