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
        Schema::table('users', function (Blueprint $table) {
            $table->string('apple_app_account_token')->nullable()->after('stripe_customer_id');
            $table->string('google_obfuscated_account_id')->nullable()->after('apple_app_account_token');
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->enum('store_type', ['stripe', 'apple', 'google'])->default('stripe')->after('plan_id');
            $table->string('original_transaction_id')->nullable()->after('stripe_subscription_id');
            $table->string('purchase_token')->nullable()->after('original_transaction_id');
            $table->boolean('is_acknowledged')->default(0)->after('purchase_token');
        });

        Schema::table('voluntary_supports', function (Blueprint $table) {
            $table->enum('store_type', ['stripe', 'apple', 'google'])->default('stripe')->after('type');
            $table->string('original_transaction_id')->nullable()->after('stripe_payment_intent_id');
            $table->string('purchase_token')->nullable()->after('original_transaction_id');
            $table->boolean('is_acknowledged')->default(0)->after('purchase_token');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('store_type', ['stripe', 'apple', 'google'])->default('stripe')->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['apple_app_account_token', 'google_obfuscated_account_id']);
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['store_type', 'original_transaction_id', 'purchase_token', 'is_acknowledged']);
        });

        Schema::table('voluntary_supports', function (Blueprint $table) {
            $table->dropColumn(['store_type', 'original_transaction_id', 'purchase_token', 'is_acknowledged']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('store_type');
        });
    }
};
