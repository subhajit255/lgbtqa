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
        Schema::create('visitor_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('visited_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('visitor_id');
            $table->index('visited_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_histories');
    }
};
