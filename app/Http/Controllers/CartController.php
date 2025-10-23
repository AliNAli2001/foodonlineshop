<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Show cart page.
     */
    public function index()
    {
        $language = session('language_preference', 'en');
        return view('cart.index', compact('language'));
    }

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

        // Return product data for localStorage storage
        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'product' => [
                'id' => $product->id,
                'name' => $product->name_en,
                'price' => $product->price,
                'quantity' => $validated['quantity'],
            ],
        ]);
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

        $cart = session()->get('cart', []);
        $productId = $validated['product_id'];

        if ($validated['quantity'] <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['quantity'] = $validated['quantity'];
        }

        session(['cart' => $cart]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
        ]);
    }

    /**
     * Remove item from cart (AJAX).
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $cart = session()->get('cart', []);
        unset($cart[$validated['product_id']]);

        session(['cart' => $cart]);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart',
        ]);
    }

    /**
     * Clear entire cart.
     */
    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }
}

