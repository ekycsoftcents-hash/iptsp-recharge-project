<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentOrder extends Model
{
    protected $fillable = ['tenant_id', 'order_type', 'gateway', 'status', 'amount', 'currency', 'merchant_order_id', 'gateway_transaction_id', 'gateway_payment_url', 'gateway_response', 'paid_at', 'expires_at'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'gateway_response' => 'array', 'paid_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
