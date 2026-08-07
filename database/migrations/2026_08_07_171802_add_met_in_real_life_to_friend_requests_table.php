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
        Schema::table('friend_requests', function (Blueprint $table) {
            $table->boolean('met_in_real_life')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('friend_requests', function (Blueprint $table) {
            $table->dropColumn('met_in_real_life');
        });
    }
};
