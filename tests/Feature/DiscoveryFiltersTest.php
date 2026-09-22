<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DiscoveryFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_cannot_access_advanced_search()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/subscription/advanced-search', [
            'sleep_rhythm' => 'early_bird'
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status' => false,
            'message' => 'Premium subscription required to use advanced filters.'
        ]);
    }

    public function test_premium_user_can_access_advanced_search_and_get_results()
    {
        $premiumUser = User::factory()->create();
        DB::table('user_subscriptions')->insert([
            'user_id' => $premiumUser->id,
            'plan_id' => 1,
            'status' => 'ACTIVE',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $targetUser = User::factory()->create(['user_type' => 3, 'is_active' => 1, 'is_blocked' => 0]);
        Profile::factory()->create([
            'user_id' => $targetUser->id,
            'sleep_rhythm' => 'early_bird',
        ]);

        $response = $this->actingAs($premiumUser, 'api')->postJson('/api/subscription/advanced-search', [
            'sleep_rhythm' => 'early_bird'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'users',
                'pagination'
            ]
        ]);
    }
}
