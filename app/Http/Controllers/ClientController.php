<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Show client profile.
     */
    public function profile()
    {
        $clientId = session('client_id');
        if (!$clientId) {
            return redirect()->route('login');
        }

        $client = Client::find($clientId);
        return view('client.profile', compact('client'));
    }

    /**
     * Update client profile.
     */
    public function updateProfile(Request $request)
    {
        $clientId = session('client_id');
        if (!$clientId) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_details' => 'nullable|string',
            'language_preference' => 'required|in:ar,en',
            'promo_consent' => 'boolean',
        ]);

        $client = Client::find($clientId);
        $client->update($validated);

        return redirect()->route('client.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Show client orders.
     */
    public function orders()
    {
        $clientId = session('client_id');
        if (!$clientId) {
            return redirect()->route('login');
        }

        $client = Client::find($clientId);
        $orders = $client->orders()->latest()->paginate(10);

        return view('client.orders', compact('orders'));
    }

    /**
     * Show single order details.
     */
    public function orderDetails($orderId)
    {
        $clientId = session('client_id');
        if (!$clientId) {
            return redirect()->route('login');
        }

        $order = Client::find($clientId)->orders()->findOrFail($orderId);
        $items = $order->items()->with('product')->get();
        $returnedItems = $order->returnedItems()->with('orderItem.product')->get();

        return view('client.order-details', compact('order', 'items', 'returnedItems'));
    }
}

