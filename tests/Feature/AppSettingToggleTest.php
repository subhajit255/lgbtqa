<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AppSettingToggle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Laravel\Passport\Passport;

class AppSettingToggleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_cannot_access_settings()
    {
        $response = $this->getJson('/api/app-setting-toggle');
        $response->assertStatus(401);

        $response = $this->postJson('/api/app-setting-toggle', ['stealth_mode' => true]);
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_default_settings()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->getJson('/api/app-setting-toggle');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'App settings fetched successfully.',
        ]);

        $response->assertJsonFragment([
            'stealth_mode' => false,
            'ghost_mode' => false,
            'two_factor_auth' => false,
            'biometric_login' => false,
            'login_alerts' => false,
            'show_in_discovery' => true,
            'location_based' => false,
            'match_by_interests' => true,
            'pride_events_nearby' => true,
            'audience' => 'open',
            'connection_node' => 'open',
            'distance_range' => 1,
            'send_email_when' => 'after_1_hours_offline',
            'message_friends_only' => true,
            'message_community' => true,
            'message_open' => true,
            'notify_new_message' => true,
            'notify_event_reminder' => true,
            'notify_friend_requests' => true,
            'notify_post_interactions' => true,
            'notify_mentions_tags' => true,
            'notify_profile_visits' => true,
            'notify_marketing_updates' => false,
            'push_notification' => true,
            'email_notification' => true,
        ]);

        $this->assertDatabaseHas('app_setting_toggles', [
            'user_id' => $user->id,
            'stealth_mode' => false,
            'audience' => 'open',
            'connection_node' => 'open',
            'send_email_when' => 'after_1_hours_offline',
        ]);
    }

    public function test_authenticated_user_can_update_settings()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // First request to save/update subset of fields
        $response = $this->postJson('/api/app-setting-toggle', [
            'stealth_mode' => true,
            'show_in_discovery' => false,
            'notify_marketing_updates' => true,
            'audience' => 'friends_only',
            'connection_node' => 'community',
            'distance_range' => 25,
            'send_email_when' => 'daily_digest',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'App settings saved successfully.',
        ]);

        $response->assertJsonFragment([
            'stealth_mode' => true,
            'show_in_discovery' => false,
            'notify_marketing_updates' => true,
            'ghost_mode' => false, // unchanged default
            'audience' => 'friends_only',
            'connection_node' => 'community',
            'distance_range' => 25,
            'send_email_when' => 'daily_digest',
        ]);

        $this->assertDatabaseHas('app_setting_toggles', [
            'user_id' => $user->id,
            'stealth_mode' => true,
            'show_in_discovery' => false,
            'notify_marketing_updates' => true,
            'ghost_mode' => false,
            'audience' => 'friends_only',
            'connection_node' => 'community',
            'send_email_when' => 'daily_digest',
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'distance_range' => 25,
        ]);
    }

    public function test_validation_fails_for_non_boolean_values()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/app-setting-toggle', [
            'stealth_mode' => 'not-a-boolean-string-invalid',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
        ]);
    }

    public function test_validation_fails_for_invalid_audience_value()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/app-setting-toggle', [
            'audience' => 'invalid-audience-value',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
        ]);
    }

    public function test_validation_fails_for_invalid_send_email_when_value()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/app-setting-toggle', [
            'send_email_when' => 'invalid-send-email-when-value',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
        ]);
    }
}
