<?php

use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Order\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/store/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/store/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');

    Route::post('/store/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/store/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
});
