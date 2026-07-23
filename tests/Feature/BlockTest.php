<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Laravel\Passport\Passport;

class BlockTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_block_a_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Passport::actingAs($user1);

        $response = $this->postJson("/api/users/block/{$user2->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'User blocked successfully.',
        ]);

        $this->assertDatabaseHas('user_blocks', [
            'user_id' => $user1->id,
            'blocked_user_id' => $user2->id,
        ]);
    }

    public function test_cannot_block_self()
    {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->postJson("/api/users/block/{$user->id}");

        $response->assertStatus(400);
        $response->assertJson([
            'status' => false,
            'response_code' => 400,
            'message' => 'You cannot block yourself.',
        ]);
    }

    public function test_can_retrieve_blocked_users_list()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // user1 blocks user2
        UserBlock::create([
            'user_id' => $user1->id,
            'blocked_user_id' => $user2->id,
        ]);

        Passport::actingAs($user1);

        $response = $this->getJson('/api/users/block-list');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'response_code',
            'message',
            'data' => [
                '*' => [
                    'uuid',
                    'name',
                    'email',
                    'profile_image',
                ]
            ]
        ]);

        $response->assertJsonFragment([
            'uuid' => $user2->uuid,
            'name' => $user2->name,
        ]);

        $response->assertJsonMissing([
            'uuid' => $user3->uuid,
        ]);
    }

    public function test_can_unblock_a_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create block relation
        UserBlock::create([
            'user_id' => $user1->id,
            'blocked_user_id' => $user2->id,
        ]);

        Passport::actingAs($user1);

        $response = $this->postJson("/api/users/unblock/{$user2->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'User unblocked successfully.',
        ]);

        $this->assertDatabaseMissing('user_blocks', [
            'user_id' => $user1->id,
            'blocked_user_id' => $user2->id,
        ]);
    }
}
