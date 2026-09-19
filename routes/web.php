<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes (Unified Login)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    
    // Receipt Printing (Accessible by Cashier & Admin)
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');

    // POS Terminal Routes (Accessible by Cashiers and Admins)
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/lookup', [PosController::class, 'lookup'])->name('pos.lookup');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/hold', [PosController::class, 'holdOrder'])->name('pos.hold');
    Route::get('/pos/held-orders', [PosController::class, 'getHeldOrders'])->name('pos.held-orders');
    Route::post('/pos/recall/{order}', [PosController::class, 'recallHeldOrder'])->name('pos.recall');
    Route::delete('/pos/held-orders/{order}', [PosController::class, 'deleteHeldOrder'])->name('pos.delete-held');
    Route::get('/pos/invoices', [PosController::class, 'invoiceHistory'])->name('pos.invoices');
    Route::put('/pos/invoices/{order}', [PosController::class, 'updateInvoice'])->name('pos.update-invoice');

    // Admin Panel Routes (Protected by role:admin)
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Inventory / Product Management
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        // Category Management
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Sales Reports & History
        Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
        Route::get('/sales/{order}', [SalesController::class, 'show'])->name('sales.show');
        Route::put('/sales/{order}', [SalesController::class, 'update'])->name('sales.update');
        Route::delete('/sales/{order}', [SalesController::class, 'destroy'])->name('sales.destroy');

        // Shop Settings Management
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

        // User / Staff Account Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
