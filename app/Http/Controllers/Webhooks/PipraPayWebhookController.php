<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\PaymentOrder;
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
        $metadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];
        $merchantOrderId = (string) ($metadata['order_id'] ?? $metadata['merchant_order_id'] ?? '');
        $transactionId = (string) ($payload['transaction_id'] ?? '');
        $status = strtolower((string) ($payload['status'] ?? ''));
        $successful = in_array($status, ['completed', 'paid', 'success'], true);

        DB::transaction(function () use ($merchantOrderId, $ppId, $transactionId, $successful, $payload): void {
            $query = PaymentOrder::query()->lockForUpdate();
            $order = $merchantOrderId !== '' ? $query->where('merchant_order_id', $merchantOrderId)->first() : null;
            if (! $order || $order->status === 'paid') {
                return;
            }
            $order->update([
                'status' => $successful ? 'paid' : 'failed',
                'gateway_transaction_id' => $transactionId !== '' ? $transactionId : ($ppId !== '' ? $ppId : null),
                'gateway_response' => $payload,
                'paid_at' => $successful ? now() : null,
            ]);
        });

        return response()->json(['status' => true, 'message' => 'Webhook received']);
    }
}
