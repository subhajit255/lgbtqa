<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ChatUserListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_cannot_access_chat_user_list()
    {
        $response = $this->getJson('/api/chats/users');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_chat_user_list_excluding_self()
    {
        $me = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);
        
        $otherUser = User::factory()->create([
            'name' => 'John Doe',
            'username' => 'johndoe',
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        Passport::actingAs($me);

        $response = $this->getJson('/api/chats/users');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Users retrieved successfully',
        ]);

        // Assert that the response data contains other user but not me
        $response->assertJsonFragment([
            'id' => $otherUser->id,
            'name' => $otherUser->name,
            'username' => $otherUser->username,
        ]);

        $response->assertJsonMissing([
            'id' => $me->id,
        ]);
    }

    public function test_chat_user_list_only_returns_standard_active_and_unblocked_users()
    {
        $me = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        // Standard, active, unblocked user (should be returned)
        $validUser = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        // Inactive user (should be excluded)
        $inactiveUser = User::factory()->create([
            'user_type' => 3,
            'is_active' => 0,
            'is_blocked' => 0,
        ]);

        // Blocked user (should be excluded)
        $blockedUser = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 1,
        ]);

        // Admin user (should be excluded)
        $adminUser = User::factory()->create([
            'user_type' => 2, // Admin
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        Passport::actingAs($me);

        $response = $this->getJson('/api/chats/users');

        $response->assertStatus(200);

        // Assert valid user is in list
        $response->assertJsonFragment([
            'id' => $validUser->id,
        ]);

        // Assert other users are not in list
        $response->assertJsonMissing([
            'id' => $inactiveUser->id,
        ]);
        $response->assertJsonMissing([
            'id' => $blockedUser->id,
        ]);
        $response->assertJsonMissing([
            'id' => $adminUser->id,
        ]);
    }

    public function test_chat_user_list_excludes_blocked_users()
    {
        $me = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        $blockedByMeUser = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        $blockedMeUser = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        $otherUser = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        // I block $blockedByMeUser
        \App\Models\UserBlock::create([
            'user_id' => $me->id,
            'blocked_user_id' => $blockedByMeUser->id,
        ]);

        // $blockedMeUser blocks me
        \App\Models\UserBlock::create([
            'user_id' => $blockedMeUser->id,
            'blocked_user_id' => $me->id,
        ]);

        Passport::actingAs($me);

        $response = $this->getJson('/api/chats/users');

        $response->assertStatus(200);

        // $otherUser should be present
        $response->assertJsonFragment([
            'id' => $otherUser->id,
        ]);

        // Blocked users should be excluded
        $response->assertJsonMissing([
            'id' => $blockedByMeUser->id,
        ]);
        $response->assertJsonMissing([
            'id' => $blockedMeUser->id,
        ]);
    }

    public function test_chat_user_list_can_be_filtered_by_search()
    {
        $me = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        $matchingUserByName = User::factory()->create([
            'name' => 'Alice Smith',
            'username' => 'alice123',
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        $matchingUserByUsername = User::factory()->create([
            'name' => 'Bob Miller',
            'username' => 'superbob',
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        $nonMatchingUser = User::factory()->create([
            'name' => 'Charlie Brown',
            'username' => 'charlie',
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        Passport::actingAs($me);

        // Search by name "Alice"
        $response = $this->getJson('/api/chats/users?search=Alice');
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $matchingUserByName->id]);
        $response->assertJsonMissing(['id' => $matchingUserByUsername->id]);
        $response->assertJsonMissing(['id' => $nonMatchingUser->id]);

        // Search by username "super"
        $response = $this->getJson('/api/chats/users?search=super');
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $matchingUserByUsername->id]);
        $response->assertJsonMissing(['id' => $matchingUserByName->id]);
        $response->assertJsonMissing(['id' => $nonMatchingUser->id]);
    }

    public function test_chat_user_list_pagination()
    {
        $me = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        // Create 15 other standard users
        $otherUsers = User::factory()->count(15)->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ])->sortBy('id')->values();

        Passport::actingAs($me);

        // Calculate expected total dynamically (including any pre-existing users in the DB)
        $expectedTotal = User::where('id', '!=', $me->id)
            ->where('user_type', 3)
            ->where('is_active', 1)
            ->where('is_blocked', 0)
            ->count();

        // Fetch page 1 with per_page = 10
        $response = $this->getJson('/api/chats/users?page_no=1&per_page=10');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'current_page',
                'data',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ],
        ]);

        $responseData = $response->json('data.data');
        $this->assertCount(min(10, $expectedTotal), $responseData);
        $this->assertEquals($expectedTotal, $response->json('data.total'));
        $this->assertEquals(1, $response->json('data.current_page'));

        // Fetch page 2 with per_page = 10
        $response = $this->getJson('/api/chats/users?page_no=2&per_page=10');
        $response->assertStatus(200);
        $responseData = $response->json('data.data');
        $this->assertCount(max(0, $expectedTotal - 10), $responseData);
        $this->assertEquals(2, $response->json('data.current_page'));
    }

    public function test_chat_user_list_returns_user_wise_last_message_and_date_time()
    {
        $me = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        $otherUser = User::factory()->create([
            'user_type' => 3,
            'is_active' => 1,
            'is_blocked' => 0,
        ]);

        // Create a 1-on-1 chat
        $chat = \App\Models\Chat::create([
            'is_group' => false,
        ]);

        \App\Models\ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $me->id]);
        \App\Models\ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $otherUser->id]);

        // Send a message
        $message = \App\Models\Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $otherUser->id,
            'message' => 'Hello from other user!',
        ]);

        Passport::actingAs($me);

        $response = $this->getJson('/api/chats/users');
        $response->assertStatus(200);

        $userData = collect($response->json('data.data'))->firstWhere('id', $otherUser->id);

        $this->assertNotNull($userData);
        $this->assertEquals('Hello from other user!', $userData['last_message']);
        $this->assertNotNull($userData['last_message_date_time']);
        $this->assertEquals($message->id, $userData['latest_message']['id']);
    }
}
