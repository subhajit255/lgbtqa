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
        Schema::table('user_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('user_locations', 'city')) {
                $table->string('city')->nullable()->after('lng');
            }
            if (!Schema::hasColumn('user_locations', 'ghost_mode')) {
                $table->boolean('ghost_mode')->default(false)->after('city');
            }
            if (!Schema::hasColumn('user_locations', 'approximate_region_radius_km')) {
                $table->unsignedTinyInteger('approximate_region_radius_km')->default(10)->after('ghost_mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            if (Schema::hasColumn('user_locations', 'approximate_region_radius_km')) {
                $table->dropColumn('approximate_region_radius_km');
            }
        });
    }
};
