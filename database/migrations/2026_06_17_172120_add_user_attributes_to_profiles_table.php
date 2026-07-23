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
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('occupation')->nullable()->after('music_tests');
            $table->string('weight')->nullable()->after('occupation');
            $table->string('zodiac')->nullable()->after('weight');
            $table->string('location')->nullable()->after('zodiac');
            $table->text('languages_written')->nullable()->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'occupation',
                'weight',
                'zodiac',
                'location',
                'languages_written',
            ]);
        });
    }
};
