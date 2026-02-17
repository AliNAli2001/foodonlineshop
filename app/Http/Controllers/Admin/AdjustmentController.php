<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdjustmentController extends Controller
{
    /**
     * Show all adjustments.
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Adjustment::query()->orderByDesc('created_at');

        if (is_string($startDate) && $startDate !== '') {
            $query->whereDate(DB::raw('COALESCE(date, created_at)'), '>=', $startDate);
        }

        if (is_string($endDate) && $endDate !== '') {
            $query->whereDate(DB::raw('COALESCE(date, created_at)'), '<=', $endDate);
        }

        $adjustments = $query->paginate(15)->withQueryString();

        return Inertia::render('admin.adjustments.index', [
            'adjustments' => $adjustments,
            'filters' => [
                'start_date' => is_string($startDate) ? $startDate : '',
                'end_date' => is_string($endDate) ? $endDate : '',
            ],
        ]);
    }

    /**
     * Show create adjustment form.
     */
    public function create()
    {
        return Inertia::render('admin.adjustments.create');
    }

    /**
     * Store a new adjustment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'nullable|integer',
            'adjustment_type' => 'required|in:gain,loss',
            'reason' => 'required|string',
            'date' => 'required|date',
        ]);

        Adjustment::create($validated);

        return redirect()->route('admin.adjustments.index')
            ->with('success', 'تم حفظ التعديل بنجاح.');
    }

    /**
     * Show adjustment details.
     */
    public function show($id)
    {
        $adjustment = Adjustment::findOrFail($id);
        return Inertia::render('admin.adjustments.show', compact('adjustment'));
    }

    /**
     * Show edit adjustment form.
     */
    public function edit($id)
    {
        $adjustment = Adjustment::findOrFail($id);
        return Inertia::render('admin.adjustments.edit', compact('adjustment'));
    }

    /**
     * Update adjustment.
     */
    public function update(Request $request, $id)
    {
        $adjustment = Adjustment::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'nullable|integer',
            'adjustment_type' => 'required|in:gain,loss',
            'reason' => 'required|string',
        ]);

        $adjustment->update($validated);

        return redirect()->route('admin.adjustments.index')
            ->with('success', 'تم تحديث التعديل بنجاح.');
    }

    /**
     * Delete adjustment.
     */
    public function destroy($id)
    {
        $adjustment = Adjustment::findOrFail($id);
        $adjustment->delete();

        return redirect()->route('admin.adjustments.index')
            ->with('success', 'تم حذف التعديل بنجاح.');
    }
}



