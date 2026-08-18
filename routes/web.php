<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('dashboard'))->name('dashboard');

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'application' => 'IPTSP Recharge SaaS',
]))->name('health');
