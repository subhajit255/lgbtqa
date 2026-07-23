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
        Schema::table('chats', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('image')->nullable()->after('description');
            $table->boolean('is_public')->default(false)->after('image');
            $table->boolean('is_locked')->default(false)->after('is_public');
            $table->string('tags')->nullable()->after('is_locked');
            $table->integer('member_limit')->default(100)->after('tags');
        });

        Schema::table('chat_participants', function (Blueprint $table) {
            $table->string('role')->default('member')->after('user_id'); // member, admin
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['description', 'image', 'is_public', 'is_locked', 'tags', 'member_limit']);
        });

        Schema::table('chat_participants', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
