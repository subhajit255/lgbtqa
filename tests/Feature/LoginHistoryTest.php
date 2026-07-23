<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AppSettingToggle;
use App\Models\LoginHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Passport;
use Tests\TestCase;

class LoginHistoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_without_two_factor_records_login_history()
    {
        $user = User::factory()->create([
            'email' => 'loginhistory1@example.com',
            'password' => Hash::make('password123'),
            'is_verified_email' => 1,
            'is_active' => 1,
            'is_approve' => 1,
            'is_blocked' => 0,
        ]);

        AppSettingToggle::create([
            'user_id' => $user->id,
            'two_factor_auth' => false,
        ]);

        $this->assertDatabaseMissing('login_histories', [
            'user_id' => $user->id,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'loginhistory1@example.com',
            'password' => 'password123',
        ], [
            'User-Agent' => 'TestAgent_DirectLogin',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('login_histories', [
            'user_id' => $user->id,
            'user_agent' => 'TestAgent_DirectLogin',
        ]);
    }

    public function test_login_with_two_factor_records_login_history_on_verification()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'loginhistory2fa@example.com',
            'password' => Hash::make('password123'),
            'is_verified_email' => 1,
            'is_active' => 1,
            'is_approve' => 1,
            'is_blocked' => 0,
        ]);

        AppSettingToggle::create([
            'user_id' => $user->id,
            'two_factor_auth' => true,
        ]);

        // Send OTP first
        $this->postJson('/api/login', [
            'email' => 'loginhistory2fa@example.com',
            'password' => 'password123',
        ]);

        $user->refresh();
        $otp = $user->verification_code;

        $this->assertNotNull($otp);
        $this->assertDatabaseMissing('login_histories', [
            'user_id' => $user->id,
        ]);

        // Verify OTP
        $response = $this->postJson('/api/twofactor/verify', [
            'email' => 'loginhistory2fa@example.com',
            'otp' => $otp,
        ], [
            'User-Agent' => 'TestAgent_2FA',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('login_histories', [
            'user_id' => $user->id,
            'user_agent' => 'TestAgent_2FA',
        ]);
    }

    public function test_social_login_records_login_history()
    {
        $email = 'sociallogin@example.com';
        
        $this->assertDatabaseMissing('users', [
            'email' => $email,
        ]);

        $response = $this->postJson('/api/social/login', [
            'name' => 'Social User',
            'email' => $email,
        ], [
            'User-Agent' => 'TestAgent_Social',
        ]);

        $response->assertStatus(200);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('login_histories', [
            'user_id' => $user->id,
            'user_agent' => 'TestAgent_Social',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_login_history()
    {
        $response = $this->getJson('/api/login-history');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_login_history()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Browser_Test',
        ]);

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mobile_Test',
        ]);

        // Create another record for a different user
        $otherUser = User::factory()->create();
        LoginHistory::create([
            'user_id' => $otherUser->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Other_Test',
        ]);

        $response = $this->getJson('/api/login-history');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Login history retrieved successfully.',
        ]);

        // Verify paginated structure and count
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment([
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Browser_Test',
        ]);
        $response->assertJsonFragment([
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mobile_Test',
        ]);
        $response->assertJsonMissing([
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Other_Test',
        ]);
    }
}
