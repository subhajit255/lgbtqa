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
        Schema::table('plans', function (Blueprint $table) {
            $table->string('apple_product_id')->nullable()->after('billing_cycle');
            $table->string('google_product_id')->nullable()->after('apple_product_id');
        });

        Schema::table('support_options', function (Blueprint $table) {
            $table->string('apple_product_id')->nullable()->after('type');
            $table->string('google_product_id')->nullable()->after('apple_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['apple_product_id', 'google_product_id']);
        });

        Schema::table('support_options', function (Blueprint $table) {
            $table->dropColumn(['apple_product_id', 'google_product_id']);
        });
    }
};
