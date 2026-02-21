<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Services\InventoryService;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;
    protected InventoryMovementService $inventoryMovementService;

    

    public function __construct(InventoryService $inventoryService, InventoryMovementService $inventoryMovementService)
    {
        $this->inventoryService = $inventoryService;
        $this->inventoryMovementService = $inventoryMovementService;
    }
    /**
     * Show form to add a new batch (restock) for a product.
     */
    public function create($productId)
    {
        $product = Product::findOrFail($productId);
        return Inertia::render('admin.inventory.create', compact('product'));
    }

    /**
     * Store a new inventory batch (restock).
     */
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'batch_number' => 'required|string|max:100',
            'expiry_date' => 'nullable|date|after_or_equal:today',
            'available_quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0.001',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->inventoryService->createBatch($productId, $validated);

            return redirect()->route('admin.inventory.product', $product->id)
                ->with('success', __('admin.inventory.batch_created'));
        } catch (InsufficientStockException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show all products that have inventory batches.
     */
    public function index()
    {
        $products = $this->inventoryService->getProductsWithBatchesOrderedByLowestStock();
        $summary = $this->inventoryService->getIndexSummary();
        return Inertia::render('admin.inventory.index', compact('products', 'summary'));
    }

    /**
     * Show all products that have inventory batches.
     */
    public function indexLowStockProducts()
    {
        $products = $this->inventoryService->indexLowStockProducts();
        $summary = $this->inventoryService->getIndexSummary();
        return Inertia::render('admin.inventory.index', compact('products', 'summary'));
    }

    /**
     * Show bulk add batches form.
     */
    public function bulkCreate()
    {
        return Inertia::render('admin.inventory.bulk');
    }

    

    /**
     * Show all batches and movements for a specific product.
     */
    public function product($productId)
    {
        $product = $this->inventoryService->getProductBatches($productId);
        $movements = $this->inventoryMovementService->getProductMovements($productId);

        return Inertia::render('admin.inventory.product.show', [
            'product' => $product,
            'batches' => $product->inventoryBatches,
            'movements' => $movements,
        ]);
    }

    /**
     * Show details of a specific batch + its movement history.
     */
    public function show($batchId)
    {
        $batch = $this->inventoryService->getBatch($batchId);
        $product = $batch->product;
        $movements = $this->inventoryMovementService->getBatchMovements($batchId);

        return Inertia::render('admin.inventory.show', compact('product', 'batch', 'movements'));
    }

    /**
     * Show form to edit/adjust an existing batch.
     */
    public function edit($batchId)
    {
        $batch = $this->inventoryService->getBatch($batchId);
        $product = $batch->product;

        return Inertia::render('admin.inventory.edit', compact('product', 'batch'));
    }

    /**
     * Update an existing batch (manual adjustment).
     */
    public function update(Request $request, $batchId)
    {
        $validated = $request->validate([
            'batch_number' => 'required|string|max:100',
            'expiry_date' => 'nullable|date',
            'available_quantity' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0.001',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $batch = $this->inventoryService->updateBatch($batchId, $validated);

            return redirect()->route('admin.inventory.show', $batch->id)
                ->with('success', __('admin.inventory.batch_updated'));
        } catch (InsufficientStockException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Bulk create batches for multiple products.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_number' => 'required|string|max:100',
            'items.*.available_quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0.001',
            'items.*.expiry_date' => 'nullable|date|after_or_equal:today',
            'items.*.reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                $this->inventoryService->createBatch((int) $item['product_id'], [
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'available_quantity' => (int) $item['available_quantity'],
                    'cost_price' => (float) $item['cost_price'],
                    'reason' => $item['reason'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.inventory.index')->with('success', __('admin.inventory.bulk_created'));
    }

    /**
     * Search products for bulk inventory add form.
     */
    public function searchProducts(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_en', 'LIKE', "%{$search}%")
                        ->orWhere('name_ar', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('name_en')
            ->limit(20)
            ->get(['id', 'name_en', 'name_ar']);

        return response()->json([
            'results' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name_en' => $product->name_en,
                    'name_ar' => $product->name_ar,
                    'label' => trim(($product->name_en ?? '') . ' / ' . ($product->name_ar ?? '')),
                ];
            }),
        ]);
    }
}


