<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_cannot_change_password()
    {
        $response = $this->postJson('/api/change/password', [
            'old_password' => 'password',
            'new_password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);

        $response->assertStatus(401);
    }

    public function test_validation_errors_for_missing_fields()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/change/password', []);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
        ]);
    }

    public function test_validation_errors_for_mismatched_confirm_password()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/change/password', [
            'old_password' => 'password',
            'new_password' => 'newpassword123',
            'confirm_password' => 'different123',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
        ]);
    }

    public function test_validation_errors_for_short_new_password()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/change/password', [
            'old_password' => 'password',
            'new_password' => '123',
            'confirm_password' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
        ]);
    }

    public function test_incorrect_old_password_fails()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct_old_password'),
        ]);
        Passport::actingAs($user);

        $response = $this->postJson('/api/change/password', [
            'old_password' => 'wrong_old_password',
            'new_password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'response_code' => 422,
            'message' => 'The old password does not match.',
        ]);
    }

    public function test_successful_password_change()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct_old_password'),
        ]);
        Passport::actingAs($user);

        $response = $this->postJson('/api/change/password', [
            'old_password' => 'correct_old_password',
            'new_password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Password changed successfully.',
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertEquals('newpassword123', $user->original_password);
    }
}
