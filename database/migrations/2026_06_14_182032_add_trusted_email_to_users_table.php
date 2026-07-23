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
        Schema::table('users', function (Blueprint $table) {
            $table->string('trusted_email')->nullable()->after('email');
            $table->string('trusted_email_otp')->nullable()->after('trusted_email');
            $table->tinyInteger('is_trusted_email_verified')->default(0)->after('trusted_email_otp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['trusted_email', 'trusted_email_otp', 'is_trusted_email_verified']);
        });
    }
};
