<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Provider extends Model
{
    protected $fillable = ['name', 'code', 'adapter_class', 'description', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function credentials(): HasMany { return $this->hasMany(TenantProviderCredential::class); }
}
