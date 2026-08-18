<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant'])->group(function (): void {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::post('/billing/subscription/checkout', [\App\Http\Controllers\BillingController::class, 'subscriptionCheckout'])
        ->name('billing.subscription.checkout');

    Route::post('/wallet/deposit/checkout', [\App\Http\Controllers\BillingController::class, 'walletDepositCheckout'])
        ->name('wallet.deposit.checkout');

    Route::post('/recharges', [\App\Http\Controllers\RechargeController::class, 'store'])
        ->name('recharges.store');
});

Route::post('/webhooks/piprapay', [\App\Http\Controllers\Webhooks\PipraPayWebhookController::class, 'handle'])
    ->name('webhooks.piprapay');

Route::get('/payments/piprapay/return', [\App\Http\Controllers\BillingController::class, 'returnFromGateway'])
    ->name('payments.piprapay.return');

Route::get('/payments/piprapay/cancel', [\App\Http\Controllers\BillingController::class, 'cancelFromGateway'])
    ->name('payments.piprapay.cancel');
