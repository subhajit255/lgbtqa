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
}
