<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Subscription extends Model
{
    protected $fillable = ['tenant_id', 'subscription_plan_id', 'status', 'gateway', 'gateway_customer_id', 'gateway_subscription_id', 'amount', 'currency', 'starts_at', 'current_period_start', 'current_period_end', 'cancelled_at', 'ended_at'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'starts_at' => 'datetime', 'current_period_start' => 'datetime', 'current_period_end' => 'datetime', 'cancelled_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function plan(): BelongsTo { return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id'); }
}
