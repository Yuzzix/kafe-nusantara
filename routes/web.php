<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\GuestMenuController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Public / Shared Workspace Routes
Route::get('/buku-menu', [GuestMenuController::class, 'index'])->name('menu-book');
Route::post('/staff/switch', [DashboardController::class, 'switchStaff'])->name('staff.switch');
Route::get('/search', [DashboardController::class, 'search'])->name('workspace.search');

// Ringkasan Gerai (Admin & Kasir)
Route::middleware('shift.role:admin,cashier')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::post('/orders/{order:order_number}/pay', [OrderController::class, 'pay'])->name('orders.pay');
});

// Layar Dapur / Kitchen Display System (Admin & Dapur)
Route::middleware('shift.role:admin,kitchen')->group(function () {
    Route::get('/dapur', [KitchenController::class, 'index'])->name('kitchen.index');
});

// Master Katalog CRUD: Kategori & Pengaturan Harga Menu (Admin Only)
Route::middleware('shift.role:admin')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/categories/{category:slug}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category:slug}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category:slug}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/menus/create', [MenuController::class, 'create'])->name('menus.create');
    Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
    Route::get('/menus/{menu:slug}/edit', [MenuController::class, 'edit'])->name('menus.edit');
    Route::put('/menus/{menu:slug}', [MenuController::class, 'update'])->name('menus.update');
    Route::delete('/menus/{menu:slug}', [MenuController::class, 'destroy'])->name('menus.destroy');
});

// Daftar Menu & Toggle Stok Harian Tersedia/Habis (Admin, Kasir, Dapur)
Route::middleware('shift.role:admin,cashier,kitchen')->group(function () {
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    Route::patch('/menus/{menu:slug}/toggle', [MenuController::class, 'toggleAvailability'])->name('menus.toggle');
});

// Operasional Pesanan & Status Meja (Admin, Kasir, Pelayan)
Route::middleware('shift.role:admin,cashier,waiter')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/tables', [DiningTableController::class, 'index'])->name('tables.index');
    Route::patch('/tables/{diningTable}/status', [DiningTableController::class, 'updateStatus'])->name('tables.update-status');
});

// Update Progres Dapur / Sajian (Admin, Kasir, Pelayan, Dapur)
Route::patch('/orders/{order:order_number}/status', [OrderController::class, 'updateStatus'])
    ->middleware('shift.role:admin,cashier,waiter,kitchen')
    ->name('orders.update-status');
