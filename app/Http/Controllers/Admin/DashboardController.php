<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Services\InventoryQueryService;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard.
     */
    public function index(InventoryQueryService $service)
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalProducts = Product::count();
        $totalClients = Client::count();
        $lowStockProducts = $service->lowStockProducts();



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
