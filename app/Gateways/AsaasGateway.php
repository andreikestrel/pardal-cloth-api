<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\DataTransferObjects\PaymentResult;
use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;

/**
 * Asaas gateway — supports Pix, Boleto, and Credit Card.
 *
 * Credentials needed (configured via /admin/settings/payment):
 *   - api_key      → all API calls (header: access_token)
 *   - environment  → 'sandbox' or 'production'
 *
 * Sandbox: https://sandbox.asaas.com (free, no real money)
 * Docs: https://docs.asaas.com/reference
 */
class AsaasGateway extends AbstractGateway implements PaymentGatewayInterface
{
    protected function gatewayName(): string
    {
        return 'asaas';
    }

    protected function baseUrl(): string
    {
        $env = $this->credentials()['environment'] ?? 'sandbox';

        return $env === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';
    }

    protected function http(): PendingRequest
    {
        return parent::http()->withHeader('access_token', $this->credential('api_key'));
    }

    // ─── PaymentGatewayInterface ──────────────────────────────────────────────

    public function createPayment(Order $order, string $method): PaymentResult
    {
        return match ($method) {
            'pix'         => $this->createPix($order),
            'boleto'      => $this->createBoleto($order),
            'credit_card' => $this->createCreditCard($order),
            default       => throw new \InvalidArgumentException("Unsupported method '{$method}' for Asaas."),
        };
    }

    public function getPaymentStatus(string $gatewayPaymentId): string
    {
        // TODO: GET /payments/{id} and map Asaas status to our enum
        // Asaas statuses: PENDING, RECEIVED, CONFIRMED, OVERDUE, REFUNDED,
        //                 RECEIVED_IN_CASH, REFUND_REQUESTED, CHARGEBACK_REQUESTED, etc.
        // Mapping:
        //   RECEIVED / CONFIRMED / RECEIVED_IN_CASH → 'approved'
        //   REFUNDED / REFUND_REQUESTED             → 'refunded'
        //   OVERDUE / CHARGEBACK_*                  → 'rejected'
        //   everything else                         → 'pending'
        throw new \RuntimeException('TODO: implement AsaasGateway::getPaymentStatus');
    }

    public function handleWebhook(Request $request): void
    {
        // TODO: validate asaas-access-token header
        // The header value must match the token configured in the Asaas dashboard
        // and stored in payment_settings.credentials['api_key'].
        // Reject with 401 if token is invalid.
        //
        // After validation:
        //   1. Check $request->input('event') — we care about 'PAYMENT_RECEIVED' / 'PAYMENT_CONFIRMED'
        //   2. Find Payment by gateway_payment_id = $request->input('payment.id')
        //   3. Map status and update payments + orders tables
        //   4. Dispatch NotifyOrderStatusChanged if order status changed
        throw new \RuntimeException('TODO: implement AsaasGateway::handleWebhook');
    }

    // ─── Private methods ──────────────────────────────────────────────────────

    private function resolveOrCreateCustomer(Order $order): string
    {
        // TODO: find existing Asaas customer by email, or create one
        // GET /customers?email={email} → use id if found
        // POST /customers → { name, email, cpfCnpj (optional) }
        // Returns Asaas customer id (e.g. "cus_000000000001")
        throw new \RuntimeException('TODO: implement AsaasGateway::resolveOrCreateCustomer');
    }

    private function createPix(Order $order): PaymentResult
    {
        // TODO:
        //   1. $customerId = $this->resolveOrCreateCustomer($order)
        //   2. POST /payments with:
        //        customer    → $customerId
        //        billingType → 'PIX'
        //        value       → (float) $order->total
        //        dueDate     → now()->addDays(1)->format('Y-m-d')
        //        description → "Pedido #{$order->id}"
        //   3. GET /payments/{id}/pixQrCode → { encodedImage (base64), payload (copia e cola) }
        //
        // Response fields to map:
        //   id                     → gatewayPaymentId
        //   pixQrCode.payload      → pixCode
        //   pixQrCode.encodedImage → pixQrCode
        //   dueDate                → expiresAt (parse as end of day)
        throw new \RuntimeException('TODO: implement AsaasGateway::createPix');
    }

    private function createBoleto(Order $order): PaymentResult
    {
        // TODO:
        //   1. $customerId = $this->resolveOrCreateCustomer($order)
        //   2. POST /payments with:
        //        customer    → $customerId
        //        billingType → 'BOLETO'
        //        value       → (float) $order->total
        //        dueDate     → now()->addDays(3)->format('Y-m-d')
        //        description → "Pedido #{$order->id}"
        //
        // Response fields to map:
        //   id         → gatewayPaymentId
        //   bankSlipUrl → boletoUrl
        //   dueDate    → expiresAt
        throw new \RuntimeException('TODO: implement AsaasGateway::createBoleto');
    }

    private function createCreditCard(Order $order): PaymentResult
    {
        // TODO: credit card requires tokenization on the frontend first.
        // Flow:
        //   1. Frontend uses Asaas.js to tokenize card → receives creditCardToken
        //   2. Frontend sends token to POST /orders/{id}/pay along with holderName
        //   3. POST /payments with:
        //        billingType         → 'CREDIT_CARD'
        //        creditCardToken     → from request
        //        creditCardHolderInfo → { name, email, cpfCnpj, phone, postalCode, etc. }
        //        installmentCount    → 1 (no installments for v1)
        //
        // NOTE: createPayment() signature may need updating to accept extra card data
        // when implementing this method.
        throw new \RuntimeException('TODO: implement AsaasGateway::createCreditCard');
    }
}
