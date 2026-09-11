<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'products.index' : 'login');
});

Route::middleware(['auth', 'can:access-medewerker-dashboard'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::get('/products/{product}/allergenen', [ProductController::class, 'allergenen'])
        ->name('products.allergenen');

    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->name('products.show');
});

require __DIR__.'/auth.php';
