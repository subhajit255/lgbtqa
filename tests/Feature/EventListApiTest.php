<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class EventListApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_event_list_filters_by_is_interested()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $eventInterested = Event::create([
            'title' => 'Interested Event Test',
            'description' => 'Event description',
            'event_date' => '2026-08-01',
            'start_time' => '10:00 AM',
            'end_time' => '12:00 PM',
            'location' => 'Location A',
            'host_name' => 'Host A',
            'is_active' => 1,
        ]);

        $eventNotInterested = Event::create([
            'title' => 'Other Event Test',
            'description' => 'Event description 2',
            'event_date' => '2026-08-02',
            'start_time' => '10:00 AM',
            'end_time' => '12:00 PM',
            'location' => 'Location B',
            'host_name' => 'Host B',
            'is_active' => 1,
        ]);

        EventParticipant::create([
            'event_id' => $eventInterested->id,
            'user_id' => $user->id,
            'status' => 'interested',
        ]);

        // Filter is_interested=1
        $response = $this->getJson('/api/events?is_interested=1');
        $response->assertStatus(200);
        $data = $response->json('data');
        
        $titles = collect($data)->pluck('title');
        $this->assertTrue($titles->contains('Interested Event Test'));
        $this->assertFalse($titles->contains('Other Event Test'));

        // Filter is_interested=0
        $responseZero = $this->getJson('/api/events?is_interested=0');
        $responseZero->assertStatus(200);
        $dataZero = $responseZero->json('data');
        
        $titlesZero = collect($dataZero)->pluck('title');
        $this->assertFalse($titlesZero->contains('Interested Event Test'));
        $this->assertTrue($titlesZero->contains('Other Event Test'));
    }

    public function test_event_list_filters_by_is_joined()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $eventJoined = Event::create([
            'title' => 'Joined Event Test',
            'description' => 'Event description',
            'event_date' => '2026-08-01',
            'start_time' => '10:00 AM',
            'end_time' => '12:00 PM',
            'location' => 'Location A',
            'host_name' => 'Host A',
            'is_active' => 1,
        ]);

        $eventNotJoined = Event::create([
            'title' => 'Not Joined Event Test',
            'description' => 'Event description 2',
            'event_date' => '2026-08-02',
            'start_time' => '10:00 AM',
            'end_time' => '12:00 PM',
            'location' => 'Location B',
            'host_name' => 'Host B',
            'is_active' => 1,
        ]);

        EventParticipant::create([
            'event_id' => $eventJoined->id,
            'user_id' => $user->id,
            'status' => 'joined',
        ]);

        // Filter is_joined=1
        $response = $this->getJson('/api/events?is_joined=1');
        $response->assertStatus(200);
        $data = $response->json('data');
        
        $titles = collect($data)->pluck('title');
        $this->assertTrue($titles->contains('Joined Event Test'));
        $this->assertFalse($titles->contains('Not Joined Event Test'));

        // Filter is_joined=0
        $responseZero = $this->getJson('/api/events?is_joined=0');
        $responseZero->assertStatus(200);
        $dataZero = $responseZero->json('data');
        
        $titlesZero = collect($dataZero)->pluck('title');
        $this->assertFalse($titlesZero->contains('Joined Event Test'));
        $this->assertTrue($titlesZero->contains('Not Joined Event Test'));
    }
}
