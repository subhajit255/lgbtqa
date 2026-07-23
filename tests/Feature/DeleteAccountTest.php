<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use App\Models\Gallery;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\Status;
use App\Models\KycVerification;
use App\Models\Bug;
use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\Message;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure directories exist
        @mkdir(storage_path('app/public/profile'), 0777, true);
        @mkdir(storage_path('app/public/gallery'), 0777, true);
        @mkdir(public_path('assets/uploads/posts'), 0777, true);
        @mkdir(storage_path('app/public/statuses'), 0777, true);
        @mkdir(storage_path('app/public/kyc'), 0777, true);
        @mkdir(storage_path('app/public/bugs'), 0777, true);
        @mkdir(storage_path('app/public/chat_attachments'), 0777, true);
    }

    public function test_guest_cannot_delete_account()
    {
        $response = $this->deleteJson('/api/delete-account');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_permanently_delete_account_and_all_data()
    {
        $this->withoutExceptionHandling();

        // 1. Setup User and OAuth Token
        $user = User::factory()->create([
            'profile_image' => 'dummy_profile.png',
        ]);
        Passport::actingAs($user);

        // Create dummy profile image file
        file_put_contents(storage_path('app/public/profile/dummy_profile.png'), 'dummy profile content');

        // Create user token record in oauth_access_tokens to assert token deletion
        DB::table('oauth_access_tokens')->insert([
            'id' => 'dummy-token-id-123',
            'user_id' => $user->id,
            'client_id' => 'dummy-client-id',
            'name' => 'Test Token',
            'scopes' => '[]',
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDays(1),
        ]);

        // 2. Create Profile
        $profile = Profile::create([
            'user_id' => $user->id,
            'display_name' => 'John Doe',
        ]);

        // 3. Create Gallery Images
        $gallery = Gallery::create([
            'user_id' => $user->id,
            'file' => 'dummy_gallery.png',
            'type' => 1,
            'is_active' => 1,
        ]);
        file_put_contents(storage_path('app/public/gallery/dummy_gallery.png'), 'dummy gallery content');

        // 4. Create Post & PostMedia
        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post Title',
            'description' => 'Test Post Description',
        ]);
        $postMedia = PostMedia::create([
            'post_id' => $post->id,
            'file' => 'assets/uploads/posts/dummy_post.png',
            'file_type' => 'image',
        ]);
        file_put_contents(public_path('assets/uploads/posts/dummy_post.png'), 'dummy post content');

        // 5. Create Status
        $status = Status::create([
            'user_id' => $user->id,
            'type' => 'image',
            'content' => 'statuses/dummy_status.png',
            'expires_at' => now()->addHours(24),
            'is_active' => true,
        ]);
        Storage::disk('public')->put('statuses/dummy_status.png', 'dummy status content');

        // 6. Create KYC Verification
        $kyc = KycVerification::create([
            'user_id' => $user->id,
            'govt_id_image' => 'dummy_govt.png',
            'identity_image' => 'dummy_identity.png',
            'status' => 'pending',
        ]);
        file_put_contents(storage_path('app/public/kyc/dummy_govt.png'), 'dummy govt content');
        file_put_contents(storage_path('app/public/kyc/dummy_identity.png'), 'dummy identity content');

        // 7. Create Bug Report
        $bug = Bug::create([
            'user_id' => $user->id,
            'text' => 'Dummy Bug description',
            'image' => 'dummy_bug.png',
            'status' => 'pending',
        ]);
        file_put_contents(storage_path('app/public/bugs/dummy_bug.png'), 'dummy bug content');

        // 8. Create Chat, Participants, Messages
        $chat = Chat::create([
            'is_group' => false,
            'name' => 'Direct Chat',
        ]);
        $participant = ChatParticipant::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'type' => 'image',
            'message' => 'Hello',
            'attachment' => 'chat_attachments/dummy_attachment.png',
        ]);
        Storage::disk('public')->put('chat_attachments/dummy_attachment.png', 'dummy attachment content');

        // Assert all files exist before deletion
        $this->assertFileExists(storage_path('app/public/profile/dummy_profile.png'));
        $this->assertFileExists(storage_path('app/public/gallery/dummy_gallery.png'));
        $this->assertFileExists(public_path('assets/uploads/posts/dummy_post.png'));
        $this->assertTrue(Storage::disk('public')->exists('statuses/dummy_status.png'));
        $this->assertFileExists(storage_path('app/public/kyc/dummy_govt.png'));
        $this->assertFileExists(storage_path('app/public/kyc/dummy_identity.png'));
        $this->assertFileExists(storage_path('app/public/bugs/dummy_bug.png'));
        $this->assertTrue(Storage::disk('public')->exists('chat_attachments/dummy_attachment.png'));

        // 9. Execute Account Deletion Endpoint
        $response = $this->deleteJson('/api/delete-account');

        // 10. Assert API Response
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Your account and all associated data have been permanently deleted.',
        ]);

        // 11. Assert Database Cleanup
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
        $this->assertDatabaseMissing('galleries', ['id' => $gallery->id]);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('post_media', ['id' => $postMedia->id]);
        $this->assertDatabaseMissing('statuses', ['id' => $status->id]);
        $this->assertDatabaseMissing('kyc_verifications', ['id' => $kyc->id]);
        $this->assertDatabaseMissing('bugs', ['id' => $bug->id]);
        $this->assertDatabaseMissing('chat_participants', ['id' => $participant->id]);
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
        $this->assertDatabaseMissing('oauth_access_tokens', ['user_id' => $user->id]);

        // 12. Assert File Cleanups on Storage/Disk
        $this->assertFileDoesNotExist(storage_path('app/public/profile/dummy_profile.png'));
        $this->assertFileDoesNotExist(storage_path('app/public/gallery/dummy_gallery.png'));
        $this->assertFileDoesNotExist(public_path('assets/uploads/posts/dummy_post.png'));
        $this->assertFalse(Storage::disk('public')->exists('statuses/dummy_status.png'));
        $this->assertFileDoesNotExist(storage_path('app/public/kyc/dummy_govt.png'));
        $this->assertFileDoesNotExist(storage_path('app/public/kyc/dummy_identity.png'));
        $this->assertFileDoesNotExist(storage_path('app/public/bugs/dummy_bug.png'));
        $this->assertFalse(Storage::disk('public')->exists('chat_attachments/dummy_attachment.png'));
    }
}
