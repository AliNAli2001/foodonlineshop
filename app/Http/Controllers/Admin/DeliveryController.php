<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * Show all delivery personnel.
     */
    public function index()
    {
        $deliveryPersons = Delivery::paginate(15);
        return view('admin.delivery.index', compact('deliveryPersons'));
    }

    /**
     * Show create delivery form.
     */
    public function create()
    {
        return view('admin.delivery.create');
    }

    /**
     * Store a new delivery person.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:delivery,phone',
            'email' => 'nullable|email|unique:delivery,email',
            'status' => 'required|in:available,busy,inactive',
            
            'info' => 'nullable|string',
            'phone_plus' => 'nullable|string|max:20',
        ]);

        Delivery::create($validated);

        return redirect()->route('admin.delivery.index')
            ->with('success', 'Delivery person created successfully.');
    }

    /**
     * Show delivery person details.
     */
    public function show($deliveryId)
    {
        $delivery = Delivery::findOrFail($deliveryId);
        $orders = $delivery->orders()->paginate(15);

        return view('admin.delivery.show', compact('delivery', 'orders'));
    }

    
    /**
     * Show edit delivery form.
     */
    public function edit($deliveryId)
    {
        $delivery = Delivery::findOrFail($deliveryId);
        return view('admin.delivery.edit', compact('delivery'));
    }

    /**
     * Update delivery person.
     */
    public function update(Request $request, $deliveryId)
    {
        $delivery = Delivery::findOrFail($deliveryId);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:delivery,phone,' . $deliveryId,
            'email' => 'nullable|email|unique:delivery,email,' . $deliveryId,
            'status' => 'required|in:available,busy,inactive',
            'info' => 'nullable|string',
            'phone_plus' => 'nullable|string|max:20',
        ]);

        $delivery->update($validated);

        return redirect()->route('admin.delivery.index')
            ->with('success', 'Delivery person updated successfully.');
    }

    /**
     * Delete delivery person.
     */
    public function destroy($deliveryId)
    {
        $delivery = Delivery::findOrFail($deliveryId);
        $delivery->delete();

        return redirect()->route('admin.delivery.index')
            ->with('success', 'Delivery person deleted successfully.');
    }
}

