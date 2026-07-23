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
        Schema::table('post_emojis', function (Blueprint $table) {
            $table->index(['post_id', 'emoji']);
        });

        Schema::table('post_comments', function (Blueprint $table) {
            $table->index(['post_id', 'parent_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_emojis', function (Blueprint $table) {
            $table->dropIndex(['post_id', 'emoji']);
        });

        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropIndex(['post_id', 'parent_id', 'created_at']);
        });
    }
};
