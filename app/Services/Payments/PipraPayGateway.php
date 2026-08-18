<?php

declare(strict_types=1);

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class PipraPayGateway
{
    private function baseUrl(): string
    {
        $baseUrl = rtrim((string) config('piprapay.base_url'), '/');
        $apiKey = (string) config('piprapay.api_key');
        if ($baseUrl === '' || $apiKey === '') {
            throw new RuntimeException('PipraPay is not configured.');
        }
        return $baseUrl;
    }

    private function client()
    {
        return Http::withHeaders(['MH-PIPRAPAY-API-KEY' => (string) config('piprapay.api_key')])
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('piprapay.timeout', 20));
    }

    public function createPayment(array $payload): array
    {
        $response = $this->client()->post($this->baseUrl() . '/api/checkout/redirect', [
            'full_name' => $payload['full_name'],
            'email_address' => $payload['email_address'],
            'mobile_number' => $payload['mobile_number'] ?? '',
            'amount' => (string) $payload['amount'],
            'currency' => $payload['currency'] ?? 'BDT',
            'metadata' => json_encode($payload['metadata'] ?? [], JSON_UNESCAPED_SLASHES),
            'return_url' => $payload['return_url'],
            'webhook_url' => $payload['webhook_url'],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('PipraPay checkout creation failed: ' . $response->body());
        }
        return $response->json();
    }

    public function verifyPayment(string $ppId): array
    {
        $response = $this->client()->post($this->baseUrl() . '/api/verify-payments', ['pp_id' => $ppId]);
        if ($response->failed()) {
            throw new RuntimeException('PipraPay payment verification failed: ' . $response->body());
        }
        return $response->json();
    }

    public function isValidWebhook(?string $providedApiKey): bool
    {
        $configured = (string) config('piprapay.api_key');
        return $configured !== '' && $providedApiKey !== null && hash_equals($configured, $providedApiKey);
    }
}
