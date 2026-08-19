<?php

use Illuminate\Support\Facades\Route;

// Auth
Route::view('/login', 'auth.login')->name('login');
Route::view('/setup-pin', 'auth.setup-pin')->name('setup-pin');
Route::post('/logout', function () { session()->flush(); return redirect('/login'); })->name('logout');

// Cached logo assets (public — used by login + sidebar)
Route::get('/logo', [App\Http\Controllers\LogoController::class, 'appLogo'])->middleware('throttle:assets');
Route::get('/receipt-logo', [App\Http\Controllers\LogoController::class, 'receiptLogo'])->middleware('throttle:assets');

// POS
Route::view('/pos', 'pos.index')->name('pos.index');
Route::view('/pos/orders', 'pos.orders')->name('orders.index');

// Admin
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/products', 'products.index')->name('web.products.index');
Route::view('/customers', 'customers.index')->name('web.customers.index');
Route::view('/reports', 'reports.index')->name('web.reports.index');
Route::view('/users', 'users.index')->name('web.users.index');
Route::view('/taxes', 'taxes.index')->name('web.taxes.index');
Route::view('/promotions', 'promotions.index')->name('web.promotions.index');
Route::view('/loyalty', 'loyalty.index')->name('web.loyalty.index');
Route::view('/fiscal', 'fiscal.index')->name('web.fiscal.index');
Route::view('/printers', 'printers.index')->name('web.printers.index');
Route::view('/settings', 'settings.index')->name('web.settings.index');
Route::view('/branches', 'branches.index')->name('web.branches.index');
Route::view('/roles', 'roles.index')->name('web.roles.index');
Route::view('/warehouses', 'warehouses.index')->name('web.warehouses.index');
Route::view('/purchases', 'purchases.index')->name('web.purchases.index');
Route::view('/income-expenses', 'income-expenses.index')->name('web.income-expenses.index');
Route::view('/barcodes', 'barcodes.index')->name('web.barcodes.index');
Route::view('/activity', 'activity.index')->name('web.activity.index');

// Redirect root to login
Route::redirect('/', '/login');