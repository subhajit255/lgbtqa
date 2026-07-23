<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('push_notification_queues', function (Blueprint $table) {
            $table->id();
            $table->text('fcm_token')->nullable();
            $table->string('title')->nullable();
            $table->longText('body')->nullable();
            $table->string('type')->nullable();
            $table->string('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notification_queues');
    }
};
