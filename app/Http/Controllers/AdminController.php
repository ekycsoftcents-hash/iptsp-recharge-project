<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->role === 'platform_admin' && $request->user()->is_active, 403);
        $tenants = Tenant::query()->withCount('users')->latest()->paginate(25);
        $plans = SubscriptionPlan::query()->where('is_active', true)->orderBy('monthly_price')->get();
        return view('admin.index', compact('tenants', 'plans'));
    }

    public function updateTenantStatus(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($request->user()?->role === 'platform_admin' && $request->user()->is_active, 403);
        $data = $request->validate(['status' => ['required', 'in:pending,active,suspended,cancelled']]);
        $tenant->update(['status' => $data['status']]);
        return back()->with('status', 'Tenant status updated.');
    }
}
