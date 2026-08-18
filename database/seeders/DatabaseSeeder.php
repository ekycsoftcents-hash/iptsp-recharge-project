<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::updateOrCreate(['code' => 'starter'], [
            'name' => 'Starter', 'description' => 'For small reseller operations.', 'monthly_price' => 499, 'currency' => 'BDT', 'max_users' => 2, 'max_customers' => 500, 'features' => ['customer_management', 'wallet', 'basic_reports'], 'is_active' => true,
        ]);
        SubscriptionPlan::updateOrCreate(['code' => 'business'], [
            'name' => 'Business', 'description' => 'For growing reseller operations.', 'monthly_price' => 999, 'currency' => 'BDT', 'max_users' => 10, 'max_customers' => 5000, 'features' => ['customer_management', 'wallet', 'reports', 'priority_support'], 'is_active' => true,
        ]);
        SubscriptionPlan::updateOrCreate(['code' => 'enterprise'], [
            'name' => 'Enterprise', 'description' => 'For high-volume IPTSP resellers.', 'monthly_price' => 1999, 'currency' => 'BDT', 'max_users' => 50, 'max_customers' => null, 'features' => ['customer_management', 'wallet', 'reports', 'multi_provider', 'priority_support'], 'is_active' => true,
        ]);

        foreach ([
            ['name' => 'iTalk', 'code' => 'italk', 'adapter_class' => \App\Services\Providers\ItalkProvider::class],
            ['name' => 'Ranksitt', 'code' => 'ranksitt', 'adapter_class' => \App\Services\Providers\RanksittProvider::class],
            ['name' => 'WebVoice', 'code' => 'webvoice', 'adapter_class' => \App\Services\Providers\WebVoiceProvider::class],
        ] as $provider) {
            Provider::updateOrCreate(['code' => $provider['code']], $provider);
        }
    }
}
