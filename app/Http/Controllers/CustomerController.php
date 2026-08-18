<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()->where('tenant_id', $request->user()->tenant_id)->latest()->paginate(20);
        return view('customers.index', compact('customers'));
    }

    public function create(): View { return view('customers.create'); }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'external_customer_id' => ['nullable', 'string', 'max:255'],
            'pin' => ['nullable', 'string', 'max:100'],
        ]);

        Customer::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'external_customer_id' => $data['external_customer_id'] ?? null,
            'pin_encrypted' => $data['pin'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('customers.index')->with('status', 'Customer created.');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        abort_unless($customer->tenant_id === $request->user()->tenant_id, 403);
        $customer->delete();
        return redirect()->route('customers.index')->with('status', 'Customer removed.');
    }
}
