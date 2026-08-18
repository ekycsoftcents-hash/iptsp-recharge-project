<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recharge;
use App\Models\TenantProviderCredential;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class RechargeService
{
    public function create(int $tenantId, int $customerId, TenantProviderCredential $credential, string $amount, string $customerIdentifier = ''): Recharge
    {
        $value = round((float) $amount, 2);
        if ($value <= 0) throw new RuntimeException('Recharge amount must be greater than zero.');
        $credential->loadMissing('provider');
        $demo = (bool) config('recharge.demo_mode', true);
        $providerResult = null;

        if (! $demo) {
            $adapterClass = (string) ($credential->provider->adapter_class ?? '');
            if ($adapterClass === '' || ! class_exists($adapterClass)) throw new RuntimeException('Provider adapter is not configured.');
            $providerResult = app($adapterClass)->recharge($credential->credentials, ['amount' => $value, 'customer_identifier' => $customerIdentifier]);
            if (! $providerResult->success) throw new RuntimeException($providerResult->message ?: 'Provider recharge failed.');
        }

        return DB::transaction(function () use ($tenantId, $customerId, $credential, $value, $demo, $providerResult): Recharge {
            $wallet = $credential->tenant()->firstOrFail()->wallet()->lockForUpdate()->firstOrFail();
            $before = (float) $wallet->balance;
            if ($before < $value) throw new RuntimeException('Insufficient wallet balance.');
            $reference = 'RCH-' . Str::upper(Str::random(20));
            $recharge = Recharge::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'provider_id' => $credential->provider_id,
                'tenant_provider_credential_id' => $credential->id,
                'status' => $demo ? 'success' : 'success',
                'amount' => $value,
                'cost_amount' => $value,
                'currency' => $wallet->currency,
                'client_reference' => $reference,
                'provider_reference' => $demo ? 'DEMO-' . Str::upper(Str::random(16)) : $providerResult?->providerReference,
                'provider_response' => $demo ? ['mode' => 'demo', 'message' => 'Provider execution disabled until live mode is enabled.'] : $providerResult?->raw,
            ]);
            $wallet->balance = $before - $value;
            $wallet->save();
            WalletTransaction::create([
                'tenant_id' => $tenantId, 'wallet_id' => $wallet->id, 'type' => 'debit', 'amount' => $value,
                'balance_before' => $before, 'balance_after' => $wallet->balance, 'reference_type' => Recharge::class,
                'reference_id' => $recharge->id, 'idempotency_key' => 'recharge-' . $recharge->id,
                'description' => 'Recharge debit for ' . $reference,
            ]);
            return $recharge;
        });
    }
}
