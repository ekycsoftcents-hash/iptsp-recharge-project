<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'email', 'phone', 'status', 'timezone', 'settings', 'trial_ends_at'];

    protected function casts(): array
    {
        return ['settings' => 'array', 'trial_ends_at' => 'datetime'];
    }

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function wallet(): HasOne { return $this->hasOne(Wallet::class); }
    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
    public function activeSubscription(): HasOne { return $this->hasOne(Subscription::class)->whereIn('status', ['active', 'trialing'])->latestOfMany(); }
}
