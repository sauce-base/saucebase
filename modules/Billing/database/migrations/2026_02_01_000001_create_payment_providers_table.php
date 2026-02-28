<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_providers', function (Blueprint $table) {
            $table->id();

            // Identifiers
            $table->string('name');
            $table->string('slug')->unique();

            // Configuration
            $table->json('config')->nullable();

            // Status
            $table->boolean('is_active')->default(false);

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_providers');
    }
};
