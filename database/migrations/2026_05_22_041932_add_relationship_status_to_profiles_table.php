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
            $table->tinyInteger('relationship_status')->comment('1:Single, 2:In a relationship, 3:Married, 4:Divorced, 5:Widowed, 6:Separated, 7:Its complicated, 8:Open relationship')->nullable()->after('dating_preferences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('relationship_status');
        });
    }
};
