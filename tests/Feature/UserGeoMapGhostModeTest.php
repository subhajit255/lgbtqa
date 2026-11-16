<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserGeoMapGhostModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ghost_mode_toggles_and_persists_without_an_existing_location(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $enabledResponse = $this->postJson('/api/map/ghost-mode');

        $enabledResponse->assertOk();
        $enabledResponse->assertJsonPath('data.ghost_mode', true);
        $this->assertDatabaseHas('user_locations', [
            'user_id' => $user->id,
            'ghost_mode' => true,
        ]);
        $this->assertSame(1, UserLocation::where('user_id', $user->id)->count());

        $disabledResponse = $this->postJson('/api/map/ghost-mode');

        $disabledResponse->assertOk();
        $disabledResponse->assertJsonPath('data.ghost_mode', false);
        $this->assertDatabaseHas('user_locations', [
            'user_id' => $user->id,
            'ghost_mode' => false,
        ]);
        $this->assertSame(1, UserLocation::where('user_id', $user->id)->count());
    }

    public function test_ghost_mode_toggles_with_existing_location(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        UserLocation::create([
            'user_id' => $user->id,
            'lat' => 40.7128,
            'lng' => -74.0060,
            'city' => 'New York',
            'ghost_mode' => false,
            'approximate_region_radius_km' => 10,
        ]);

        $response = $this->postJson('/api/map/ghost-mode');

        $response->assertOk();
        $response->assertJsonPath('data.ghost_mode', true);
        $response->assertJsonPath('data.is_ghost_mode', true);
        $this->assertDatabaseHas('user_locations', [
            'user_id' => $user->id,
            'ghost_mode' => true,
        ]);

        $profileAfterEnable = $this->postJson('/api/get/profile');
        $profileAfterEnable->assertOk();
        $profileAfterEnable->assertJsonPath('data.is_ghost_mode', true);

        $explicitDisable = $this->postJson('/api/map/ghost-mode', ['is_ghost_mode' => false]);
        $explicitDisable->assertOk();
        $explicitDisable->assertJsonPath('data.ghost_mode', false);
        $explicitDisable->assertJsonPath('data.is_ghost_mode', false);

        $profileAfterDisable = $this->postJson('/api/get/profile');
        $profileAfterDisable->assertOk();
        $profileAfterDisable->assertJsonPath('data.is_ghost_mode', false);
    }

    public function test_ghost_mode_handles_missing_user_locations_columns_gracefully(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Drop the ghost_mode column from user_locations to simulate a server where migration has not run yet
        \Illuminate\Support\Facades\Schema::table('user_locations', function ($table) {
            $table->dropColumn(['ghost_mode', 'city', 'approximate_region_radius_km']);
        });

        $response = $this->postJson('/api/map/ghost-mode');

        // Must still succeed with 200 (not 500!)
        $response->assertOk();
        $response->assertJsonPath('data.ghost_mode', true);
        $response->assertJsonPath('data.is_ghost_mode', true);
    }

    public function test_location_ping_without_ghost_mode_preserves_active_ghost_mode(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Enable ghost mode
        $this->postJson('/api/map/ghost-mode', ['ghost_mode' => true])->assertOk();

        // Location ping without ghost_mode param
        $locResponse = $this->postJson('/api/map/location', [
            'latitude' => 28.6139,
            'longitude' => 77.2090,
        ]);
        $locResponse->assertOk();
        $locResponse->assertJsonPath('data.ghost_mode', true);

        // Profile should still report true
        $profile = $this->postJson('/api/get/profile');
        $profile->assertOk();
        $profile->assertJsonPath('data.is_ghost_mode', true);
    }

    public function test_ghost_mode_persists_when_only_approximate_region_radius_km_is_missing(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Drop only approximate_region_radius_km (simulating live server state)
        \Illuminate\Support\Facades\Schema::table('user_locations', function ($table) {
            $table->dropColumn('approximate_region_radius_km');
        });

        // 1. Enable ghost mode
        $enableResponse = $this->postJson('/api/map/ghost-mode', ['ghost_mode' => true]);
        $enableResponse->assertOk();
        $enableResponse->assertJsonPath('data.ghost_mode', true);
        $enableResponse->assertJsonPath('data.is_ghost_mode', true);

        // Verify it was actually written to user_locations table in DB!
        $this->assertDatabaseHas('user_locations', [
            'user_id' => $user->id,
            'ghost_mode' => true,
        ]);

        // Profile API must return is_ghost_mode: true
        $profileResponse = $this->postJson('/api/get/profile');
        $profileResponse->assertOk();
        $profileResponse->assertJsonPath('data.is_ghost_mode', true);

        // 2. Disable ghost mode
        $disableResponse = $this->postJson('/api/map/ghost-mode', ['ghost_mode' => false]);
        $disableResponse->assertOk();
        $disableResponse->assertJsonPath('data.ghost_mode', false);
        $disableResponse->assertJsonPath('data.is_ghost_mode', false);

        // Verify it was actually updated in user_locations table in DB!
        $this->assertDatabaseHas('user_locations', [
            'user_id' => $user->id,
            'ghost_mode' => false,
        ]);

        // Profile API must return is_ghost_mode: false
        $profileResponse2 = $this->postJson('/api/get/profile');
        $profileResponse2->assertOk();
        $profileResponse2->assertJsonPath('data.is_ghost_mode', false);
    }

    public function test_ghost_mode_updates_duplicate_user_locations_records(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Create duplicate rows for the same user
        UserLocation::create([
            'user_id' => $user->id,
            'lat' => 10.0,
            'lng' => 20.0,
            'ghost_mode' => false,
        ]);
        UserLocation::create([
            'user_id' => $user->id,
            'lat' => 11.0,
            'lng' => 21.0,
            'ghost_mode' => false,
        ]);

        // Enable ghost mode
        $response = $this->postJson('/api/map/ghost-mode', ['ghost_mode' => true]);
        $response->assertOk();

        // Every row for this user should now be ghost_mode = 1
        $rows = UserLocation::where('user_id', $user->id)->get();
        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertTrue((bool) $row->ghost_mode);
        }

        // Profile API should see true
        $profile = $this->postJson('/api/get/profile');
        $profile->assertOk();
        $profile->assertJsonPath('data.is_ghost_mode', true);
    }

    public function test_direct_ghost_mode_route_alias(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/ghost-mode', ['ghost_mode' => true]);
        $response->assertOk();
        $response->assertJsonPath('data.ghost_mode', true);
        $response->assertJsonPath('data.is_ghost_mode', true);
    }
}
