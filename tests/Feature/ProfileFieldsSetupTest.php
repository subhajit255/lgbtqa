<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ProfileFieldsSetupTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test setup profile with camelCase keys and verify they are stored and returned properly.
     */
    public function test_setup_profile_with_camel_case_fields()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $payload = [
            'sleepRhythm' => 1,
            'kidsHave' => 2,
            'kidsFuture' => 3,
            'petsCurrent' => [1, 2],
            'petsFuture' => 4,
            'livingPreference' => 5,
            'travelImportance' => 6,
            'preferredCommunication' => [7, 8],
            'loveLanguage' => [9, 10],
            'socialEnergy' => 11,
            'personalityType' => 12,
            'comingOutStatus' => 'Out',
            'showComingOutStatus' => true,
            'religion' => ['Atheism'],
            'showReligion' => false,
            'politicalViews' => ['Liberal'],
            'showPoliticalViews' => true,
            'musicTests' => ['Pop', 'Rock'],
            'languagesWritten' => ['en', 'fr'],
            'nationality' => ['Afghan'],
            'allShowOnProfile' => true,
            'bodyType' => 1,
            'hairColor' => 2,
            'eyeColor' => 3,
            'relationshipStatus' => 4,
            'lookingFor' => 5,
            'datingPace' => 6,
            'languagesSpoken' => ['en', 'es'],
            'languagesLearning' => ['de'],
            'datingPreferences' => [1, 2],
        ];

        $response = $this->postJson('/api/setup/profile', $payload);
        $response->assertStatus(200);

        // Verify stored in Database
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'sleep_rhythm' => 1,
            'kids_have' => 2,
            'kids_future' => 3,
            'pets_current' => json_encode([1, 2]),
            'pets_future' => 4,
            'living_preference' => 5,
            'travel_importance' => 6,
            'preferred_communication' => json_encode([7, 8]),
            'love_language' => json_encode([9, 10]),
            'social_energy' => 11,
            'personality_type' => 12,
            'coming_out_status' => 'Out',
            'show_coming_out_status' => 1,
            'religion' => json_encode(['Atheism']),
            'show_religion' => 0,
            'political_views' => json_encode(['Liberal']),
            'show_political_views' => 1,
            'music_tests' => json_encode(['Pop', 'Rock']),
            'languages_written' => json_encode(['en', 'fr']),
            'nationality' => json_encode(['Afghan']),
            'all_show_on_profile' => 1,
            'body_type' => 1,
            'hair_color' => 2,
            'eye_color' => 3,
            'relationship_status' => 4,
            'what_i_am_looking_for' => 5,
            'dating_pace' => 6,
            'languages_spoken' => json_encode(['en', 'es']),
            'languages_learning' => json_encode(['de']),
            'dating_preferences' => json_encode([1, 2]),
        ]);

        // Get profile and verify camelCase & snake_case returned values
        $response = $this->postJson('/api/get/profile');
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'sleep_rhythm' => 1,
            'sleepRhythm' => 1,
            'kids_have' => 2,
            'kidsHave' => 2,
            'kids_future' => 3,
            'kidsFuture' => 3,
            'pets_current' => [1, 2],
            'petsCurrent' => [1, 2],
            'pets_future' => 4,
            'petsFuture' => 4,
            'living_preference' => 5,
            'livingPreference' => 5,
            'travel_importance' => 6,
            'travelImportance' => 6,
            'preferred_communication' => [7, 8],
            'preferredCommunication' => [7, 8],
            'love_language' => [9, 10],
            'loveLanguage' => [9, 10],
            'social_energy' => 11,
            'socialEnergy' => 11,
            'personality_type' => 12,
            'personalityType' => 12,
            'coming_out_status' => 'Out',
            'comingOutStatus' => 'Out',
            'show_coming_out_status' => true,
            'showComingOutStatus' => true,
            'religion' => ['Atheism'],
            'show_religion' => false,
            'showReligion' => false,
            'political_views' => ['Liberal'],
            'politicalViews' => ['Liberal'],
            'show_political_views' => true,
            'showPoliticalViews' => true,
            'music_tests' => ['Pop', 'Rock'],
            'musicTests' => ['Pop', 'Rock'],
            'musicTaste' => ['Pop', 'Rock'],
            'languages_written' => ['en', 'fr'],
            'languagesWritten' => ['en', 'fr'],
            'nationality' => ['Afghan'],
            'all_show_on_profile' => true,
            'allShowOnProfile' => true,
            'body_type' => 1,
            'bodyType' => 1,
            'hair_color' => 2,
            'hairColor' => 2,
            'eye_color' => 3,
            'eyeColor' => 3,
            'relationship_status' => 4,
            'relationshipStatus' => 4,
            'what_i_am_looking_for' => 5,
            'lookingFor' => 5,
            'dating_pace' => 6,
            'datingPace' => 6,
            'languages_spoken' => ['en', 'es'],
            'languagesSpoken' => ['en', 'es'],
            'languages_learning' => ['de'],
            'languagesLearning' => ['de'],
            'dating_preferences' => [1, 2],
        ]);
    }

    /**
     * Test setup profile with snake_case keys, and different formats for nationality (string, int/ID).
     */
    public function test_setup_profile_with_snake_case_fields_and_various_nationality_formats()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Scenario 1: nationality as string
        $payload1 = [
            'sleep_rhythm' => 2,
            'nationality' => 'Afghan',
        ];

        $response = $this->postJson('/api/setup/profile', $payload1);
        $response->assertStatus(200);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'sleep_rhythm' => 2,
            'nationality' => 'Afghan',
        ]);

        $response = $this->postJson('/api/get/profile');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'sleep_rhythm' => 2,
            'nationality' => 'Afghan',
        ]);

        // Scenario 2: nationality as integer ID
        $payload2 = [
            'nationality' => 82,
        ];

        $response = $this->postJson('/api/setup/profile', $payload2);
        $response->assertStatus(200);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'nationality' => 82,
        ]);

        $response = $this->postJson('/api/get/profile');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'nationality' => 82,
        ]);
    }
}
