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
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->boolean('agreed_to_terms')->default(false)->after('no_one_at_all');
            $table->tinyInteger('what_i_am_looking_for')->nullable()->after('relationship_status');
            $table->string('nationality')->nullable()->after('what_i_am_looking_for');
            $table->string('living_in_country')->nullable()->after('nationality');
            $table->string('living_in_city')->nullable()->after('living_in_country');
            $table->boolean('show_location_on_profile')->default(true)->after('living_in_city');
            $table->boolean('currently_traveling')->default(false)->after('show_location_on_profile');
            $table->text('languages_spoken')->nullable()->after('currently_traveling'); // Stores JSON or comma-separated list
            $table->text('languages_learning')->nullable()->after('languages_spoken'); // Stores JSON or comma-separated list
            $table->integer('height')->nullable()->after('languages_learning');
            $table->tinyInteger('body_type')->nullable()->after('height');
            $table->tinyInteger('eye_color')->nullable()->after('body_type');
            $table->tinyInteger('hair_color')->nullable()->after('eye_color');
            $table->boolean('confirm_18_plus')->default(false)->after('hair_color');
            $table->tinyInteger('sex_importance')->nullable()->after('confirm_18_plus');
            $table->tinyInteger('role_position')->nullable()->after('sex_importance');
            $table->tinyInteger('dating_pace')->nullable()->after('role_position');
            $table->tinyInteger('presentation_preference')->nullable()->after('dating_pace');
            $table->boolean('private_album')->default(false)->after('presentation_preference');
            $table->integer('age_range_min')->nullable()->after('age_range');
            $table->integer('age_range_max')->nullable()->after('age_range_min');
            
            // Modify dating_preferences to be text so it can store JSON / comma-separated arrays of gender preferences
            $table->text('dating_preferences')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'agreed_to_terms',
                'what_i_am_looking_for',
                'nationality',
                'living_in_country',
                'living_in_city',
                'show_location_on_profile',
                'currently_traveling',
                'languages_spoken',
                'languages_learning',
                'height',
                'body_type',
                'eye_color',
                'hair_color',
                'confirm_18_plus',
                'sex_importance',
                'role_position',
                'dating_pace',
                'presentation_preference',
                'private_album',
                'age_range_min',
                'age_range_max'
            ]);

            // Revert dating_preferences to tinyInteger
            $table->tinyInteger('dating_preferences')->nullable()->change();
        });
    }
};
