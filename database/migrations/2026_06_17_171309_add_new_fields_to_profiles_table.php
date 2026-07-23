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
            $table->string('nationality')->nullable()->after('what_i_am_looking_for');
            $table->string('coming_out_status')->nullable()->after('presentation_preference');
            $table->boolean('show_coming_out_status')->default(true)->after('coming_out_status');
            $table->text('religion')->nullable()->after('show_coming_out_status');
            $table->boolean('show_religion')->default(true)->after('religion');
            $table->text('political_views')->nullable()->after('show_religion');
            $table->boolean('show_political_views')->default(true)->after('political_views');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'nationality',
                'coming_out_status',
                'show_coming_out_status',
                'religion',
                'show_religion',
                'political_views',
                'show_political_views',
            ]);
        });
    }
};
