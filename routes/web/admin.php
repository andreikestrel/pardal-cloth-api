<?php

use App\Http\Controllers\Admin\CatalogAdminController;
use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\Admin\CouponAdminController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\PromotionAdminController;
use App\Http\Controllers\Admin\ReportAdminController;
use App\Http\Controllers\Admin\SettingsAdminController;
use App\Http\Controllers\Admin\StockAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardAdminController::class, 'index'])->name('dashboard');

    // Products
    Route::get('/products', [ProductAdminController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductAdminController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductAdminController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductAdminController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductAdminController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductAdminController::class, 'destroy'])->name('products.destroy');

    // Categories
    Route::get('/categories', [CategoryAdminController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryAdminController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryAdminController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}', [CategoryAdminController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryAdminController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryAdminController::class, 'destroy'])->name('categories.destroy');

    // Promotions
    Route::get('/promotions', [PromotionAdminController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/create', [PromotionAdminController::class, 'create'])->name('promotions.create');
    Route::post('/promotions', [PromotionAdminController::class, 'store'])->name('promotions.store');
    Route::get('/promotions/{promotion}', [PromotionAdminController::class, 'edit'])->name('promotions.edit');
    Route::put('/promotions/{promotion}', [PromotionAdminController::class, 'update'])->name('promotions.update');
    Route::delete('/promotions/{promotion}', [PromotionAdminController::class, 'destroy'])->name('promotions.destroy');

    // Coupons
    Route::get('/coupons', [CouponAdminController::class, 'index'])->name('coupons.index');
    Route::get('/coupons/create', [CouponAdminController::class, 'create'])->name('coupons.create');
    Route::post('/coupons', [CouponAdminController::class, 'store'])->name('coupons.store');
    Route::get('/coupons/{coupon}', [CouponAdminController::class, 'edit'])->name('coupons.edit');
    Route::put('/coupons/{coupon}', [CouponAdminController::class, 'update'])->name('coupons.update');
    Route::delete('/coupons/{coupon}', [CouponAdminController::class, 'destroy'])->name('coupons.destroy');

    // Orders
    Route::get('/orders', [OrderAdminController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderAdminController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('orders.update-status');

    // Stock
    Route::get('/stock', [StockAdminController::class, 'index'])->name('stock.index');
    Route::post('/stock/adjust', [StockAdminController::class, 'adjust'])->name('stock.adjust');

    // Settings
    Route::get('/settings/general', [SettingsAdminController::class, 'general'])->name('settings.general');
    Route::put('/settings/general', [SettingsAdminController::class, 'updateGeneral'])->name('settings.update-general');
    Route::get('/settings/payment', [SettingsAdminController::class, 'payment'])->name('settings.payment');
    Route::put('/settings/payment', [SettingsAdminController::class, 'updatePayment'])->name('settings.update-payment');

    // Reports
    Route::get('/reports', [ReportAdminController::class, 'index'])->name('reports.index');

    // Catalog (hero carousel)
    Route::get('/catalog/slides', [CatalogAdminController::class, 'slides'])->name('catalog.slides');
    Route::post('/catalog/slides', [CatalogAdminController::class, 'storeSlide'])->name('catalog.slides.store');
    Route::post('/catalog/slides/{slide}', [CatalogAdminController::class, 'updateSlide'])->name('catalog.slides.update');
    Route::delete('/catalog/slides/{slide}', [CatalogAdminController::class, 'destroySlide'])->name('catalog.slides.destroy');
    Route::post('/catalog/slides/reorder', [CatalogAdminController::class, 'reorderSlides'])->name('catalog.slides.reorder');

    // Users
    Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
    Route::post('/users', [UserAdminController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/resend', [UserAdminController::class, 'resend'])->name('users.resend');
    Route::delete('/users/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');
});
