<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loss;
use Illuminate\Http\Request;

class LossController extends Controller
{
    /**
     * Show all losses.
     */
    public function index()
    {
        $losses = Loss::paginate(15);
        return view('admin.losses.index', compact('losses'));
    }

    /**
     * Show create loss form.
     */
    public function create()
    {
        return view('admin.losses.create');
    }

    /**
     * Store a new loss.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'nullable|integer',
            'type' => 'required|in:shipping_costs,general_costs,delivery_costs,other',
            'reason' => 'required|string',
        ]);

        Loss::create($validated);

        return redirect()->route('admin.losses.index')
            ->with('success', 'تمت إضافة الخسارة بنجاح.');
    }

    /**
     * Show loss details.
     */
    public function show($lossId)
    {
        $loss = Loss::findOrFail($lossId);
        return view('admin.losses.show', compact('loss'));
    }

    /**
     * Show edit loss form.
     */
    public function edit($lossId)
    {
        $loss = Loss::findOrFail($lossId);
        return view('admin.losses.edit', compact('loss'));
    }

    /**
     * Update loss.
     */
    public function update(Request $request, $lossId)
    {
        $loss = Loss::findOrFail($lossId);

        $validated = $request->validate([
            'quantity' => 'nullable|integer',
            'type' => 'required|in:shipping_costs,general_costs,delivery_costs,other',
            'reason' => 'required|string',
        ]);

        $loss->update($validated);

        return redirect()->route('admin.losses.index')
            ->with('success', 'تم تحديث بيانات الخسارة بنجاح.');
    }

    /**
     * Delete loss.
     */
    public function destroy($lossId)
    {
        $loss = Loss::findOrFail($lossId);
        $loss->delete();

        return redirect()->route('admin.losses.index')
            ->with('success', 'تم حذف بيانات الخسارة بنجاح.');
    }
}