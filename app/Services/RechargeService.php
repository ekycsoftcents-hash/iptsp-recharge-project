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
    public function create(int $tenantId, int $customerId, TenantProviderCredential $credential, string $amount): Recharge
    {
        $value = round((float) $amount, 2);
        if ($value <= 0) {
            throw new RuntimeException('Recharge amount must be greater than zero.');
        }

        return DB::transaction(function () use ($tenantId, $customerId, $credential, $value): Recharge {
            $wallet = $credential->tenant()->firstOrFail()->wallet()->lockForUpdate()->firstOrFail();
            $before = (float) $wallet->balance;
            if ($before < $value) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $recharge = Recharge::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'provider_id' => $credential->provider_id,
                'tenant_provider_credential_id' => $credential->id,
                'status' => config('recharge.demo_mode', true) ? 'success' : 'pending',
                'amount' => $value,
                'cost_amount' => $value,
                'currency' => $wallet->currency,
                'client_reference' => 'RCH-' . Str::upper(Str::random(20)),
                'provider_reference' => config('recharge.demo_mode', true) ? 'DEMO-' . Str::upper(Str::random(16)) : null,
                'provider_response' => config('recharge.demo_mode', true) ? ['mode' => 'demo', 'message' => 'Provider execution disabled until live credentials/API mapping is configured.'] : null,
            ]);

            $wallet->balance = $before - $value;
            $wallet->save();

            WalletTransaction::create([
                'tenant_id' => $tenantId,
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $value,
                'balance_before' => $before,
                'balance_after' => $wallet->balance,
                'reference_type' => Recharge::class,
                'reference_id' => $recharge->id,
                'idempotency_key' => 'recharge-' . $recharge->id,
                'description' => 'Recharge debit for ' . $recharge->client_reference,
            ]);

            return $recharge;
        });
    }
}
