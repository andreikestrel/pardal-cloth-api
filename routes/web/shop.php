<?php

use App\Http\Controllers\Shop\AboutController;
use App\Http\Controllers\Shop\BlogController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CategoryController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/store', [HomeController::class, 'index'])->name('home');

Route::get('/store/catalog', [ProductController::class, 'index'])->name('shop.index');
Route::get('/store/products/{product:slug}', [ProductController::class, 'show'])->name('shop.show');

Route::get('/store/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/store/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/store/cart', [CartController::class, 'page'])->name('cart.index');
Route::post('/store/cart/calculate', [CartController::class, 'calculate'])->name('cart.calculate');
Route::post('/store/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.apply-coupon');

Route::get('/store/about', [AboutController::class, 'index'])->name('about');

Route::get('/blog',           [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}',    [BlogController::class, 'show'])->name('blog.show');

Route::get('/tag/{slug}',     [TagController::class, 'show'])->name('tag.show');
