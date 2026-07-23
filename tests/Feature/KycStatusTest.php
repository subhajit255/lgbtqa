<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\KycVerification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class KycStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_profile_returns_not_uploaded_yet_status_when_no_kyc_record_exists()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/get/profile');

        $response->assertStatus(200);
        $response->assertJsonPath('data.kyc_verification.status', 'not uploaded yet');
        $response->assertJsonPath('data.kyc_verification.badge_style', null);
        $response->assertJsonPath('data.kyc_verification.badge_color', null);
    }

    public function test_profile_returns_pending_status_when_kyc_record_is_pending()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        KycVerification::create([
            'user_id' => $user->id,
            'govt_id_image' => 'govt.jpg',
            'identity_image' => 'identity.jpg',
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/get/profile');

        $response->assertStatus(200);
        $response->assertJsonPath('data.kyc_verification.status', 'pending');
    }

    public function test_profile_returns_approved_status_when_kyc_record_is_approved()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        KycVerification::create([
            'user_id' => $user->id,
            'govt_id_image' => 'govt.jpg',
            'identity_image' => 'identity.jpg',
            'status' => 'approved',
        ]);

        $response = $this->postJson('/api/get/profile');

        $response->assertStatus(200);
        $response->assertJsonPath('data.kyc_verification.status', 'approved');
    }
}
