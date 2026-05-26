<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductAvailabilityController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use Illuminate\Support\Facades\Route;

// Public Landing Page (Homepage)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Public Equipment Catalog
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');

// Public Equipment Detail Page
Route::get('/product/{equipment:slug}', [ProductController::class, 'show'])->name('product.show');

// Public Equipment Availability polling
Route::get('/product/{equipment:slug}/availability', [ProductAvailabilityController::class, 'show'])->name('product.availability');

// Public Midtrans Webhook Callback URLs (CSRF excluded)
Route::post('/midtrans/callback', [MidtransWebhookController::class, 'handle'])->name('midtrans.callback');
Route::post('/api/midtrans/callback', [MidtransWebhookController::class, 'handle'])->name('api.midtrans.callback');

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

    // Checkout Preview & Submission
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Customer Bookings Dashboard & Invoices
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}/invoice/download', [OrderController::class, 'downloadInvoice'])->name('orders.invoice.download');
    Route::post('/payments/{order}/refresh', [OrderController::class, 'refreshPayment'])->name('payments.refresh');
});

// Admin Operations Panel Route Group
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('equipments', EquipmentController::class);
    
    // Order monitoring
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/fees', [AdminOrderController::class, 'addFee'])->name('orders.fees');
    
    // Payments query
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
});

require __DIR__.'/auth.php';
