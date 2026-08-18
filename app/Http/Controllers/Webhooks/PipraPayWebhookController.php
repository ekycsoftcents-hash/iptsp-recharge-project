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
        $payload = $request->all();
        $signature = $request->header('X-PipraPay-Signature');

        abort_unless($gateway->isValidWebhook($payload, $signature), 401, 'Invalid webhook signature.');

        $merchantOrderId = (string) ($payload['merchant_order_id'] ?? $payload['order_id'] ?? '');
        $transactionId = (string) ($payload['transaction_id'] ?? $payload['trx_id'] ?? '');
        $status = strtolower((string) ($payload['status'] ?? ''));

        DB::transaction(function () use ($merchantOrderId, $transactionId, $status, $payload): void {
            $order = PaymentOrder::query()->where('merchant_order_id', $merchantOrderId)->lockForUpdate()->firstOrFail();
            if ($order->status === 'paid') {
                return;
            }
            $order->update([
                'status' => in_array($status, ['paid', 'success', 'completed'], true) ? 'paid' : 'failed',
                'gateway_transaction_id' => $transactionId ?: null,
                'gateway_response' => $payload,
                'paid_at' => in_array($status, ['paid', 'success', 'completed'], true) ? now() : null,
            ]);
        });

        return response()->json(['received' => true]);
    }
}
