<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Client;
use App\Models\Inventory;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard.
     */
    public function index()
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalProducts = Product::count();
        $totalClients = Client::count();
        $lowStockProducts = Inventory::where('stock_quantity', '<=', 'minimum_alert_quantity')
            ->with('product')
            ->get();

        $recentOrders = Order::latest()->take(5)->with('client')->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'pendingOrders',
            'totalProducts',
            'totalClients',
            'lowStockProducts',
            'recentOrders'
        ));
    }
}

