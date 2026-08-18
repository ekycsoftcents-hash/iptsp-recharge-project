<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentOrder;
use App\Models\SubscriptionPlan;
use App\Services\Payments\PipraPayGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

final class BillingController extends Controller
{
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::query()->where('is_active', true)->orderBy('monthly_price')->get();
        $subscription = $request->user()->tenant?->activeSubscription;
        return view('billing.index', compact('plans', 'subscription'));
    }

    public function subscriptionCheckout(Request $request, PipraPayGateway $gateway): RedirectResponse
    {
        $data = $request->validate(['subscription_plan_id' => ['required', 'integer', 'exists:subscription_plans,id']]);
        $plan = SubscriptionPlan::query()->where('is_active', true)->findOrFail($data['subscription_plan_id']);
        $order = PaymentOrder::create([
            'tenant_id' => $request->user()->tenant_id,
            'order_type' => 'subscription',
            'gateway' => 'piprapay',
            'amount' => $plan->monthly_price,
            'currency' => $plan->currency,
            'merchant_order_id' => 'SUB-' . Str::upper(Str::random(20)),
            'expires_at' => now()->addHour(),
        ]);

        $response = $gateway->createPayment([
            'amount' => (string) $order->amount,
            'currency' => $order->currency,
            'merchant_order_id' => $order->merchant_order_id,
            'full_name' => $request->user()->name,
            'email_address' => $request->user()->email,
            'mobile_number' => $request->user()->tenant?->phone ?? '',
            'return_url' => config('piprapay.return_url'),
            'webhook_url' => route('webhooks.piprapay'),
            'metadata' => ['order_id' => $order->merchant_order_id, 'payment_order_id' => $order->id],
        ]);

        $url = $response['payment_url'] ?? $response['checkout_url'] ?? $response['pp_url'] ?? $response['url'] ?? null;
        if (! is_string($url) || $url === '') {
            throw new RuntimeException('PipraPay did not return a payment URL. Confirm your API version and endpoint.');
        }

        $order->update(['gateway_payment_url' => $url, 'gateway_response' => $response]);
        return redirect()->away($url);
    }

    public function returnFromGateway(Request $request): RedirectResponse
    {
        return redirect()->route('dashboard')->with('status', 'Payment return received. Subscription activates only after server-side verification.');
    }

    public function cancelFromGateway(): RedirectResponse
    {
        return redirect()->route('dashboard')->withErrors(['payment' => 'Payment was cancelled.']);
    }
}
