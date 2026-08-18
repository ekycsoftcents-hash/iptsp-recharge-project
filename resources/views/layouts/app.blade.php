<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'IPTSP Recharge SaaS' }}</title>
    <style>
        body{font-family:system-ui,sans-serif;margin:0;background:#f1f5f9;color:#0f172a}.nav{background:#0f172a;color:#fff;padding:16px 24px;display:flex;justify-content:space-between}.nav a{color:#cbd5e1;text-decoration:none;margin-left:16px}.wrap{max-width:1000px;margin:32px auto;padding:0 18px}.card{background:#fff;border-radius:12px;padding:22px;box-shadow:0 4px 18px #0f172a12;margin-bottom:18px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}.btn{background:#2563eb;color:#fff;padding:10px 15px;border:0;border-radius:7px;text-decoration:none;cursor:pointer}.input{display:block;width:100%;box-sizing:border-box;padding:10px;margin:7px 0 14px;border:1px solid #cbd5e1;border-radius:7px}.error{color:#b91c1c}.success{color:#166534;background:#dcfce7;padding:10px;border-radius:7px}
    </style>
</head>
<body>
<nav class="nav"><strong>IPTSP Recharge SaaS</strong><span>@auth<a href="{{ route('dashboard') }}">Dashboard</a><a href="{{ route('customers.index') }}">Customers</a><form style="display:inline" method="POST" action="{{ route('logout') }}">@csrf<button style="background:none;border:0;color:#cbd5e1;cursor:pointer">Logout</button></form>@else<a href="{{ route('login') }}">Login</a><a href="{{ route('register') }}">Register</a>@endauth</span></nav>
<main class="wrap">@if(session('status'))<div class="success">{{ session('status') }}</div>@endif @yield('content')</main>
</body>
</html>
