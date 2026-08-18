<?php

declare(strict_types=1);

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class PipraPayGateway
{
    public function createPayment(array $payload): array
    {
        $baseUrl = rtrim((string) config('piprapay.base_url'), '/');
        $apiKey = (string) config('piprapay.api_key');

        if ($baseUrl === '' || $apiKey === '') {
            throw new RuntimeException('PipraPay is not configured.');
        }

        // Confirm the exact endpoint and payload names against your PipraPay account/API version.
        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('piprapay.timeout', 20))
            ->post($baseUrl . '/api/payment/create', $payload);

        if ($response->failed()) {
            throw new RuntimeException('PipraPay payment creation failed: ' . $response->body());
        }

        return $response->json();
    }

    public function verifyPayment(string $merchantOrderId): array
    {
        $baseUrl = rtrim((string) config('piprapay.base_url'), '/');
        $apiKey = (string) config('piprapay.api_key');

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout((int) config('piprapay.timeout', 20))
            ->get($baseUrl . '/api/payment/verify', [
                'merchant_order_id' => $merchantOrderId,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('PipraPay payment verification failed: ' . $response->body());
        }

        return $response->json();
    }

    public function isValidWebhook(array $payload, ?string $signature): bool
    {
        $secret = (string) config('piprapay.webhook_secret');
        if ($secret === '' || $signature === null) {
            return false;
        }

        $provided = hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES), $secret);
        return hash_equals($provided, $signature);
    }
}
