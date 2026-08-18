@extends('layouts.app')
@section('content')
<h1>Reseller dashboard</h1>
<p class="muted">Welcome, {{ auth()->user()->name }}. Business: {{ auth()->user()->tenant?->name ?? 'Platform admin' }}</p>
<div class="grid"><div class="card"><span class="label">Wallet balance</span><strong class="value">৳ {{ number_format((float) (auth()->user()->tenant?->wallet?->balance ?? 0), 2) }}</strong></div><div class="card"><span class="label">Customers</span><strong class="value">{{ auth()->user()->tenant?->customers()->count() ?? 0 }}</strong></div><div class="card"><span class="label">Subscription</span><strong class="value">Pending</strong></div><div class="card"><span class="label">Providers</span><strong class="value">3</strong></div></div>
<div class="card" style="margin-top:18px"><h2>Quick actions</h2><a class="btn" href="{{ route('customers.create') }}">Add customer</a> <a class="btn" href="{{ route('customers.index') }}">Manage customers</a></div>
<div class="card"><h2>Production note</h2><p class="muted">Live PipraPay checkout and provider recharge execution must be configured and tested before real funds or customer PINs are used.</p></div>
@endsection
