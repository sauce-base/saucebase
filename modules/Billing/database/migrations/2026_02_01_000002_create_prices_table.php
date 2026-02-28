<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Billing\Models\PaymentProvider;
use Modules\Billing\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PaymentProvider::class)->nullable()->constrained()->nullOnDelete();

            // Identifiers
            $table->string('provider_price_id')->nullable();

            // Pricing
            $table->string('currency', 3);
            $table->unsignedBigInteger('amount');
            $table->string('billing_scheme')->default('flat_amount');

            // Billing interval
            $table->string('interval')->nullable();
            $table->unsignedInteger('interval_count')->nullable();

            // Configuration
            $table->json('metadata')->nullable();

            // Status
            $table->boolean('is_active')->default(false);

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('provider_price_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
