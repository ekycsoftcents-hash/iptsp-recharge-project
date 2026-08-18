<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('DEMO_ADMIN_EMAIL');
        $adminPassword = env('DEMO_ADMIN_PASSWORD');
        $resellerEmail = env('DEMO_RESELLER_EMAIL');
        $resellerPassword = env('DEMO_RESELLER_PASSWORD');

        if (! $adminEmail || ! $adminPassword || ! $resellerEmail || ! $resellerPassword) {
            $this->command?->warn('Demo users skipped: set DEMO_ADMIN_EMAIL, DEMO_ADMIN_PASSWORD, DEMO_RESELLER_EMAIL, and DEMO_RESELLER_PASSWORD in .env.');
            return;
        }

        User::updateOrCreate(['email' => $adminEmail], [
            'name' => 'Demo Super Admin', 'password' => $adminPassword, 'role' => 'platform_admin', 'tenant_id' => null, 'is_active' => true,
        ]);

        $tenant = Tenant::updateOrCreate(['email' => $resellerEmail], [
            'name' => 'Demo Reseller', 'slug' => 'demo-reseller', 'status' => 'active',
        ]);
        $tenant->wallet()->firstOrCreate([], ['currency' => 'BDT']);
        $tenant->users()->updateOrCreate(['email' => $resellerEmail], [
            'name' => 'Demo Reseller Owner', 'password' => $resellerPassword, 'role' => 'tenant_owner', 'is_active' => true,
        ]);
    }
}
