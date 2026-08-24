<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Added separately rather than inlined into the users table, which is already live
     * wherever this application is deployed.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Null until the user picks one, which is what lets them keep inheriting the
            // application default rather than being frozen to whatever it was at signup.
            $table->string('locale')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
