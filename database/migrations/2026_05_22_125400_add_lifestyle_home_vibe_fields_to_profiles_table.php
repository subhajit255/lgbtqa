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
            $table->tinyInteger('alcohol')->nullable()->after('dating_pace');
            $table->tinyInteger('smoking')->nullable()->after('alcohol');
            $table->tinyInteger('exercise')->nullable()->after('smoking');
            $table->tinyInteger('diet')->nullable()->after('exercise');
            $table->tinyInteger('sleep_rhythm')->nullable()->after('diet');
            $table->tinyInteger('kids_have')->nullable()->after('sleep_rhythm');
            $table->tinyInteger('kids_future')->nullable()->after('kids_have');
            $table->text('pets_current')->nullable()->after('kids_future');
            $table->tinyInteger('pets_future')->nullable()->after('pets_current');
            $table->tinyInteger('living_preference')->nullable()->after('pets_future');
            $table->tinyInteger('travel_importance')->nullable()->after('living_preference');
            $table->text('preferred_communication')->nullable()->after('travel_importance');
            $table->text('love_language')->nullable()->after('preferred_communication');
            $table->tinyInteger('social_energy')->nullable()->after('love_language');
            $table->tinyInteger('personality_type')->nullable()->after('social_energy');
            $table->tinyInteger('education')->nullable()->after('personality_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'alcohol',
                'smoking',
                'exercise',
                'diet',
                'sleep_rhythm',
                'kids_have',
                'kids_future',
                'pets_current',
                'pets_future',
                'living_preference',
                'travel_importance',
                'preferred_communication',
                'love_language',
                'social_energy',
                'personality_type',
                'education'
            ]);
        });
    }
};
