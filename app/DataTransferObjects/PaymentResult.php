<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

final class PaymentResult
{
    public function __construct(
        public readonly string $gatewayPaymentId,
        public readonly string $method,
        public readonly string $status,
        public readonly ?string $pixCode = null,
        public readonly ?string $pixQrCode = null,
        public readonly ?string $paymentUrl = null,
        public readonly ?string $boletoUrl = null,
        public readonly ?Carbon $expiresAt = null,
    ) {}
}
