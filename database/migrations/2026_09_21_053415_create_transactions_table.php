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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_id')->nullable();
            $table->string('payment_type'); // e.g., 'stripe', 'paypal', 'one_time', 'subscription'
            $table->string('payment_method_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('payment_status')->default('pending');
            $table->string('idempotency_key')->nullable()->unique();
            $table->text('payment_details')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
