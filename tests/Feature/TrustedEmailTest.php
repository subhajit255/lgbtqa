<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TrustedEmailTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_cannot_access_trusted_email_endpoints()
    {
        $response = $this->postJson('/api/trusted-email/add', [
            'email' => 'trusted@example.com',
        ]);
        $response->assertStatus(401);

        $response = $this->postJson('/api/trusted-email/verify', [
            'email' => 'trusted@example.com',
            'otp' => '1234',
        ]);
        $response->assertStatus(401);
    }

    public function test_add_trusted_email_generates_and_returns_otp()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/trusted-email/add', [
            'email' => 'trusted@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'OTP generated successfully for trusted email.',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'otp',
                'email',
            ]
        ]);

        $user->refresh();
        $this->assertEquals('trusted@example.com', $user->trusted_email);
        $this->assertNotNull($user->trusted_email_otp);
        $this->assertEquals(0, $user->is_trusted_email_verified);
    }

    public function test_verify_trusted_email_fails_with_invalid_otp()
    {
        $user = User::factory()->create([
            'trusted_email' => 'trusted@example.com',
            'trusted_email_otp' => '1234',
            'is_trusted_email_verified' => 0,
        ]);
        Passport::actingAs($user);

        $response = $this->postJson('/api/trusted-email/verify', [
            'email' => 'trusted@example.com',
            'otp' => '9999', // wrong OTP
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
            'message' => 'The OTP or email does not match.',
        ]);
    }

    public function test_verify_trusted_email_succeeds_with_correct_otp()
    {
        $user = User::factory()->create([
            'trusted_email' => 'trusted@example.com',
            'trusted_email_otp' => '1234',
            'is_trusted_email_verified' => 0,
        ]);
        Passport::actingAs($user);

        $response = $this->postJson('/api/trusted-email/verify', [
            'email' => 'trusted@example.com',
            'otp' => '1234', // correct OTP
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Trusted email verified successfully.',
            'data' => [
                'trusted_email' => 'trusted@example.com',
                'is_trusted_email_verified' => true,
            ]
        ]);

        $user->refresh();
        $this->assertEquals(1, $user->is_trusted_email_verified);
        $this->assertNull($user->trusted_email_otp);
    }

    public function test_profile_returns_trusted_email_fields()
    {
        $user = User::factory()->create([
            'trusted_email' => 'trusted@example.com',
            'is_trusted_email_verified' => 1,
        ]);
        Passport::actingAs($user);

        $response = $this->postJson('/api/get/profile');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'trusted_email' => 'trusted@example.com',
            'is_trusted_email_verified' => true,
        ]);
    }
}
