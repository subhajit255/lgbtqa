<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ChatMessageFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Event::fake();
    }

    public function test_user_can_send_and_reply_to_a_message()
    {
        $this->withoutExceptionHandling();
        $user1 = User::factory()->create(['user_type' => 3, 'is_active' => 1]);
        $user2 = User::factory()->create(['user_type' => 3, 'is_active' => 1]);

        $chat = Chat::create(['is_group' => false]);
        ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $user1->id]);
        ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $user2->id]);

        Passport::actingAs($user1);

        // Send first message
        $response1 = $this->postJson("/api/chats/{$chat->id}/messages", [
            'type' => 'text',
            'message' => 'First message',
        ]);
        $response1->assertStatus(201);
        $message1Id = $response1->json('data.id');

        // Reply to first message
        $response2 = $this->postJson("/api/chats/{$chat->id}/messages", [
            'type' => 'text',
            'message' => 'Replying to first message',
            'reply_to_message_id' => $message1Id,
        ]);
        $response2->assertStatus(201);
        $response2->assertJsonPath('data.reply_to_message_id', $message1Id);
        $response2->assertJsonPath('data.reply_to_message.message', 'First message');
    }

    public function test_user_can_edit_own_message()
    {
        $user = User::factory()->create(['user_type' => 3, 'is_active' => 1]);
        $chat = Chat::create(['is_group' => false]);
        ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $user->id]);

        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'type' => 'text',
            'message' => 'Original text',
        ]);

        Passport::actingAs($user);

        $response = $this->putJson("/api/messages/{$message->id}", [
            'message' => 'Edited text',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.message', 'Edited text');
        $response->assertJsonPath('data.is_edited', true);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'message' => 'Edited text',
            'is_edited' => 1,
        ]);
    }

    public function test_user_can_delete_own_message()
    {
        $user = User::factory()->create(['user_type' => 3, 'is_active' => 1]);
        $chat = Chat::create(['is_group' => false]);
        ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $user->id]);

        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'type' => 'text',
            'message' => 'To be deleted',
        ]);

        Passport::actingAs($user);

        $response = $this->deleteJson("/api/messages/{$message->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_user_can_pin_and_unpin_text_message()
    {
        $user = User::factory()->create(['user_type' => 3, 'is_active' => 1]);
        $chat = Chat::create(['is_group' => false]);
        ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $user->id]);

        $textMessage = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'type' => 'text',
            'message' => 'Text message to pin',
        ]);

        $imageMessage = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'type' => 'image',
            'attachment' => 'test.jpg',
        ]);

        Passport::actingAs($user);

        // Pin text message (success)
        $responsePin = $this->postJson("/api/messages/{$textMessage->id}/pin");
        $responsePin->assertStatus(200);
        $responsePin->assertJsonPath('is_pinned', true);

        // Unpin text message (success)
        $responseUnpin = $this->postJson("/api/messages/{$textMessage->id}/pin");
        $responseUnpin->assertStatus(200);
        $responseUnpin->assertJsonPath('is_pinned', false);

        // Attempt to pin non-text message (should fail with 422)
        $responseFail = $this->postJson("/api/messages/{$imageMessage->id}/pin");
        $responseFail->assertStatus(422);
    }

    public function test_user_can_forward_message_to_another_chat()
    {
        $user = User::factory()->create(['user_type' => 3, 'is_active' => 1]);

        $chat1 = Chat::create(['is_group' => false]);
        ChatParticipant::create(['chat_id' => $chat1->id, 'user_id' => $user->id]);

        $chat2 = Chat::create(['is_group' => false]);
        ChatParticipant::create(['chat_id' => $chat2->id, 'user_id' => $user->id]);

        $message = Message::create([
            'chat_id' => $chat1->id,
            'sender_id' => $user->id,
            'type' => 'text',
            'message' => 'Forward this message',
        ]);

        Passport::actingAs($user);

        $response = $this->postJson("/api/messages/{$message->id}/forward", [
            'target_chat_ids' => [$chat2->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat2->id,
            'sender_id' => $user->id,
            'message' => 'Forward this message',
            'is_forwarded' => 1,
            'forwarded_from_message_id' => $message->id,
        ]);
    }
}
