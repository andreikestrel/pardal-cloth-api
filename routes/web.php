<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/web/auth.php';
require __DIR__ . '/web/shop.php';
require __DIR__ . '/web/orders.php';
require __DIR__ . '/web/checkout.php';
require __DIR__ . '/web/admin.php';
