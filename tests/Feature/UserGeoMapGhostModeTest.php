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
}
