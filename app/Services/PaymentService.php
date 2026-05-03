<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\DataTransferObjects\PaymentResult;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSetting;
use RuntimeException;

class PaymentService
{
    /**
     * Initiates a payment for the given order via the active gateway.
     *
     * Resolves the gateway at runtime from payment_settings — no if/else per gateway.
     */
    public function initiate(Order $order, string $method): Payment
    {
        $gateway = $this->resolveGateway();
        $result  = $gateway->createPayment($order, $method);

        return Payment::create([
            'order_id'           => $order->id,
            'gateway'            => $this->activeGatewayName(),
            'gateway_payment_id' => $result->gatewayPaymentId,
            'method'             => $result->method,
            'status'             => $result->status,
            'amount'             => $order->total,
            'pix_code'           => $result->pixCode,
            'pix_qr_code'        => $result->pixQrCode,
            'payment_url'        => $result->paymentUrl,
            'boleto_url'         => $result->boletoUrl,
            'expires_at'         => $result->expiresAt,
        ]);
    }

    public function getStatus(Payment $payment): string
    {
        return $this->resolveGateway()->getPaymentStatus($payment->gateway_payment_id);
    }

    /**
     * Resolves the active PaymentGatewayInterface implementation from the database setting.
     *
     * Adding a third gateway = implement the interface + register in AppServiceProvider.
     */
    public function resolveGateway(): PaymentGatewayInterface
    {
        $setting = PaymentSetting::where('active', true)->first();

        if (! $setting) {
            throw new RuntimeException('No active payment gateway configured.');
        }

        return app("gateway.{$setting->gateway}");
    }

    /**
     * Dispatches the incoming webhook to the active gateway for validation and processing.
     */
    public function handleWebhook(\Illuminate\Http\Request $request): void
    {
        $this->resolveGateway()->handleWebhook($request);
    }

    private function activeGatewayName(): string
    {
        return PaymentSetting::where('active', true)->value('gateway')
            ?? throw new RuntimeException('No active payment gateway configured.');
    }
}
