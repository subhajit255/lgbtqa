<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup Passport personal client for testing token creation
        $this->artisan('passport:client', [
            '--personal' => true,
            '--name' => 'Personal Access Client',
            '--no-interaction' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_notifications()
    {
        $response = $this->getJson('/api/notifications');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_notifications_desc()
    {
        $user = User::factory()->create();

        // Create notifications out of order
        $notif1 = Notification::create([
            'user_id' => $user->id,
            'title' => 'First Notification',
            'description' => 'Test',
            'type' => 'test',
            'for' => 2,
            'is_read' => 0,
            'is_active' => 1,
            'created_at' => now()->subMinutes(10),
        ]);

        $notif2 = Notification::create([
            'user_id' => $user->id,
            'title' => 'Second Notification',
            'description' => 'Test',
            'type' => 'test',
            'for' => 2,
            'is_read' => 0,
            'is_active' => 1,
            'created_at' => now(),
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Notifications retrieved successfully',
        ]);

        // Second notification (newer) should come first in desc order
        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertEquals($notif2->id, $data[0]['id']);
        $this->assertEquals($notif1->id, $data[1]['id']);
    }

    public function test_can_filter_notifications_by_read_unread()
    {
        $user = User::factory()->create();

        $unreadNotif = Notification::create([
            'user_id' => $user->id,
            'title' => 'Unread',
            'is_read' => 0,
            'is_active' => 1,
        ]);

        $readNotif = Notification::create([
            'user_id' => $user->id,
            'title' => 'Read',
            'is_read' => 1,
            'is_active' => 1,
        ]);

        Passport::actingAs($user);

        // Fetch unread
        $response = $this->getJson('/api/notifications?status=unread');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($unreadNotif->id, $response->json('data.0.id'));

        // Fetch read
        $response = $this->getJson('/api/notifications?status=read');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($readNotif->id, $response->json('data.0.id'));

        // Fetch all
        $response = $this->getJson('/api/notifications?status=all');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_mark_notification_as_read()
    {
        $user = User::factory()->create();

        $notif = Notification::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'is_read' => 0,
            'is_active' => 1,
        ]);

        Passport::actingAs($user);

        $response = $this->postJson("/api/notifications/{$notif->id}/read");

        $response->assertStatus(200);
        $this->assertEquals(1, $notif->fresh()->is_read);
    }

    public function test_can_mark_all_notifications_as_read()
    {
        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Test 1',
            'is_read' => 0,
            'is_active' => 1,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Test 2',
            'is_read' => 0,
            'is_active' => 1,
        ]);

        Passport::actingAs($user);

        $response = $this->postJson('/api/notifications/read-all');

        $response->assertStatus(200);
        $this->assertEquals(0, Notification::where('user_id', $user->id)->where('is_read', 0)->count());
    }

    public function test_login_sends_notification()
    {
        $user = User::factory()->create([
            'email' => 'login_test@example.com',
            'password' => bcrypt('password123'),
            'is_active' => 1,
            'is_approve' => 1,
            'is_verified_email' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login_test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'login',
        ]);
    }

    public function test_signup_sends_notification()
    {
        $response = $this->postJson('/api/signup', [
            'name' => 'John Signup',
            'email' => 'signup_test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(200);

        $user = User::where('email', 'signup_test@example.com')->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'signup',
        ]);
    }

    public function test_post_creation_sends_notification()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/posts/create', [
            'title' => 'Post Test Notification',
            'description' => 'Test Description',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'post_create',
        ]);
    }
}
