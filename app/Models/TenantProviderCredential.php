<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenantProviderCredential extends Model
{
    protected $fillable = ['tenant_id', 'provider_id', 'label', 'credentials', 'status', 'last_tested_at', 'last_error'];

    protected $hidden = ['credentials'];

    protected function casts(): array
    {
        return ['credentials' => 'encrypted:array', 'last_tested_at' => 'datetime'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }
}
