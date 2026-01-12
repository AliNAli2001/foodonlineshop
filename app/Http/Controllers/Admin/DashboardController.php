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
       
        $pendingOrders = Order::where('status', 'pending')->count();
        $confirmedOrders = Order::where('status', 'confirmed')->count();
       
        $totalClients = Client::count();
        $lowStockProducts = $service->lowStockProducts();
        $lowStockProductsCount = $service->lowStockProducts()->count() ?? 0;



        $recentOrders = Order::latest()->take(5)->with('client')->get();

        return view('admin.dashboard', compact(
            'lowStockProductsCount',
            'pendingOrders',
            'confirmedOrders',
            'totalClients',
            'lowStockProducts',
            'recentOrders'
        ));
    }
}
