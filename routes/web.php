<?php

use App\Enums\UserRole;
use App\Http\Controllers\LeverancierController;
use App\Http\Controllers\LeveringController;
use App\Http\Controllers\ProductController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = Auth::user();

    if ($user instanceof User && $user->hasRole(UserRole::Klant)) {
        return redirect()->route('klant.home');
    }

    return view('home');
})->name('home');

Route::view('/klant', 'klant.home')
    ->middleware('auth')
    ->name('klant.home');

Route::middleware(['auth', 'can:access-medewerker-dashboard'])->group(function () {
    Route::get('/leveranciers', [LeverancierController::class, 'index'])
        ->name('leveranciers.index');

    Route::get('/leveranciers/{leverancier}/producten', [LeverancierController::class, 'producten'])
        ->name('leveranciers.producten')
        ->whereNumber('leverancier');

    Route::get('/leveranciers/{leverancier}/producten/{product}/levering', [LeveringController::class, 'create'])
        ->name('leveranciers.levering.create')
        ->whereNumber(['leverancier', 'product']);

    Route::post('/leveranciers/{leverancier}/producten/{product}/levering', [LeveringController::class, 'store'])
        ->name('leveranciers.levering.store')
        ->whereNumber(['leverancier', 'product']);

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::get('/products/{product}/allergenen', [ProductController::class, 'allergenen'])
        ->name('products.allergenen');

    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->name('products.show');
});

require __DIR__.'/auth.php';
