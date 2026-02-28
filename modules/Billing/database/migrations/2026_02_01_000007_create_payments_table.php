<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\PaymentMethod;
use Modules\Billing\Models\Price;
use Modules\Billing\Models\Subscription;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Subscription::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(PaymentMethod::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Price::class)->nullable()->constrained()->nullOnDelete();

            // Provider identifiers
            $table->string('provider_payment_id')->nullable();

            // Amount
            $table->string('currency', 3);
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('amount_refunded')->default(0);

            // Status
            $table->string('status')->default('pending');

            // Failure info
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();

            // Configuration
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('provider_payment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
