<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Webpatser\Uuid\Uuid;

class EventFullApiSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
        Storage::fake('public');
    }

    public function test_get_events_list()
    {
        Event::create([
            'title' => 'Pride Festival',
            'description' => 'Annual pride celebration',
            'event_date' => now()->addDays(5)->toDateString(),
            'start_time' => '10:00 AM',
            'end_time' => '06:00 PM',
            'location_string' => 'Zurich Central',
            'host_name' => 'Pride Org',
            'is_active' => 1,
            'is_all_day' => 0,
        ]);

        $response = $this->getJson('/api/events');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Events retrieved successfully'
        ]);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_get_event_details()
    {
        $event = Event::create([
            'title' => 'Community Gathering',
            'description' => 'Social meetup',
            'event_date' => now()->addDays(3)->toDateString(),
            'start_time' => '07:00 PM',
            'end_time' => '10:00 PM',
            'location_string' => 'Geneva Hall',
            'host_name' => 'Host User',
            'is_active' => 1,
        ]);

        $response = $this->getJson("/api/events/{$event->uuid}");
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'data' => [
                'uuid' => $event->uuid,
                'title' => 'Community Gathering',
            ]
        ]);
    }

    public function test_create_event_via_api()
    {
        $file = UploadedFile::fake()->image('banner.jpg');

        $payload = [
            'title' => 'New Year Gala',
            'description' => 'Gala party celebration',
            'event_date' => now()->addDays(20)->toDateString(),
            'start_time' => '08:00 PM',
            'end_time' => '02:00 AM',
            'location_string' => 'Basel Center',
            'is_all_day' => 0,
            'host_name' => 'Gala Committee',
            'host_type' => 'PARTNER',
            'audience' => 'LGBTQA',
            'age_restriction' => '18+',
            'file' => $file,
        ];

        $response = $this->postJson('/api/events/create', $payload);
        $response->assertStatus(201);
        $response->assertJson([
            'status' => true,
            'message' => 'Event created successfully',
        ]);

        $this->assertDatabaseHas('events', [
            'title' => 'New Year Gala',
            'location_string' => 'Basel Center',
        ]);
    }

    public function test_create_all_day_event_with_linked_location()
    {
        $file = UploadedFile::fake()->image('banner.jpg');
        $location = Location::create([
            'uuid' => (string) Uuid::generate(4),
            'name' => 'Rainbow Cafe',
            'address' => 'Bahnhofstrasse 10',
            'lat' => 47.3769,
            'lng' => 8.5417,
            'is_active' => 1,
            'status' => 'PUBLISHED',
        ]);

        $payload = [
            'title' => 'Full Day Rainbow Meet',
            'description' => 'Full day event',
            'event_date' => now()->addDays(10)->toDateString(),
            'location_id' => $location->id,
            'is_all_day' => 1,
            'host_name' => 'Cafe Host',
            'file' => $file,
        ];

        $response = $this->postJson('/api/events/create', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('events', [
            'title' => 'Full Day Rainbow Meet',
            'location_id' => $location->id,
            'is_all_day' => 1,
        ]);
    }

    public function test_update_event_via_api()
    {
        $event = Event::create([
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'event_date' => now()->addDays(5)->toDateString(),
            'start_time' => '06:00 PM',
            'location_string' => 'Initial Venue',
            'host_name' => 'Initial Host',
            'is_active' => 1,
        ]);

        $updatePayload = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'event_date' => now()->addDays(6)->toDateString(),
            'start_time' => '07:00 PM',
            'location_string' => 'Updated Venue',
            'host_name' => 'Updated Host',
        ];

        $response = $this->postJson("/api/events/update/{$event->uuid}", $updatePayload);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Event updated successfully'
        ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Title',
            'location_string' => 'Updated Venue',
        ]);
    }

    public function test_toggle_join_event()
    {
        $event = Event::create([
            'title' => 'Join Test Event',
            'event_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00 AM',
            'location_string' => 'Venue',
            'host_name' => 'Host',
            'is_active' => 1,
        ]);

        // Join
        $resJoin = $this->postJson("/api/events/{$event->uuid}/join");
        $resJoin->assertStatus(200);
        $resJoin->assertJson(['status' => true, 'joined' => true]);

        // Leave
        $resLeave = $this->postJson("/api/events/{$event->uuid}/join");
        $resLeave->assertStatus(200);
        $resLeave->assertJson(['status' => true, 'joined' => false]);
    }

    public function test_toggle_interest_event()
    {
        $event = Event::create([
            'title' => 'Interest Test Event',
            'event_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00 AM',
            'location_string' => 'Venue',
            'host_name' => 'Host',
            'is_active' => 1,
        ]);

        $resInterest = $this->postJson("/api/events/{$event->uuid}/interest");
        $resInterest->assertStatus(200);
        $resInterest->assertJson(['status' => true, 'interested' => true]);

        $resUninterest = $this->postJson("/api/events/{$event->uuid}/interest");
        $resUninterest->assertStatus(200);
        $resUninterest->assertJson(['status' => true, 'interested' => false]);
    }

    public function test_toggle_save_event()
    {
        $event = Event::create([
            'title' => 'Save Test Event',
            'event_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00 AM',
            'location_string' => 'Venue',
            'host_name' => 'Host',
            'is_active' => 1,
        ]);

        $resSave = $this->postJson("/api/events/{$event->uuid}/save");
        $resSave->assertStatus(200);
        $resSave->assertJson(['status' => true, 'saved' => true]);

        $resUnsave = $this->postJson("/api/events/{$event->uuid}/save");
        $resUnsave->assertStatus(200);
        $resUnsave->assertJson(['status' => true, 'saved' => false]);
    }

    public function test_share_event()
    {
        $event = Event::create([
            'title' => 'Share Test Event',
            'event_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00 AM',
            'location_string' => 'Venue',
            'host_name' => 'Host',
            'is_active' => 1,
        ]);

        $response = $this->postJson("/api/events/{$event->uuid}/share");
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Event shared successfully',
        ]);
        $this->assertNotEmpty($response->json('share_url'));
    }

    public function test_report_event()
    {
        $event = Event::create([
            'title' => 'Report Test Event',
            'event_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00 AM',
            'location_string' => 'Venue',
            'host_name' => 'Host',
            'is_active' => 1,
        ]);

        $response = $this->postJson("/api/events/{$event->uuid}/report", [
            'reason' => 'Inappropriate content',
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Report received successfully',
        ]);

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $this->user->id,
            'reportable_type' => Event::class,
            'reportable_id' => $event->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_delete_event()
    {
        $event = Event::create([
            'title' => 'To Be Deleted',
            'event_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00 AM',
            'location_string' => 'Venue',
            'host_name' => 'Host',
            'is_active' => 1,
        ]);

        $response = $this->deleteJson("/api/events/delete/{$event->uuid}");
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Event deleted successfully'
        ]);

        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }
}
