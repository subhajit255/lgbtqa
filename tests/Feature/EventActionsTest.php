<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\EventReminder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Webpatser\Uuid\Uuid;

class EventActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_event_reminder()
    {
        $user = User::factory()->create();
        $eventUuid = (string) Str::uuid();
        $eventId = DB::table('events')->insertGetId([
            'uuid' => $eventUuid,
            'title' => 'Test Event',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user, 'api')->postJson("/api/events/{$eventUuid}/remind", [
            'remind_at' => now()->addDay()->format('Y-m-d H:i:s')
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Reminder set successfully.'
        ]);

        $this->assertDatabaseHas('event_reminders', [
            'user_id' => $user->id,
            'event_id' => $eventId
        ]);
    }

    public function test_user_can_remove_event_reminder()
    {
        $user = User::factory()->create();
        $eventUuid = (string) Str::uuid();
        $eventId = DB::table('events')->insertGetId([
            'uuid' => $eventUuid,
            'title' => 'Test Event',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        EventReminder::create([
            'user_id' => $user->id,
            'event_id' => $eventId,
            'remind_at' => now()->addDay()
        ]);

        $response = $this->actingAs($user, 'api')->postJson("/api/events/{$eventUuid}/remove-reminder");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Reminder removed successfully.'
        ]);

        $this->assertDatabaseMissing('event_reminders', [
            'user_id' => $user->id,
            'event_id' => $eventId
        ]);
    }

    public function test_user_can_save_event()
    {
        $user = User::factory()->create();
        $eventUuid = (string) Str::uuid();
        $eventId = DB::table('events')->insertGetId([
            'uuid' => $eventUuid,
            'title' => 'Test Event',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user, 'api')->postJson("/api/events/{$eventUuid}/save");

        $response->assertStatus(200);
    }
}
