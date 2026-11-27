<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

// ── Redirect Root ke Katalog ───────────────────────────────────
Route::get('/', fn() => redirect()->route('catalog.index'));

// ── Authentication Routes ──────────────────────────────────────
Route::get('/login',             [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',            [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::get('/login/demo/{role}', [AuthController::class, 'demoLogin'])->name('login.demo');
Route::get('/register',          [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',         [AuthController::class, 'register'])->name('register.post');
Route::post('/logout',           [AuthController::class, 'logout'])->name('logout');

// ── Buyer Routes (Wireframe A & B) ─────────────────────────────
Route::prefix('catalog')->name('catalog.')->group(function () {
    Route::get('/',             [CatalogController::class, 'index'])->name('index');
    Route::get('/product/{id}', [CatalogController::class, 'show'])->name('show');
    Route::post('/order',       [CatalogController::class, 'storeOrder'])->name('order.store')->middleware('throttle:10,1');

    // Riwayat pesanan pembeli wajib login
    Route::get('/orders',       [CatalogController::class, 'orders'])->name('orders')->middleware('auth');
});

// ── Buyer Profile Route (/profile — Mengubah Nama, Email, Nomor Telepon) ──
Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['put', 'patch', 'post'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// ── Owner Routes (Wireframe C — Terproteksi Khusus Role Owner) ──
Route::prefix('owner')->name('owner.')->middleware(['auth', 'store.role:owner'])->group(function () {
    Route::get('/dashboard',                  [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/products',                   [OwnerController::class, 'products'])->name('products');
    Route::post('/products',                  [OwnerController::class, 'storeProduct'])->name('products.store');
    Route::match(['post', 'patch'], '/products/{id}', [OwnerController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{id}',           [OwnerController::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/orders',                     [OwnerController::class, 'orders'])->name('orders');
    Route::patch('/orders/{orderCode}/status', [OwnerController::class, 'updateOrderStatus'])->name('orders.update_status');
    Route::get('/finance',                    [OwnerController::class, 'finance'])->name('finance');
    Route::get('/employees',                  [OwnerController::class, 'employees'])->name('employees');
    Route::post('/employees',                 [OwnerController::class, 'storeEmployee'])->name('employees.store');
    Route::patch('/employees/{id}',           [OwnerController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{id}',          [OwnerController::class, 'destroyEmployee'])->name('employees.destroy');
});

// ── Employee Routes (Wireframe D — Terproteksi Khusus Role Pegawai) ─
Route::prefix('employee')->name('employee.')->middleware(['auth', 'store.role:employee'])->group(function () {
    Route::get('/orders',                     [EmployeeController::class, 'orders'])->name('orders');
    Route::patch('/orders/{orderCode}/status', [EmployeeController::class, 'updateOrderStatus'])->name('orders.update_status');
    Route::get('/summary',                    [EmployeeController::class, 'summary'])->name('summary');
    Route::get('/products',                   [EmployeeController::class, 'products'])->name('products');
});
