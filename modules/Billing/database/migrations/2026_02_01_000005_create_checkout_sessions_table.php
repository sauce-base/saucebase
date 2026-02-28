<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Price;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkout_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Foreign keys
            $table->foreignIdFor(Customer::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Price::class)->constrained()->cascadeOnDelete();

            // Provider identifiers
            $table->string('provider_session_id')->nullable();

            // URLs
            $table->string('success_url')->nullable();
            $table->string('cancel_url')->nullable();

            // Status
            $table->string('status')->default('pending');

            // Configuration
            $table->json('metadata')->nullable();

            // Expiration
            $table->timestamp('expires_at')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('provider_session_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_sessions');
    }
};
