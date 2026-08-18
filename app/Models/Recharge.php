<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Recharge extends Model
{
    protected $fillable = ['tenant_id', 'customer_id', 'provider_id', 'tenant_provider_credential_id', 'status', 'amount', 'cost_amount', 'currency', 'client_reference', 'provider_reference', 'failure_reason', 'provider_response'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'cost_amount' => 'decimal:2', 'provider_response' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }
    public function credential(): BelongsTo { return $this->belongsTo(TenantProviderCredential::class, 'tenant_provider_credential_id'); }
}
