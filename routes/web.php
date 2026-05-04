<?php

use Illuminate\Support\Facades\Route;

// Root redirects to admin panel
Route::get('/', fn () => redirect('/admin'));

require __DIR__ . '/web/auth.php';
require __DIR__ . '/web/shop.php';
require __DIR__ . '/web/orders.php';
require __DIR__ . '/web/checkout.php';
require __DIR__ . '/web/admin.php';
