<?php

declare(strict_types=1);

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProviderCredentialController;
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\Webhooks\PipraPayWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('landing'));
Route::get('/health', fn () => response()->json(['status' => 'ok', 'application' => 'IPTSP Recharge SaaS']))->name('health');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', fn () => view('dashboard.home'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/providers/credentials', [ProviderCredentialController::class, 'index'])->name('providers.credentials');
    Route::post('/providers/credentials', [ProviderCredentialController::class, 'store'])->name('providers.credentials.store');
    Route::post('/providers/credentials/{credential}/test', [ProviderCredentialController::class, 'test'])->name('providers.credentials.test');
    Route::get('/recharges', [RechargeController::class, 'index'])->name('recharges.index');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/recharges', [RechargeController::class, 'store'])->name('recharges.store');
    Route::post('/billing/subscription/checkout', [BillingController::class, 'subscriptionCheckout'])->name('billing.subscription.checkout');
    Route::get('/payments/piprapay/return', [BillingController::class, 'returnFromGateway'])->name('payments.piprapay.return');
    Route::get('/payments/piprapay/cancel', [BillingController::class, 'cancelFromGateway'])->name('payments.piprapay.cancel');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::patch('/tenants/{tenant}/status', [AdminController::class, 'updateTenantStatus'])->name('tenants.status');
    });
});

Route::post('/webhooks/piprapay', [PipraPayWebhookController::class, 'handle'])->name('webhooks.piprapay');
