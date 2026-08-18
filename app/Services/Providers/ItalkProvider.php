<?php

declare(strict_types=1);

namespace App\Services\Providers;

final class ItalkProvider implements RechargeProvider
{
    public function testConnection(array $credentials): ProviderResult
    {
        // Port the verified logic from the uploaded lib/scraper.php here.
        return new ProviderResult(false, message: 'iTalk adapter requires provider-specific endpoint configuration.');
    }

    public function recharge(array $credentials, array $request): ProviderResult
    {
        // Required request keys should be validated before this method is called.
        return new ProviderResult(false, message: 'iTalk recharge adapter is not configured yet.');
    }
}
