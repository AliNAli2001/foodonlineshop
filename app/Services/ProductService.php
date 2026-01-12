<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new product with optional initial inventory.
     */
    public function createProduct(array $data): Product
    {


    
        $product = Product::create([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'description_ar' => $data['description_ar'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'selling_price' => $data['selling_price'] ?? 0,
            'max_order_item' => $data['max_order_item'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'minimum_alert_quantity' => $data['minimum_alert_quantity'] ?? 5,
            'featured' => $data['featured'] ?? false,
        ]);
        // Attach tags
        if (!empty($data['tags'])) {
            $product->tags()->attach($data['tags']);
        }

        // Create initial inventory batch if enabled
        if ($data['enable_initial_stock'] ?? false) {
            $this->inventoryService->createBatch($product->id, [
                'batch_number' => $data['initial_batch_number'],
                'expiry_date' => $data['initial_expiry_date'] ?? null,
                'available_quantity' => $data['initial_stock_quantity'],
                'cost_price' => $data['initial_cost_price'],
            ]);
        }


        return $product;
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(int $productId, array $data): Product
    {
        $product = Product::findOrFail($productId);

        $product->update([
            'name_ar' => $data['name_ar'] ?? $product->name_ar,
            'name_en' => $data['name_en'] ?? $product->name_en,
            'description_ar' => $data['description_ar'] ?? $product->description_ar,
            'description_en' => $data['description_en'] ?? $product->description_en,
            'selling_price' => $data['selling_price'] ?? $product->selling_price,
            'max_order_item' => $data['max_order_item'] ?? $product->max_order_item,
            'company_id' => $data['company_id'] ?? $product->company_id,
            'category_id' => $data['category_id'] ?? $product->category_id,
            'minimum_alert_quantity' => $data['minimum_alert_quantity'] ?? $product->minimum_alert_quantity,
            'featured' => $data['featured'] ?? $product->featured,
        ]);


        // Update tags if provided
        if (isset($data['tags'])) {
            $product->tags()->sync($data['tags']);
        }

        return $product;
    }

    /**
     * Get all products with pagination.
     */
    public function getAllProducts(int $perPage = 15)
    {
        return Product::with(['inventoryBatches', 'category', 'tags', 'primaryImage'])
            ->paginate($perPage);
    }

    /**
     * Get a single product with relations.
     */
    public function getProduct(int $productId): Product
    {
        return Product::with(['inventory', 'category', 'company', 'tags', 'images'])
            ->findOrFail($productId);
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(int $productId): bool
    {
        $product = Product::findOrFail($productId);
        return $product->delete();
    }
}
