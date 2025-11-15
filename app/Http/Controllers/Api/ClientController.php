<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Get client profile.
     */
    public function profile(Request $request)
    {
        $client = $request->user();

        return response()->json($client);
    }

    /**
     * Update client profile.
     */
    public function updateProfile(Request $request)
    {
        $client = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_details' => 'nullable|string',
            'language_preference' => 'required|in:ar,en',
            'promo_consent' => 'boolean',
        ]);

        $client->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'client' => $client,
        ]);
    }

    /**
     * Get client orders.
     */
    public function orders(Request $request)
    {
        $client = $request->user();
        $orders = $client->orders()->latest()->paginate(10);

        return response()->json($orders);
    }

    /**
     * Get single order details.
     */
    public function orderDetails(Request $request, $orderId)
    {
        $client = $request->user();
        $order = $client->orders()->findOrFail($orderId);
        $items = $order->items()->with('product')->get();
        $returnedItems = $order->returnedItems()->with('orderItem.product')->get();

        return response()->json([
            'order' => $order,
            'items' => $items,
            'returnedItems' => $returnedItems,
        ]);
    }
}