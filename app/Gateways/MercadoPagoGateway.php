<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\DataTransferObjects\PaymentResult;
use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Mercado Pago gateway — supports Checkout Pro and Pix.
 *
 * Credentials needed (configured via /admin/settings/payment):
 *   - access_token  → server-side API calls
 *   - public_key    → frontend SDK (returned masked via settings endpoint)
 *   - webhook_secret → used to validate x-signature header on webhooks
 *
 * Sandbox: use test credentials from https://www.mercadopago.com.br/developers
 * Docs: https://www.mercadopago.com.br/developers/pt/reference
 */
class MercadoPagoGateway extends AbstractGateway implements PaymentGatewayInterface
{
    protected function gatewayName(): string
    {
        return 'mercadopago';
    }

    protected function baseUrl(): string
    {
        return 'https://api.mercadopago.com';
    }

    protected function http(): PendingRequest
    {
        return parent::http()->withToken($this->credential('access_token'));
    }

    // ─── PaymentGatewayInterface ──────────────────────────────────────────────

    public function createPayment(Order $order, string $method): PaymentResult
    {
        return match ($method) {
            'pix'           => $this->createPix($order),
            'checkout_pro'  => $this->createCheckoutPro($order),
            default         => throw new \InvalidArgumentException("Unsupported method '{$method}' for Mercado Pago."),
        };
    }

    public function getPaymentStatus(string $gatewayPaymentId): string
    {
        // TODO: GET /v1/payments/{id} and map MP status to our enum
        // MP statuses: pending, approved, authorized, in_process, in_mediation,
        //              rejected, cancelled, refunded, charged_back
        // Mapping:
        //   approved              → 'approved'
        //   rejected / cancelled  → 'rejected'
        //   refunded              → 'refunded'
        //   everything else       → 'pending'
        throw new \RuntimeException('TODO: implement MercadoPagoGateway::getPaymentStatus');
    }

    public function handleWebhook(Request $request): void
    {
        // TODO: validate x-signature header
        // MP sends: x-signature = "ts=<timestamp>,v1=<hmac>"
        // HMAC-SHA256 of "<ts>.<data.id>" signed with webhook_secret credential
        // Reject with 401 if signature is invalid — never process unsigned webhooks.
        //
        // After validation:
        //   1. Check $request->input('type') — we care about 'payment'
        //   2. Fetch payment from MP API: GET /v1/payments/{data.id}
        //   3. Map status and update payments + orders tables
        //   4. Dispatch NotifyOrderStatusChanged if order status changed
        throw new \RuntimeException('TODO: implement MercadoPagoGateway::handleWebhook');
    }

    // ─── Private methods ──────────────────────────────────────────────────────

    private function createPix(Order $order): PaymentResult
    {
        // TODO: POST /v1/payments with payment_method_id = 'pix'
        // Payload:
        //   transaction_amount → $order->total (float — MP does not accept strings)
        //   description        → "Pedido #{$order->id}"
        //   payment_method_id  → 'pix'
        //   payer.email        → $order->user->email
        //   date_of_expiration → now()->addHours(24)->toIso8601String()
        //
        // Response fields to map:
        //   id                                          → gatewayPaymentId
        //   point_of_interaction.transaction_data.qr_code        → pixCode
        //   point_of_interaction.transaction_data.qr_code_base64 → pixQrCode
        //   date_of_expiration                                    → expiresAt
        throw new \RuntimeException('TODO: implement MercadoPagoGateway::createPix');
    }

    private function createCheckoutPro(Order $order): PaymentResult
    {
        // TODO: POST /checkout/preferences
        // Payload:
        //   items[]            → map $order->items (title, quantity, unit_price as float)
        //   payer.email        → $order->user->email
        //   external_reference → $order->id
        //   back_urls          → success: /checkout/payment?status=approved, failure/pending URLs
        //   auto_approve       → false (sandbox: use test cards)
        //   notification_url   → url('/api/webhooks/payment')
        //
        // Response fields to map:
        //   id         → gatewayPaymentId
        //   init_point → paymentUrl (redirect the customer here)
        throw new \RuntimeException('TODO: implement MercadoPagoGateway::createCheckoutPro');
    }
}
