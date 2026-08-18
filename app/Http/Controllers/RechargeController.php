<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Provider;
use App\Models\Recharge;
use App\Models\TenantProviderCredential;
use App\Services\RechargeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

final class RechargeController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $customers = Customer::query()->where('tenant_id', $tenantId)->where('status', 'active')->orderBy('name')->get();
        $credentials = TenantProviderCredential::query()->with('provider')->where('tenant_id', $tenantId)->where('status', 'active')->get();
        $recharges = Recharge::query()->with(['customer', 'provider'])->where('tenant_id', $tenantId)->latest()->paginate(25);
        $balance = (float) ($request->user()->tenant?->wallet?->balance ?? 0);
        return view('recharges.index', compact('customers', 'credentials', 'recharges', 'balance'));
    }

    public function store(Request $request, RechargeService $service): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'credential_id' => ['required', 'integer', 'exists:tenant_provider_credentials,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000'],
        ]);

        $tenantId = (int) $request->user()->tenant_id;
        $customer = Customer::query()->where('tenant_id', $tenantId)->findOrFail($data['customer_id']);
        $credential = TenantProviderCredential::query()->where('tenant_id', $tenantId)->findOrFail($data['credential_id']);

        try {
            $identifier = (string) ($customer->external_customer_id ?: $customer->phone ?: $customer->name);
            $recharge = $service->create($tenantId, $customer->id, $credential, (string) $data['amount'], $identifier);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['amount' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('recharges.index')->with('status', 'Recharge created: ' . $recharge->client_reference);
    }
}
