<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\TenantProviderCredential;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProviderCredentialController extends Controller
{
    public function index(Request $request): View
    {
        $providers = Provider::query()->where('is_active', true)->orderBy('name')->get();
        $credentials = TenantProviderCredential::query()->with('provider')->where('tenant_id', $request->user()->tenant_id)->latest()->get();
        return view('providers.credentials', compact('providers', 'credentials'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'label' => ['nullable', 'string', 'max:100'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'base_url' => ['nullable', 'url', 'max:500'],
        ]);

        TenantProviderCredential::updateOrCreate(
            ['tenant_id' => $request->user()->tenant_id, 'provider_id' => $data['provider_id'], 'label' => $data['label'] ?? null],
            ['credentials' => array_filter(['username' => $data['username'] ?? null, 'password' => $data['password'] ?? null, 'api_key' => $data['api_key'] ?? null, 'base_url' => $data['base_url'] ?? null]), 'status' => 'active', 'last_error' => null],
        );

        return back()->with('status', 'Provider credential saved securely. Test it before live recharge.');
    }

    public function test(Request $request, TenantProviderCredential $credential): RedirectResponse
    {
        abort_unless($credential->tenant_id === $request->user()->tenant_id, 403);
        $credential->update(['last_tested_at' => now(), 'status' => 'active', 'last_error' => null]);
        return back()->with('status', 'Credential marked for connection test. Live provider verification must be implemented per provider API.');
    }
}
