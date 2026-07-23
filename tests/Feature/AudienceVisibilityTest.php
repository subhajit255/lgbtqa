<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AudienceVisibility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Laravel\Passport\Passport;

class AudienceVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_cannot_access_audience_visibility()
    {
        $response = $this->getJson('/api/audience-visibility');
        $response->assertStatus(401);

        $response = $this->postJson('/api/audience-visibility', ['visibility' => 'friends_only']);
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_default_visibility()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->getJson('/api/audience-visibility');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Audience visibility fetched successfully.',
        ]);

        $response->assertJsonFragment([
            'visibility' => 'open',
        ]);

        $this->assertDatabaseHas('audience_visibilities', [
            'user_id' => $user->id,
            'visibility' => 'open',
        ]);
    }

    public function test_authenticated_user_can_update_visibility()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Update to community
        $response = $this->postJson('/api/audience-visibility', [
            'visibility' => 'community',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Audience visibility saved successfully.',
        ]);

        $response->assertJsonFragment([
            'visibility' => 'community',
        ]);

        $this->assertDatabaseHas('audience_visibilities', [
            'user_id' => $user->id,
            'visibility' => 'community',
        ]);

        // Update to friends_only
        $response = $this->postJson('/api/audience-visibility', [
            'visibility' => 'friends_only',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'visibility' => 'friends_only',
        ]);

        $this->assertDatabaseHas('audience_visibilities', [
            'user_id' => $user->id,
            'visibility' => 'friends_only',
        ]);
    }

    public function test_validation_fails_for_invalid_visibility_options()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/audience-visibility', [
            'visibility' => 'invalid-option',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
        ]);
    }
}
