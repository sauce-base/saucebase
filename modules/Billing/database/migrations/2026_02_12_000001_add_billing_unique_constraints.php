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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['provider_subscription_id']);
            $table->unique('provider_subscription_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['provider_payment_id']);
            $table->unique('provider_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropUnique(['provider_subscription_id']);
            $table->index('provider_subscription_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['provider_payment_id']);
            $table->index('provider_payment_id');
        });
    }
};
