<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\GuestMenuController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Dashboard Ringkasan & Workspace Utilities
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/search', [DashboardController::class, 'search'])->name('workspace.search');
Route::post('/staff/switch', [DashboardController::class, 'switchStaff'])->name('staff.switch');

// Buku Menu Digital Tamu (Customer-facing Read-Only Menu Book & QR Table Preview)
Route::get('/buku-menu', [GuestMenuController::class, 'index'])->name('menu-book');

// Layar Dapur / Kitchen Display System (KDS)
Route::get('/dapur', [KitchenController::class, 'index'])->name('kitchen.index');

// Kategori Menu (Master-Detail: Category -> Menus)
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/categories/{category:slug}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
Route::put('/categories/{category:slug}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{category:slug}', [CategoryController::class, 'destroy'])->name('categories.destroy');

// Daftar Menu & Form Tambah/Ubah Menu
Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
Route::get('/menus/create', [MenuController::class, 'create'])->name('menus.create');
Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
Route::get('/menus/{menu:slug}/edit', [MenuController::class, 'edit'])->name('menus.edit');
Route::put('/menus/{menu:slug}', [MenuController::class, 'update'])->name('menus.update');
Route::patch('/menus/{menu:slug}/toggle', [MenuController::class, 'toggleAvailability'])->name('menus.toggle');
Route::delete('/menus/{menu:slug}', [MenuController::class, 'destroy'])->name('menus.destroy');

// Pesanan & Kasir (Master-Detail: Order -> OrderItems/Menus + Payments + DiningTable)
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');
Route::patch('/orders/{order:order_number}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
Route::post('/orders/{order:order_number}/pay', [OrderController::class, 'pay'])->name('orders.pay');

// Status Meja
Route::get('/tables', [DiningTableController::class, 'index'])->name('tables.index');
Route::patch('/tables/{diningTable}/status', [DiningTableController::class, 'updateStatus'])->name('tables.update-status');
