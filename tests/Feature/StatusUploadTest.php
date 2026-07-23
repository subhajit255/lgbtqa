<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class StatusUploadTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_creating_image_status_returns_full_asset_url_path()
    {
        $user = User::factory()->create(['user_type' => 3]);
        Passport::actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/statuses', [
            'type' => 'image',
            'media_file' => $file,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => true,
            'message' => 'Status created successfully',
        ]);

        // Get the response data content field
        $contentUrl = $response->json('data.content');

        // It should be a full URL pointing to storage
        $this->assertStringContainsString('/storage/statuses/', $contentUrl);
        $this->assertStringStartsWith('http', $contentUrl);

        // Verify the file was stored on the fake disk
        $rawPath = str_replace(asset('storage') . '/', '', $contentUrl);
        Storage::disk('public')->assertExists($rawPath);
    }

    public function test_index_endpoint_returns_statuses_with_full_asset_urls()
    {
        $user = User::factory()->create(['user_type' => 3]);
        
        // Create a status with image file for the user
        $status = Status::create([
            'user_id' => $user->id,
            'type' => 'image',
            'content' => 'statuses/test_image.jpg',
            'expires_at' => now()->addHours(24),
            'is_active' => true,
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/statuses');

        $response->assertStatus(200);
        
        // Assert the returned status has the formatted asset URL under content
        $response->assertJsonFragment([
            'id' => $status->id,
            'type' => 'image',
            'content' => asset('storage/statuses/test_image.jpg'),
        ]);
    }

    public function test_user_statuses_endpoint_returns_full_asset_urls()
    {
        $user = User::factory()->create(['user_type' => 3]);
        
        // Create a status with video file for the user
        $status = Status::create([
            'user_id' => $user->id,
            'type' => 'video',
            'content' => 'statuses/test_video.mp4',
            'expires_at' => now()->addHours(24),
            'is_active' => true,
        ]);

        Passport::actingAs($user);

        $response = $this->getJson("/api/statuses/user/{$user->id}");

        $response->assertStatus(200);
        
        // Assert the returned status has the formatted asset URL under content
        $response->assertJsonFragment([
            'id' => $status->id,
            'type' => 'video',
            'content' => asset('storage/statuses/test_video.mp4'),
        ]);
    }
}
