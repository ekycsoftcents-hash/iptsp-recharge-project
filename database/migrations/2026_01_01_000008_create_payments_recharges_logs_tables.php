<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('order_type'); // subscription, wallet_deposit
            $table->string('gateway')->default('piprapay');
            $table->string('status')->default('pending'); // pending, paid, failed, expired, refunded
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('BDT');
            $table->string('merchant_order_id')->unique();
            $table->string('gateway_transaction_id')->nullable()->unique();
            $table->string('gateway_payment_url')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'order_type', 'status']);
        });

        Schema::create('recharges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('tenant_provider_credential_id')->constrained('tenant_provider_credentials')->restrictOnDelete();
            $table->string('status')->default('pending'); // pending, processing, success, failed, refunded
            $table->decimal('amount', 14, 2);
            $table->decimal('cost_amount', 14, 2)->nullable();
            $table->string('currency', 3)->default('BDT');
            $table->string('client_reference')->nullable();
            $table->string('provider_reference')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['provider_id', 'provider_reference']);
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'event']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('recharges');
        Schema::dropIfExists('payment_orders');
    }
};
