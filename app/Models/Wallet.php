<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Wallet extends Model
{
    protected $fillable = ['tenant_id', 'currency', 'balance', 'held_balance'];

    protected function casts(): array
    {
        return ['balance' => 'decimal:2', 'held_balance' => 'decimal:2'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function transactions(): HasMany { return $this->hasMany(WalletTransaction::class); }
}
