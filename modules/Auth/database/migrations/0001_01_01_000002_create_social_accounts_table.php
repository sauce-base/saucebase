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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // google, github, facebook, etc.
            $table->string('provider_id'); // unique ID from the provider
            $table->text('provider_token')->nullable(); // access token
            $table->string('provider_refresh_token')->nullable(); // refresh token
            $table->timestamp('provider_token_expires_at')->nullable();
            $table->string('provider_avatar_url')->nullable(); // avatar URL from provider
            $table->timestamp('last_login_at')->nullable(); // track when provider was last used
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
            $table->index(['user_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
