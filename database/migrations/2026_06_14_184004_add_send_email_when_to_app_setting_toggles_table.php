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
        Schema::table('app_setting_toggles', function (Blueprint $table) {
            $table->string('send_email_when')->default('after_1_hours_offline')->nullable()->after('email_notification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_setting_toggles', function (Blueprint $table) {
            $table->dropColumn('send_email_when');
        });
    }
};
