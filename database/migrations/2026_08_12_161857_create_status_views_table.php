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
        Schema::create('status_views', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('status_id')->nullable()->comment('story owner')->constrained('statuses')->cascadeOnDelete();
            $table->foreignId('viewer_id')->nullable()->comment('viewer')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_replied')->default(false)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_views');
    }
};
