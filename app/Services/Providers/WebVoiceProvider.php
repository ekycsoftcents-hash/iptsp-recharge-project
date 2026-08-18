<?php

declare(strict_types=1);

namespace App\Services\Providers;

use Illuminate\Support\Facades\Http;
use Throwable;

final class WebVoiceProvider implements RechargeProvider
{
    private function client(array $credentials)
    {
        return Http::acceptJson()->asJson()->timeout(30)->baseUrl(rtrim((string) ($credentials['base_url'] ?? ''), '/'));
    }

    private function login(array $credentials): ?string
    {
        $response = $this->client($credentials)->post('/api/login', ['username' => $credentials['username'] ?? '', 'password' => $credentials['password'] ?? '']);
        return $response->successful() ? ($response->json('token') ?: $response->json('access_token')) : null;
    }

    public function testConnection(array $credentials): ProviderResult
    {
        try {
            $token = $this->login($credentials);
            return $token ? new ProviderResult(true, message: 'WebVoice login succeeded.') : new ProviderResult(false, message: 'WebVoice login failed.');
        } catch (Throwable $e) {
            return new ProviderResult(false, message: 'WebVoice connection error.', raw: ['error' => $e->getMessage()]);
        }
    }

    public function recharge(array $credentials, array $request): ProviderResult
    {
        try {
            $token = $this->login($credentials);
            if (! $token) return new ProviderResult(false, message: 'WebVoice login failed.');
            $clientResponse = $this->client($credentials)->withToken($token)->get('/api/clients', ['page' => 1, 'per_page' => 1000]);
            $identifier = (string) ($request['customer_identifier'] ?? '');
            $client = collect($clientResponse->json('data', []))->first(fn (array $item): bool => (string) ($item['username'] ?? '') === $identifier || (string) ($item['callerid'] ?? '') === $identifier);
            if (! $client) return new ProviderResult(false, message: 'WebVoice customer was not found.');
            $response = $this->client($credentials)->withToken($token)->post('/api/clients/' . $client['id'] . '/payment', ['amount' => (float) $request['amount'], 'type' => 'credit', 'description' => 'IPTSP SaaS reseller recharge']);
            if (! $response->successful()) return new ProviderResult(false, message: 'WebVoice recharge request failed.', raw: ['http_status' => $response->status(), 'body' => $response->json()]);
            return new ProviderResult(true, providerReference: (string) ($response->json('id') ?: ''), message: 'WebVoice recharge submitted.', raw: $response->json() ?: []);
        } catch (Throwable $e) {
            return new ProviderResult(false, message: 'WebVoice recharge error.', raw: ['error' => $e->getMessage()]);
        }
    }
}
