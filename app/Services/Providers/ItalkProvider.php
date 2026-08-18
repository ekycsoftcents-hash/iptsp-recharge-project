<?php

declare(strict_types=1);

namespace App\Services\Providers;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Throwable;

final class ItalkProvider implements RechargeProvider
{
    private function client(CookieJar $cookies)
    {
        return Http::withOptions(['cookies' => $cookies, 'allow_redirects' => false])->timeout(60)->withHeaders(['User-Agent' => 'IPTSP-Recharge-SaaS/1.0']);
    }

    private function login(array $credentials, CookieJar $cookies): bool
    {
        $base = rtrim((string) ($credentials['base_url'] ?? ''), '/');
        $path = '/' . trim((string) ($credentials['base_path'] ?? '/iconiptsp/'), '/') . '/';
        $sso = rtrim((string) ($credentials['sso_url'] ?? ($base . ':2020/OTTSSO')), '/');
        $this->client($cookies)->get($sso . '/?redirect=' . urlencode($base . $path . 'Login.do') . '&logout=' . urlencode($base . $path . 'Logout.do') . '&audience=sso&app=billing');
        $login = $this->client($cookies)->asForm()->post($sso . '/login', ['username' => $credentials['username'] ?? '', 'password' => $credentials['password'] ?? '', 'rememberMe' => 'off']);
        if ($login->status() !== 302) return false;
        $location = $login->header('Location');
        return is_string($location) && $location !== '' && $this->client($cookies)->get($location)->successful();
    }

    private function findAccount(array $credentials, CookieJar $cookies, string $identifier): ?string
    {
        $base = rtrim((string) ($credentials['base_url'] ?? ''), '/');
        $path = '/' . trim((string) ($credentials['base_path'] ?? '/iconiptsp/'), '/') . '/';
        $html = $this->client($cookies)->get($base . $path . 'pin/pinRecharge.jsp')->body();
        preg_match_all('/<input[^>]+name="pinNO"[^>]+value="([^"]+)"/i', $html, $pins);
        preg_match_all('/<input[^>]+name="accountID"[^>]+value="([^"]+)"/i', $html, $accounts);
        foreach ($pins[1] ?? [] as $index => $pin) {
            if (trim($pin) === trim($identifier)) return $accounts[1][$index] ?? null;
        }
        return null;
    }

    public function testConnection(array $credentials): ProviderResult
    {
        try {
            $cookies = new CookieJar();
            return $this->login($credentials, $cookies) ? new ProviderResult(true, message: 'iTalk SSO login succeeded.') : new ProviderResult(false, message: 'iTalk SSO login failed.');
        } catch (Throwable $e) {
            return new ProviderResult(false, message: 'iTalk connection error.', raw: ['error' => $e->getMessage()]);
        }
    }

    public function recharge(array $credentials, array $request): ProviderResult
    {
        try {
            $cookies = new CookieJar();
            if (! $this->login($credentials, $cookies)) return new ProviderResult(false, message: 'iTalk SSO login failed.');
            $identifier = (string) ($request['customer_identifier'] ?? '');
            $accountId = $this->findAccount($credentials, $cookies, $identifier);
            if (! $accountId) return new ProviderResult(false, message: 'iTalk customer PIN was not found.');
            $base = rtrim((string) ($credentials['base_url'] ?? ''), '/');
            $path = '/' . trim((string) ($credentials['base_path'] ?? '/iconiptsp/'), '/') . '/';
            $response = $this->client($cookies)->asForm()->post($base . $path . 'RechargePin.do', ['pinNO' => $identifier, 'accountID' => $accountId, 'RechargeAmount' => $request['amount'], 'submit' => 'Recharge']);
            $body = strtolower($response->body());
            $success = $response->status() === 302 || str_contains($body, 'recharge successful') || str_contains($body, 'successfully');
            return new ProviderResult($success, providerReference: $accountId, message: $success ? 'iTalk recharge submitted.' : 'iTalk recharge failed.', raw: ['http_status' => $response->status()]);
        } catch (Throwable $e) {
            return new ProviderResult(false, message: 'iTalk recharge error.', raw: ['error' => $e->getMessage()]);
        }
    }
}
