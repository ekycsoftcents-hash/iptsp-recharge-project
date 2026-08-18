<?php

declare(strict_types=1);

namespace App\Services\Providers;

final class RanksittProvider implements RechargeProvider
{
    public function testConnection(array $credentials): ProviderResult
    {
        // Port and normalize the uploaded lib/RanksittAPI.php implementation here.
        return new ProviderResult(false, message: 'Ranksitt adapter requires provider-specific endpoint configuration.');
    }

    public function recharge(array $credentials, array $request): ProviderResult
    {
        return new ProviderResult(false, message: 'Ranksitt recharge adapter is not configured yet.');
    }
}
