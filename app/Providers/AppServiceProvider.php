<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Gateways\AsaasGateway;
use App\Gateways\MercadoPagoGateway;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Gateways are resolved by name so PaymentService can use app("gateway.X")
        // without any if/else — adding a third gateway only requires registering it here.
        $this->app->bind('gateway.mercadopago', MercadoPagoGateway::class);
        $this->app->bind('gateway.asaas', AsaasGateway::class);
    }

    public function boot(): void
    {
        // UUID PKs across the app — enforce UUID as the default morph key type
        // so polymorphic relations (e.g. Spatie Media Library) use UUID columns.
        Schema::defaultMorphKeyType('uuid');
    }
}
