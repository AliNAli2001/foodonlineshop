<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ClientController;

Route::prefix('v1')->group(function () {
    // Client Authentication Routes (Public)
    Route::post('/auth/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.login');

    // Logout (Authenticated)
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.logout')->middleware('auth:sanctum');

    // Email and Phone Verification (Authenticated)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail'])->name('api.verify-email');
        Route::post('/auth/verify-phone', [AuthController::class, 'verifyPhone'])->name('api.verify-phone');
    });

    // Public Product, Category, and Company Routes
    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');

    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('api.categories.show');

    Route::get('/companies', [CompanyController::class, 'index'])->name('api.companies.index');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('api.companies.show');

    // Client Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Orders
        Route::post('/orders', [OrderController::class, 'store'])->name('api.orders.store');
        Route::get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('api.orders.show');

        // Client Profile
        Route::get('/profile', [ClientController::class, 'profile'])->name('api.client.profile');
        Route::put('/profile', [ClientController::class, 'updateProfile'])->name('api.client.update-profile');
    });
});
