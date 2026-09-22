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
            $table->string('end_time')->nullable(); // Can be missing or all-day
            $table->boolean('is_all_day')->default(false);
            $table->string('time_zone')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('location_string')->nullable(); // fallback
            $table->string('host_name')->nullable();
            $table->string('host_image')->nullable();
            $table->string('host_type')->default('PARTNER');
            $table->string('host_pronouns')->nullable();
            $table->string('tags')->nullable();
            $table->string('audience')->nullable();
            
            // New fields for PM scope
            $table->enum('age_restriction', ['16-17', '18+', 'ALL'])->default('ALL');
            $table->string('official_ticket_url')->nullable();
            $table->string('source_attribution')->nullable(); // Eventfrog/gay.ch etc.
            $table->enum('admin_status', [
                'DRAFT', 'SUBMITTED', 'IN_REVIEW', 'CHANGES_REQUESTED', 
                'APPROVED', 'PUBLISHED', 'PAUSED', 'EXPIRED', 'REVOKED', 'ARCHIVED'
            ])->default('DRAFT');
            $table->tinyInteger('is_active')->default(1);
            
            $table->timestamps();

            // Foreign Key
            // $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');
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
