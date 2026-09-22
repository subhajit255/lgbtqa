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
        Schema::create('voluntary_supports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['ONE_TIME', 'MONTHLY']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('CHF');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->enum('status', ['PENDING', 'ACTIVE', 'CANCELLED', 'EXPIRED', 'REFUNDED'])->default('PENDING');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voluntary_supports');
    }
};
