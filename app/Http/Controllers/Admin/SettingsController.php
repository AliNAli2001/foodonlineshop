<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Show settings form.
     */
    public function index()
    {
        $settings = Setting::getInstance();
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'dollar_exchange_rate' => 'required|numeric|min:0.0001',
            'general_minimum_alert_quantity' => 'required|integer|min:0',
            'max_order_items' => 'required|integer|min:1',
        ]);

        $settings = Setting::getInstance();
        $settings->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم تحديث الإعدادات بنجاح.');
    }
}

