<?php

declare(strict_types=1);

namespace App\Services\Providers;

final class WebVoiceProvider implements RechargeProvider
{
    public function testConnection(array $credentials): ProviderResult
    {
        // Port and normalize the uploaded lib/WebVoiceAPI.php implementation here.
        return new ProviderResult(false, message: 'WebVoice adapter requires provider-specific endpoint configuration.');
    }

    public function recharge(array $credentials, array $request): ProviderResult
    {
        return new ProviderResult(false, message: 'WebVoice recharge adapter is not configured yet.');
    }
}
