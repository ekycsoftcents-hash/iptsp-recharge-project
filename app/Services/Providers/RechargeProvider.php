<?php

declare(strict_types=1);

namespace App\Services\Providers;

interface RechargeProvider
{
    public function testConnection(array $credentials): ProviderResult;

    public function recharge(array $credentials, array $request): ProviderResult;
}

final readonly class ProviderResult
{
    public function __construct(
        public bool $success,
        public ?string $providerReference = null,
        public ?string $message = null,
        public array $raw = [],
    ) {}
}
