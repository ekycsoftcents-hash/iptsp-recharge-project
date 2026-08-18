<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AuthController extends Controller
{
    public function showLogin(): View { return view('auth.login'); }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $remember = $request->boolean('remember');

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true], $remember)) {
            return back()->withErrors(['email' => 'Invalid credentials or inactive account.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function showRegister(): View { return view('auth.register'); }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $tenant = Tenant::create([
            'name' => $data['business_name'],
            'slug' => Str::slug($data['business_name']) . '-' . Str::lower(Str::random(6)),
            'email' => $data['email'],
            'status' => 'pending',
        ]);

        $tenant->wallet()->create(['currency' => 'BDT']);
        $user = $tenant->users()->create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'], 'role' => 'tenant_owner']);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Account created. Complete your monthly subscription to activate all features.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
