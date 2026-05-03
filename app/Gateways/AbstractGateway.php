<?php

namespace App\Gateways;

use App\Models\PaymentSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

abstract class AbstractGateway
{
    private ?array $cachedCredentials = null;

    abstract protected function gatewayName(): string;

    abstract protected function baseUrl(): string;

    /**
     * Returns decrypted credentials for this gateway from payment_settings.
     */
    protected function credentials(): array
    {
        if ($this->cachedCredentials !== null) {
            return $this->cachedCredentials;
        }

        $setting = PaymentSetting::where('gateway', $this->gatewayName())->first();

        if (! $setting) {
            throw new RuntimeException("Payment settings not configured for gateway: {$this->gatewayName()}");
        }

        return $this->cachedCredentials = $setting->getDecryptedCredentials();
    }

    /**
     * Returns a pre-configured HTTP client for this gateway.
     * Subclasses can override to add gateway-specific headers (e.g. Authorization).
     */
    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->timeout(30);
    }

    protected function credential(string $key): string
    {
        $value = $this->credentials()[$key] ?? null;

        if (! $value) {
            throw new RuntimeException("Missing credential '{$key}' for gateway: {$this->gatewayName()}");
        }

        return $value;
    }
}
