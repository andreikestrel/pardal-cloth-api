<?php

use App\Http\Controllers\Order\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/store/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/store/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/store/orders/{order}/payment-status', [OrderController::class, 'paymentStatus'])->name('orders.payment-status');
    Route::get('/store/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
});
