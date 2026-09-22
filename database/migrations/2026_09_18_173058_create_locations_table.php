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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('hours')->nullable();
            $table->string('official_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('status', [
                'DRAFT',
                'SUBMITTED',
                'IN_REVIEW',
                'CHANGES_REQUESTED',
                'APPROVED',
                'PUBLISHED',
                'PAUSED',
                'EXPIRED',
                'REVOKED',
                'ARCHIVED'
            ])->default('DRAFT');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
