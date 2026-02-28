<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Subscription;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Subscription::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Payment::class)->nullable()->constrained()->nullOnDelete();

            // Provider identifiers
            $table->string('provider_invoice_id')->nullable();

            // Invoice details
            $table->string('number')->nullable();
            $table->string('currency', 3);
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('total');

            // Status
            $table->string('status')->default('draft');

            // Dates
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();

            // URLs
            $table->string('hosted_invoice_url')->nullable();
            $table->string('pdf_url')->nullable();

            // Configuration
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('provider_invoice_id');
            $table->index('number');
            $table->index('status');
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
