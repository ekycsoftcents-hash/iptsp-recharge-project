<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'code', 'description', 'monthly_price', 'currency', 'max_users', 'max_customers', 'features', 'is_active'];

    protected function casts(): array
    {
        return ['monthly_price' => 'decimal:2', 'features' => 'array', 'is_active' => 'boolean'];
    }

    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
}
