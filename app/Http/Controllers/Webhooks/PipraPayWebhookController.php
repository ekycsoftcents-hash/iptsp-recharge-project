<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\PaymentOrder;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Payments\PipraPayGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class PipraPayWebhookController extends Controller
{
    public function handle(Request $request, PipraPayGateway $gateway): JsonResponse
    {
        abort_unless($gateway->isValidWebhook($request->header('MH-PIPRAPAY-API-KEY')), 401, 'Invalid webhook API key.');
        $payload = $request->all();
        $ppId = (string) ($payload['pp_id'] ?? '');
        if ($ppId === '') return response()->json(['status' => false, 'message' => 'Missing pp_id.'], 422);

        $verified = $gateway->verifyPayment($ppId);
        $verifiedStatus = strtolower((string) ($verified['status'] ?? $verified['data']['status'] ?? $payload['status'] ?? ''));
        $successful = in_array($verifiedStatus, ['completed', 'paid', 'success'], true);
        $metadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : (is_array($verified['metadata'] ?? null) ? $verified['metadata'] : []);
        $merchantOrderId = (string) ($metadata['order_id'] ?? $metadata['merchant_order_id'] ?? '');
        if ($merchantOrderId === '') return response()->json(['status' => false, 'message' => 'Missing order metadata.'], 422);

        DB::transaction(function () use ($merchantOrderId, $ppId, $successful, $payload, $verified, $metadata): void {
            $order = PaymentOrder::query()->where('merchant_order_id', $merchantOrderId)->lockForUpdate()->first();
            if (! $order || $order->status === 'paid') return;
            $order->update(['status' => $successful ? 'paid' : 'failed', 'gateway_transaction_id' => $ppId, 'gateway_response' => ['webhook' => $payload, 'verified' => $verified], 'paid_at' => $successful ? now() : null]);
            if (! $successful) return;

            if ($order->order_type === 'wallet_deposit') {
                $wallet = Wallet::query()->lockForUpdate()->firstOrCreate(['tenant_id' => $order->tenant_id], ['currency' => $order->currency, 'balance' => 0, 'held_balance' => 0]);
                $before = (float) $wallet->balance;
                $wallet->balance = $before + (float) $order->amount;
                $wallet->save();
                WalletTransaction::firstOrCreate(['idempotency_key' => 'payment-' . $order->id], ['tenant_id' => $order->tenant_id, 'wallet_id' => $wallet->id, 'type' => 'credit', 'amount' => $order->amount, 'balance_before' => $before, 'balance_after' => $wallet->balance, 'reference_type' => PaymentOrder::class, 'reference_id' => $order->id, 'description' => 'Verified PipraPay wallet deposit']);
            }
            if ($order->order_type === 'subscription') {
                $planId = (int) ($metadata['subscription_plan_id'] ?? 0);
                $plan = SubscriptionPlan::query()->find($planId);
                if ($plan) {
                    Subscription::updateOrCreate(['tenant_id' => $order->tenant_id, 'subscription_plan_id' => $plan->id, 'status' => 'active'], ['gateway' => 'piprapay', 'amount' => $order->amount, 'currency' => $order->currency, 'starts_at' => now(), 'current_period_start' => now(), 'current_period_end' => now()->addMonth()]);
                }
            }
        });

        return response()->json(['status' => true, 'message' => 'Webhook received']);
    }
}
