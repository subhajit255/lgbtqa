<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisitorHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileVisitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_visit_profile_and_log_visit()
    {
        $visitor = User::factory()->create();
        $visited = User::factory()->create();

        $response = $this->actingAs($visitor, 'api')->postJson('/api/profile/visit', [
            'visited_id' => $visited->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Visit logged successfully'
        ]);

        $this->assertDatabaseHas('visitor_histories', [
            'visitor_id' => $visitor->id,
            'visited_id' => $visited->id
        ]);
    }

    public function test_user_can_toggle_hide_my_visits()
    {
        $user = User::factory()->create(['hide_my_visits' => false]);

        $response = $this->actingAs($user, 'api')->postJson('/api/toggle-hide-visits', [
            'hide_my_visits' => 1
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'hide_my_visits' => true
        ]);
    }



    public function test_user_can_get_visitors()
    {
        $visited = User::factory()->create(['hide_my_visits' => 1]);
        $visitor1 = User::factory()->create(['hide_my_visits' => 1]);
        $visitor2 = User::factory()->create(['hide_my_visits' => 1]);

        VisitorHistory::create([
            'visitor_id' => $visitor1->id,
            'visited_id' => $visited->id
        ]);

        VisitorHistory::create([
            'visitor_id' => $visitor2->id,
            'visited_id' => $visited->id
        ]);

        $response = $this->actingAs($visited, 'api')->getJson('/api/profile/visitors');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'data'
            ]
        ]);
    }
}
