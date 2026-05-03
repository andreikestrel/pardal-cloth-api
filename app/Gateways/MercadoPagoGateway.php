<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\DataTransferObjects\PaymentResult;
use App\Models\Order;
use Illuminate\Http\Request;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    // TODO: implement in Phase 5
    public function createPayment(Order $order, string $method): PaymentResult
    {
        throw new \RuntimeException('MercadoPagoGateway not yet implemented.');
    }

    public function getPaymentStatus(string $gatewayPaymentId): string
    {
        throw new \RuntimeException('MercadoPagoGateway not yet implemented.');
    }

    public function handleWebhook(Request $request): void
    {
        throw new \RuntimeException('MercadoPagoGateway not yet implemented.');
    }
}
