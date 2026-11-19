<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OwnerController;

// ── Buyer Routes (Wireframe A & B) ─────────────────────────────
Route::get('/', fn() => redirect()->route('catalog.index'));

Route::prefix('catalog')->name('catalog.')->group(function () {
    Route::get('/',             [CatalogController::class, 'index'])->name('index');
    Route::get('/product/{id}', [CatalogController::class, 'show'])->name('show');
});

// ── Owner Routes (Wireframe C — Dashboard Pemilik Usaha) ───────
Route::prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/products',  [OwnerController::class, 'products'])->name('products');
    Route::get('/orders',    [OwnerController::class, 'orders'])->name('orders');
    Route::get('/finance',   [OwnerController::class, 'finance'])->name('finance');
    Route::get('/employees', [OwnerController::class, 'employees'])->name('employees');
});
