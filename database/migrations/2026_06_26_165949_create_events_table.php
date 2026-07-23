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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('about')->nullable();
            $table->string('image')->nullable();
            $table->date('event_date');
            $table->string('start_time');
            $table->string('end_time');
            $table->string('location');
            $table->string('host_name');
            $table->string('host_image')->nullable();
            $table->string('host_type')->default('PARTNER');
            $table->string('host_pronouns')->nullable();
            $table->string('tags')->nullable();
            $table->string('audience')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
