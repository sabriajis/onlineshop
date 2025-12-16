<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\TrackingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Products & Cart
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

    // AJAX RajaOngkir
    Route::get('/checkout/cities', [CheckoutController::class, 'getCities'])->name('checkout.getCities');
    Route::get('/checkout/shipping', [CheckoutController::class, 'getShipping'])->name('checkout.shipping');
    Route::get('/checkout/get-districts', [CheckoutController::class, 'getDistricts'])->name('checkout.getDistricts');
    Route::get('/checkout/search-destination', [CheckoutController::class, 'searchDestination'])->name('checkout.searchDestination');

    // Tracking
    Route::get('/tracking/{order}', [TrackingController::class, 'show'])->name('tracking.show');
});

require __DIR__.'/auth.php';
