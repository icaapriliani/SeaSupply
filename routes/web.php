<?php

use Illuminate\Support\Facades\Route;

// ── Buyer Routes (Frontend Statis — Mock Data) ──────────────────
Route::get('/', fn() => redirect()->route('catalog.index'));

Route::prefix('catalog')->name('catalog.')->group(function () {
    Route::get('/',          'App\Http\Controllers\CatalogController@index')->name('index');
    Route::get('/product/{id}', 'App\Http\Controllers\CatalogController@show')->name('show');
});
