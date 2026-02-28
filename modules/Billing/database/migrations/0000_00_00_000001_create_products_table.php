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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Identifiers
            $table->string('sku')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // Display & Marketing
            $table->integer('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_highlighted')->default(false);

            // Configuration
            $table->json('features')->nullable();
            $table->json('metadata')->nullable();

            // Status
            $table->boolean('is_active')->default(false);

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_active');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
