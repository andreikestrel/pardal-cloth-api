<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Checkout/Index');
    }

    public function payment(): Response
    {
        return Inertia::render('Checkout/Payment');
    }
}
