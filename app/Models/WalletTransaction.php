<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WalletTransaction extends Model
{
    protected $fillable = ['tenant_id', 'wallet_id', 'type', 'amount', 'balance_before', 'balance_after', 'reference_type', 'reference_id', 'idempotency_key', 'description', 'metadata'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'balance_before' => 'decimal:2', 'balance_after' => 'decimal:2', 'metadata' => 'array'];
    }

    public function wallet(): BelongsTo { return $this->belongsTo(Wallet::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
