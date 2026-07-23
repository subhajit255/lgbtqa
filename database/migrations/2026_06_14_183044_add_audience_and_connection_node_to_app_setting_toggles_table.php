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
            $table->string('audience')->default('open')->nullable()->after('pride_events_nearby');
            $table->string('connection_node')->default('open')->nullable()->after('audience');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_setting_toggles', function (Blueprint $table) {
            $table->dropColumn(['audience', 'connection_node']);
        });
    }
};
