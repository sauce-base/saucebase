<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\PaymentMethod;
use Modules\Billing\Models\Price;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Price::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PaymentMethod::class)->nullable()->constrained()->nullOnDelete();

            // Provider identifiers
            $table->string('provider_subscription_id')->nullable();

            // Status
            $table->string('status')->default('pending');

            // Trial period
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            // Billing period
            $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();

            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Configuration
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('provider_subscription_id');
            $table->index('status');
            $table->index('current_period_ends_at');
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
