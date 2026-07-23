<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Community;
use App\Models\Event;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Laravel\Passport\Passport;

class GlobalSearchTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_cannot_search()
    {
        $response = $this->getJson('/api/search');
        $response->assertStatus(401);
    }

    public function test_invalid_type_validation_fails()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->getJson('/api/search?type=invalid');
        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
        ]);
    }

    public function test_search_returns_all_types_when_type_is_null()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Create a matching user
        $otherUser = User::factory()->create([
            'name' => 'Searchable User',
            'username' => 'searchable_user',
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        // Create a matching community
        $community = Community::create([
            'name' => 'LGBTQIA Community Club',
            'description' => 'A great place for pride.',
            'creator_id' => $user->id,
            'is_active' => 1,
        ]);

        // Create a matching event
        $event = Event::create([
            'title' => 'Pride Gala Event',
            'description' => 'An annual celebration.',
            'event_date' => '2026-07-01',
            'start_time' => '18:00',
            'end_time' => '22:00',
            'location' => 'Main Hall',
            'host_name' => 'Pride Host',
            'is_active' => 1,
        ]);

        // Create a matching post
        $post = Post::create([
            'title' => 'Amazing Pride Post',
            'description' => 'Check out the details.',
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/search?search=Pride');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Search results retrieved successfully',
        ]);

        // The response data should have keys for all categories
        $response->assertJsonStructure([
            'data' => [
                'people',
                'communities',
                'events',
                'posts',
            ],
        ]);

        // Check content presence in appropriate sections
        $data = $response->json('data');
        
        $this->assertTrue(collect($data['communities'])->contains('name', 'LGBTQIA Community Club'));
        $this->assertTrue(collect($data['events'])->contains('title', 'Pride Gala Event'));
        $this->assertTrue(collect($data['posts'])->contains('title', 'Amazing Pride Post'));
    }

    public function test_search_returns_only_requested_type()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Create community
        $community = Community::create([
            'name' => 'LGBTQIA Community Club',
            'description' => 'A great place for pride.',
            'creator_id' => $user->id,
            'is_active' => 1,
        ]);

        // Create event
        $event = Event::create([
            'title' => 'Pride Gala Event',
            'description' => 'An annual celebration.',
            'event_date' => '2026-07-01',
            'start_time' => '18:00',
            'end_time' => '22:00',
            'location' => 'Main Hall',
            'host_name' => 'Pride Host',
            'is_active' => 1,
        ]);

        // Request type=community
        $response = $this->getJson('/api/search?search=Pride&type=community');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'communities',
            ],
        ]);

        // The keys for other types should not be in the response
        $this->assertNull($response->json('data.people'));
        $this->assertNull($response->json('data.events'));
        $this->assertNull($response->json('data.posts'));

        $this->assertTrue(collect($response->json('data.communities'))->contains('name', 'LGBTQIA Community Club'));
    }
}
