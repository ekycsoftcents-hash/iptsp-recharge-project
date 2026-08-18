<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['tenant_id', 'name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public function isPlatformAdmin(): bool { return $this->role === 'platform_admin'; }
}
