<?php

use App\Http\Controllers\Webhook\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

// Webhook endpoint — no session auth, gateway signature validated in controller
Route::post('/webhooks/payment', [PaymentWebhookController::class, 'receive'])
    ->name('webhooks.payment');
