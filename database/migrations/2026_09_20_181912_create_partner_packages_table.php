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
        Schema::create('partner_packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->enum('type', ['VENUE_ORGANISER', 'SPONSOR']);
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('CHF');
            $table->enum('billing_cycle', ['MONTHLY', 'YEARLY']);
            $table->json('features')->nullable();
            $table->string('subtitle')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_packages');
    }
};
