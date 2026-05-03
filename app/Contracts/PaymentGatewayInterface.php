<?php

namespace App\Contracts;

use App\DataTransferObjects\PaymentResult;
use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function createPayment(Order $order, string $method): PaymentResult;

    public function getPaymentStatus(string $gatewayPaymentId): string;

    public function handleWebhook(Request $request): void;
}
