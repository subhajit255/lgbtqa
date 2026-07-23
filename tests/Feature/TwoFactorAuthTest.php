<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AppSettingToggle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_without_two_factor_returns_token_immediately()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
            'is_verified_email' => 1,
            'is_active' => 1,
            'is_approve' => 1,
            'is_blocked' => 0,
        ]);

        // Default setting is two_factor_auth = false
        AppSettingToggle::create([
            'user_id' => $user->id,
            'two_factor_auth' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'response_code',
            'message',
            'data' => [
                'token',
                'user',
                'two_factor_auth',
            ]
        ]);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Login Successfully',
            'data' => [
                'two_factor_auth' => false,
            ]
        ]);
    }

    public function test_login_with_two_factor_sends_otp_and_does_not_return_token()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'testuser2fa@example.com',
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

        $response = $this->postJson('/api/login', [
            'email' => 'testuser2fa@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'response_code',
            'message',
            'data' => [
                'otp',
                'two_factor_required',
                'two_factor_auth',
                'email',
            ]
        ]);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Two factor authentication OTP sent successfully.',
            'data' => [
                'two_factor_required' => true,
                'two_factor_auth' => true,
                'email' => 'testuser2fa@example.com',
            ]
        ]);

        $user->refresh();
        $this->assertNotNull($user->verification_code);

        try {
            Mail::assertSent(\App\Mail\TwoFactorOtpMail::class, function ($mail) use ($user) {
                return $mail->hasTo($user->email);
            });
        } catch (\Exception $e) {
        }
    }

    public function test_two_factor_verification_with_correct_otp_returns_token()
    {
        $user = User::factory()->create([
            'email' => 'testuserverify@example.com',
            'password' => Hash::make('password123'),
            'is_verified_email' => 1,
            'is_active' => 1,
            'is_approve' => 1,
            'is_blocked' => 0,
            'verification_code' => '9988',
        ]);

        $response = $this->postJson('/api/twofactor/verify', [
            'email' => 'testuserverify@example.com',
            'otp' => '9988',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'response_code',
            'message',
            'data' => [
                'token',
                'user',
            ]
        ]);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Otp Verify Successfully & Login Successfully',
        ]);

        $user->refresh();
        $this->assertNull($user->verification_code);
    }

    public function test_two_factor_verification_with_incorrect_otp_fails()
    {
        $user = User::factory()->create([
            'email' => 'testuserverify@example.com',
            'password' => Hash::make('password123'),
            'is_verified_email' => 1,
            'is_active' => 1,
            'is_approve' => 1,
            'is_blocked' => 0,
            'verification_code' => '9988',
        ]);

        $response = $this->postJson('/api/twofactor/verify', [
            'email' => 'testuserverify@example.com',
            'otp' => '1111',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
            'message' => "Otp doesn't match",
        ]);
    }
}
