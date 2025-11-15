<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Add item to cart (AJAX).
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::with('inventories')->findOrFail($validated['product_id']);
        $maxOrderItems = Setting::get('max_order_items', 50);
        $maxPerProduct = $product->max_order_item ?? $maxOrderItems;

        if ($validated['quantity'] > $maxPerProduct) {
            return response()->json([
                'success' => false,
                'message' => "Maximum quantity for this product is {$maxPerProduct}",
            ], 422);
        }

        // Check total available stock across all expiry dates
        if ($validated['quantity'] > $product->getTotalAvailableStock()) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'product' => [
                'id' => $product->id,
                'name_en' => $product->name_en,
                'name_ar' => $product->name_ar,
                'price' => $product->price,
                'quantity' => $validated['quantity'],
            ],
        ], 200);
    }

    /**
     * Update cart item quantity (AJAX).
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $product = Product::with('inventories')->findOrFail($validated['product_id']);
        $maxOrderItems = Setting::get('max_order_items', 50);
        $maxPerProduct = $product->max_order_item ?? $maxOrderItems;

        if ($validated['quantity'] > 0 && $validated['quantity'] > $maxPerProduct) {
            return response()->json([
                'success' => false,
                'message' => "Maximum quantity for this product is {$maxPerProduct}",
            ], 422);
        }

        if ($validated['quantity'] > 0 && $validated['quantity'] > $product->getTotalAvailableStock()) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
        ], 200);
    }

    /**
     * Remove item from cart (AJAX).
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart',
        ], 200);
    }
}

