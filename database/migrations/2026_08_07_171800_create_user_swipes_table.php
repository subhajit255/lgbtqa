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
        Schema::create('user_swipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('target_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['like', 'pass', 'super_like']);
            $table->boolean('is_match')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'target_user_id']); // Ensure a user only swipes another user once
            $table->index('user_id');
            $table->index('target_user_id');
            $table->index('is_match');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_swipes');
    }
};
