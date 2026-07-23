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
        Schema::create('app_setting_toggles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Safety & Security
            $table->boolean('stealth_mode')->default(false);
            $table->boolean('ghost_mode')->default(false);
            $table->boolean('two_factor_auth')->default(false);
            $table->boolean('biometric_login')->default(false);
            $table->boolean('login_alerts')->default(false);
            
            // Discovery/Location
            $table->boolean('show_in_discovery')->default(true);
            $table->boolean('location_based')->default(false);
            $table->boolean('match_by_interests')->default(true);
            $table->boolean('pride_events_nearby')->default(true);
            
            // Who can message
            $table->boolean('message_friends_only')->default(true);
            $table->boolean('message_community')->default(true);
            $table->boolean('message_open')->default(true);
            
            // Notifications
            $table->boolean('notify_new_message')->default(true);
            $table->boolean('notify_event_reminder')->default(true);
            $table->boolean('notify_friend_requests')->default(true);
            $table->boolean('notify_post_interactions')->default(true);
            $table->boolean('notify_mentions_tags')->default(true);
            $table->boolean('notify_profile_visits')->default(true);
            $table->boolean('notify_marketing_updates')->default(false);
            $table->boolean('push_notification')->default(true);
            $table->boolean('email_notification')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_setting_toggles');
    }
};
