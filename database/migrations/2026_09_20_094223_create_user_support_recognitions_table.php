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
        Schema::create('user_support_recognitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('visibility_mode', ['PRIVATE', 'SHOW_MY_PROFILE', 'SHOW_ANONYMOUSLY'])->default('PRIVATE');
            $table->boolean('show_amount')->default(false);
            $table->boolean('show_badge')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_support_recognitions');
    }
};
