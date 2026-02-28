<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Billing\Models\Customer;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();

            // Provider identifiers
            $table->string('provider_payment_method_id')->nullable();

            // Type
            $table->string('type');

            // Payment method details (gateway-agnostic)
            $table->json('details')->nullable();

            // Configuration
            $table->json('metadata')->nullable();

            // Status
            $table->boolean('is_default')->default(false);

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('provider_payment_method_id');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
