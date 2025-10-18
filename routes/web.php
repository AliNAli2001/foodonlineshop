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
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\MessageTemplateController;

// Home page
Route::get('/', function () {
    return view('home');
})->name('home');

// Client Authentication Routes
Route::middleware('guest:client')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:client');

// Email and Phone Verification
Route::middleware('auth:client')->group(function () {
    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verify-email');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email.store');
    Route::get('/verify-phone', [AuthController::class, 'showVerifyPhone'])->name('verify-phone');
    Route::post('/verify-phone', [AuthController::class, 'verifyPhone'])->name('verify-phone.store');
});

// Client Routes (Public - no auth required for browsing)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// Cart Routes
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Client Authenticated Routes
Route::middleware('auth:client')->group(function () {
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('order.checkout');
    Route::post('/order', [OrderController::class, 'store'])->name('order.store');

    Route::get('/profile', [ClientController::class, 'profile'])->name('client.profile');
    Route::post('/profile', [ClientController::class, 'updateProfile'])->name('client.update-profile');
    Route::get('/orders', [ClientController::class, 'orders'])->name('client.orders');
    Route::get('/orders/{order}', [ClientController::class, 'orderDetails'])->name('client.order-details');
});

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

    // Categories
    Route::resource('categories', AdminCategoryController::class);

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm', [AdminOrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/reject', [AdminOrderController::class, 'reject'])->name('orders.reject');
    Route::post('/orders/{order}/assign-delivery', [AdminOrderController::class, 'assignDelivery'])->name('orders.assign-delivery');
    Route::post('/orders/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    // Delivery
    Route::resource('delivery', DeliveryController::class);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Message Templates
    Route::resource('messages', MessageTemplateController::class);
});
