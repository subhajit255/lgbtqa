<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserOnlineStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_is_marked_online_and_last_seen_updated_when_making_authenticated_request()
    {
        $user = User::factory()->create();

        // Initially, user is not online and last_seen_at is null
        $this->assertFalse($user->is_online);
        $this->assertNull($user->last_seen_at);

        // Act as user and make an authenticated API request
        Passport::actingAs($user);
        $response = $this->postJson('/api/get/profile');

        $response->assertStatus(200);

        // Fetch fresh user instance
        $freshUser = User::find($user->id);

        // User should now be marked online and last_seen_at should be recorded
        $this->assertTrue($freshUser->is_online);
        $this->assertNotNull($freshUser->last_seen_at);

        // Verify JSON response includes is_online and last_seen_at
        $response->assertJsonPath('data.is_online', true);
        $response->assertJsonPath('data.last_seen_at', $freshUser->last_seen_at);
    }

    public function test_user_becomes_offline_when_cache_key_is_cleared()
    {
        $user = User::factory()->create();

        // Mark user online in cache
        Cache::put('user-is-online-' . $user->id, true, now()->addMinutes(2));
        Cache::put('user-last-seen-' . $user->id, now()->toDateTimeString(), now()->addDays(30));

        $this->assertTrue($user->is_online);

        // Forget cache key to simulate status expiry
        Cache::forget('user-is-online-' . $user->id);

        $this->assertFalse($user->is_online);
        // last_seen_at remains cached for historical reference
        $this->assertNotNull($user->last_seen_at);
    }
}
