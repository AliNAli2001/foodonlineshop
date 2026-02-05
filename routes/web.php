<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\MessageTemplateController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductTagController;
use App\Http\Controllers\Admin\ProductDetailsController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ReturnsController;
use App\Http\Controllers\Admin\DamagedGoodsController;
use App\Http\Controllers\Admin\AdjustmentController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Models\Product;
use App\Models\Category;


// Home page
Route::get('/', function () {
    $featuredProducts = Product::where('featured', true)->get();
    $featuredCategories = Category::where('featured', true)->get();
    return view('home', compact('featuredProducts', 'featuredCategories'));
})->name('home');

// Home page (kept for web)
// Client Routes (Public - no auth required for browsing) - kept for web
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// Admin Authentication Routes
Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.store');
});

Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout')->middleware('auth:admin');

// Admin Routes
Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::resource('products', AdminProductController::class);
    Route::get('/products/{product}/batches', [AdminProductController::class, 'batches'])->name('products.batches');
    //Route::get('/products/{product}/categories', [ProductCategoryController::class, 'index'])->name('products.categories.index');
    //Route::put('/products/{product}/categories', [ProductCategoryController::class, 'update'])->name('products.categories.update');

    Route::get('/products/{product}/tags', [ProductTagController::class, 'index'])->name('products.tags.index');
    Route::put('/products/{product}/tags', [ProductTagController::class, 'update'])->name('products.tags.update');

    // Categories
    Route::resource('categories', AdminCategoryController::class);

    // Companies
    Route::resource('companies', AdminCompanyController::class);

    // Tags
    Route::resource('tags', AdminTagController::class);

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [AdminOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [AdminOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm', [AdminOrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/reject', [AdminOrderController::class, 'reject'])->name('orders.reject');
    Route::post('/orders/{order}/assign-delivery', [AdminOrderController::class, 'assignDelivery'])->name('orders.assign-delivery');
    Route::put('/orders/{order}/update-delivery-method', [AdminOrderController::class, 'updateDeliveryMethod'])->name('orders.update-delivery-method');
    Route::post('/orders/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    // Autocomplete routes for order creation
    Route::get('/orders/autocomplete/clients', [AdminOrderController::class, 'searchClients'])->name('orders.autocomplete.clients');
    Route::get('/orders/autocomplete/products', [AdminOrderController::class, 'searchProducts'])->name('orders.autocomplete.products');

    // Delivery
    Route::resource('delivery', DeliveryController::class);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');


    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/low-stock', [InventoryController::class, 'indexLowStockProducts'])->name('inventory.index.low-stock');
    Route::get('/inventory/{product}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{product}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/inventory/{product}/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory/{product}', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{product}/batches', [InventoryController::class, 'product'])->name('inventory.product');
    
    // Damaged Goods
    Route::get('/damaged-goods', [DamagedGoodsController::class, 'index'])->name('damaged-goods.index');
    Route::get('/damaged-goods/create', [DamagedGoodsController::class, 'create'])->name('damaged-goods.create');
    Route::post('/damaged-goods', [DamagedGoodsController::class, 'store'])->name('damaged-goods.store');
    Route::get('/damaged-goods/product-inventories/{product}', [DamagedGoodsController::class, 'getProductInventories'])->name('damaged-goods.product-inventories');
    Route::get('/damaged-goods/{damagedGoods}', [DamagedGoodsController::class, 'show'])->name('damaged-goods.show');
    Route::delete('/damaged-goods/{damagedGoods}', [DamagedGoodsController::class, 'destroy'])->name('damaged-goods.destroy');

    // Adjustments (Gains & Losses)
    Route::resource('adjustments', AdjustmentController::class);

    // Statistics
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/statistics/sales', [StatisticsController::class, 'sales'])->name('statistics.sales');
    Route::get('/statistics/earnings', [StatisticsController::class, 'earnings'])->name('statistics.earnings');

});
