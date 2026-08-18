<?php

declare(strict_types=1);

namespace App\Services\Providers;

use Illuminate\Support\Facades\Http;
use Throwable;

final class RanksittProvider implements RechargeProvider
{
    private function client(array $credentials)
    {
        return Http::acceptJson()->asJson()->timeout(30)->baseUrl(rtrim((string) ($credentials['base_url'] ?? ''), '/'));
    }

    private function login(array $credentials): ?string
    {
        $response = $this->client($credentials)->post('/api/Account/SignIn', ['username' => $credentials['username'] ?? '', 'password' => $credentials['password'] ?? '']);
        return $response->successful() ? ($response->json('token') ?: $response->json('accessToken')) : null;
    }

    public function testConnection(array $credentials): ProviderResult
    {
        try {
            $token = $this->login($credentials);
            return $token ? new ProviderResult(true, message: 'Ranksitt login succeeded.') : new ProviderResult(false, message: 'Ranksitt login failed.');
        } catch (Throwable $e) {
            return new ProviderResult(false, message: 'Ranksitt connection error.', raw: ['error' => $e->getMessage()]);
        }
    }

    public function recharge(array $credentials, array $request): ProviderResult
    {
        try {
            $token = $this->login($credentials);
            if (! $token) return new ProviderResult(false, message: 'Ranksitt login failed.');
            $clientResponse = $this->client($credentials)->withToken($token)->get('/api/ResellerClientsRetail/GetClientsRetailsList', ['draw' => 1, 'start' => 0, 'length' => 1000, 'active' => 'true', 'orderBy' => 'id', 'orderByType' => 'asc']);
            $client = collect($clientResponse->json('data', []))->firstWhere('login', (string) ($request['customer_identifier'] ?? ''));
            if (! $client) return new ProviderResult(false, message: 'Ranksitt customer was not found.');
            $response = $this->client($credentials)->withToken($token)->post('/api/ResellerClients/AddPayment', ['clientId' => $client['id'], 'clientType' => 32, 'amount' => (float) $request['amount'], 'balance' => 0, 'credit' => 0, 'type' => 'Payment', 'date' => now()->utc()->format('Y-m-d\\TH:i:s.000\\Z'), 'description' => 'IPTSP SaaS reseller recharge', 'addToInvoice' => false, 'sendConfirmation' => false]);
            if (! $response->successful()) return new ProviderResult(false, message: 'Ranksitt recharge request failed.', raw: ['http_status' => $response->status(), 'body' => $response->json()]);
            return new ProviderResult(true, providerReference: (string) ($response->json('id') ?: ''), message: 'Ranksitt recharge submitted.', raw: $response->json() ?: []);
        } catch (Throwable $e) {
            return new ProviderResult(false, message: 'Ranksitt recharge error.', raw: ['error' => $e->getMessage()]);
        }
    }
}
