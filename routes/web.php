<?php

use App\Enums\UserRole;
use App\Http\Controllers\ProductController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = Auth::user();

    if ($user instanceof User && $user->hasRole(UserRole::Klant)) {
        return redirect()->route('klant.home');
    }

    if ($user !== null) {
        return redirect()->route('products.index');
    }

    return view('home');
})->name('home');

Route::view('/klant', 'klant.home')
    ->middleware('auth')
    ->name('klant.home');

Route::middleware(['auth', 'can:access-medewerker-dashboard'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::get('/products/{product}/allergenen', [ProductController::class, 'allergenen'])
        ->name('products.allergenen');

    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->name('products.show');
});

require __DIR__.'/auth.php';
