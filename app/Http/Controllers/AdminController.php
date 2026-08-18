<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentOrder;
use App\Models\Recharge;
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
        $stats = [
            'tenants' => Tenant::query()->count(),
            'active_tenants' => Tenant::query()->where('status', 'active')->count(),
            'pending_tenants' => Tenant::query()->where('status', 'pending')->count(),
            'payment_volume' => (float) PaymentOrder::query()->where('status', 'paid')->sum('amount'),
            'recharges' => Recharge::query()->count(),
            'successful_recharges' => Recharge::query()->where('status', 'success')->count(),
        ];
        return view('admin.index', compact('tenants', 'plans', 'stats'));
    }

    public function updateTenantStatus(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($request->user()?->role === 'platform_admin' && $request->user()->is_active, 403);
        $data = $request->validate(['status' => ['required', 'in:pending,active,suspended,cancelled']]);
        $tenant->update(['status' => $data['status']]);
        return back()->with('status', 'Tenant status updated.');
    }
}
