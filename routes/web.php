<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductAvailabilityController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

// Public Landing Page (Homepage)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Public Equipment Catalog
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');

// Public Equipment Detail Page
Route::get('/product/{equipment:slug}', [ProductController::class, 'show'])->name('product.show');

// Public Equipment Availability polling
Route::get('/product/{equipment:slug}/availability', [ProductAvailabilityController::class, 'show'])->name('product.availability');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cart Operations
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.add');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    // Checkout Preview Staging
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
});

require __DIR__.'/auth.php';
