<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Bug;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class BugReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_cannot_report_bug()
    {
        $response = $this->postJson('/api/bug/report', [
            'text' => 'This is a test bug description',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_report_bug_with_text_only()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/bug/report', [
            'text' => 'This is a test bug description',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Bug reported successfully.',
        ]);

        $this->assertDatabaseHas('bugs', [
            'user_id' => $user->id,
            'text' => 'This is a test bug description',
            'image' => null,
            'status' => 'pending',
        ]);
    }

    public function test_authenticated_user_can_report_bug_with_text_and_image()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        Passport::actingAs($user);

        $image = UploadedFile::fake()->image('bug_screenshot.png');

        $response = $this->postJson('/api/bug/report', [
            'text' => 'This bug has an image',
            'image' => $image,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Bug reported successfully.',
        ]);

        $bug = Bug::where('user_id', $user->id)->first();
        $this->assertNotNull($bug->image);

        // Check file exists in our public storage directory structure
        $this->assertFileExists(storage_path('app/public/bugs/' . $bug->image));

        // Clean up
        @unlink(storage_path('app/public/bugs/' . $bug->image));
    }

    public function test_validation_errors_for_bug_report_missing_text()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/bug/report', []);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
            'message' => 'The text field is required.',
        ]);
    }

    public function test_admin_can_view_bug_list()
    {
        $admin = User::factory()->create();
        
        // Report a bug first
        $user = User::factory()->create();
        $bug = Bug::create([
            'user_id' => $user->id,
            'text' => 'Admin test bug',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/bug/list');

        $response->assertStatus(200);
        $response->assertSee('Bug Reports');
        $response->assertSee('Admin test bug');
    }

    public function test_admin_can_view_bug_details()
    {
        $admin = User::factory()->create();
        
        $user = User::factory()->create();
        $bug = Bug::create([
            'user_id' => $user->id,
            'text' => 'Detail test bug text description',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get("/admin/bug/view/{$bug->id}");

        $response->assertStatus(200);
        $response->assertSee('Bug Report Details');
        $response->assertSee('Detail test bug text description');
    }

    public function test_admin_can_update_bug_status()
    {
        $admin = User::factory()->create();
        
        $user = User::factory()->create();
        $bug = Bug::create([
            'user_id' => $user->id,
            'text' => 'Status update test bug',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post("/admin/bug/update-status/{$bug->id}", [
            'status' => 'working progress',
        ]);

        $response->assertStatus(302); // redirects back
        
        $bug->refresh();
        $this->assertEquals('working progress', $bug->status);
    }
}
