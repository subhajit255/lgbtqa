<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PauseNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_cannot_toggle_pause_notifications()
    {
        $response = $this->postJson('/api/user/pause-notifications', [
            'pause_notifications' => true,
        ]);

        $response->assertStatus(401);
    }

    public function test_validation_errors_for_missing_pause_notifications_field()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/user/pause-notifications', []);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
        ]);
    }

    public function test_validation_errors_for_non_boolean_pause_notifications_field()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/user/pause-notifications', [
            'pause_notifications' => 'not-a-boolean',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
        ]);
    }

    public function test_successful_pause_notifications_toggle()
    {
        $user = User::factory()->create([
            'pause_notifications' => false,
        ]);
        Passport::actingAs($user);

        // Turn ON pause notifications
        $response = $this->postJson('/api/user/pause-notifications', [
            'pause_notifications' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Notification pause status updated successfully.',
            'data' => [
                'pause_notifications' => true,
            ],
        ]);

        $user->refresh();
        $this->assertTrue($user->pause_notifications);

        // Turn OFF pause notifications
        $response = $this->postJson('/api/user/pause-notifications', [
            'pause_notifications' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Notification pause status updated successfully.',
            'data' => [
                'pause_notifications' => false,
            ],
        ]);

        $user->refresh();
        $this->assertFalse($user->pause_notifications);
    }

    public function test_pause_notifications_status_in_get_profile()
    {
        $user = User::factory()->create([
            'pause_notifications' => true,
        ]);
        Passport::actingAs($user);

        $response = $this->postJson('/api/get/profile');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Data Found',
        ]);

        // Check if pause_notifications is present and true in the data
        $this->assertTrue($response->json('data.pause_notifications'));

        // Toggle it off
        $user->pause_notifications = false;
        $user->save();

        $response = $this->postJson('/api/get/profile');
        $response->assertStatus(200);
        $this->assertFalse($response->json('data.pause_notifications'));
    }
}
