<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'name', 'phone', 'external_customer_id', 'pin_encrypted', 'status', 'metadata'];

    protected $hidden = ['pin_encrypted'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'pin_encrypted' => 'encrypted'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
