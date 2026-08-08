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
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('post_category_id')->nullable()->after('id')->constrained('post_categories');
            $table->enum('visibility', ["PUBLIC", "FRIENDS", "PRIVATE"])->default("PUBLIC")->after('description')->comment('PUBLIC, FRIENDS, PRIVATE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
