<?php

namespace Tests\Feature;

use App\Models\Hobby;
use App\Models\HobbyItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Laravel\Passport\Passport;

class HobbiesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_hobbies_returns_seeded_data()
    {
        // Act: Get hobbies
        $response = $this->getJson(route('hobbies'));

        // Assert: Success and structure check
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'hobbies' => [
                    '*' => [
                        'title',
                        'item' => [
                            '*' => [
                                'id',
                                'uuid',
                                'name',
                            ]
                        ]
                    ]
                ],
                'lifestyle' => [
                    '*' => [
                        'title',
                        'item' => [
                            '*' => [
                                'id',
                                'uuid',
                                'name',
                            ]
                        ]
                    ]
                ],
                'home_and_future' => [
                    '*' => [
                        'title',
                        'item' => [
                            '*' => [
                                'id',
                                'uuid',
                                'name',
                            ]
                        ]
                    ]
                ],
                'your_vibe' => [
                    '*' => [
                        'title',
                        'item' => [
                            '*' => [
                                'id',
                                'uuid',
                                'name',
                            ]
                        ]
                    ]
                ],
            ]
        ]);

        // Assert that the items we seeded are present in the correct places
        $response->assertJsonFragment(['name' => 'Theatre']);
        $response->assertJsonFragment(['name' => 'Reading']);
    }

    public function test_setup_profile_syncs_hobbies_and_relationship_status()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Get some hobby item IDs from seeded data
        $hobbyItems = HobbyItem::limit(3)->get();
        $hobbyItemIds = $hobbyItems->pluck('id')->toArray();

        // Authenticate user via Passport
        Passport::actingAs($user);

        // Act: Setup profile
        $response = $this->postJson(route('setup.profile'), [
            'display_name' => 'Test User',
            'about' => 'Bio info',
            'relationship_status' => 2, // e.g. Single / In Relationship
            'hobbies' => $hobbyItemIds,
        ]);

        // Assert: Setup is successful
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Profile setup successfully',
        ]);

        $uniqueCode = $response->json('data.unique_code');
        $this->assertNotEmpty($uniqueCode);
        $this->assertEquals(10, strlen($uniqueCode));
        $this->assertEquals('https://dummy-link.com/user/' . $uniqueCode, $response->json('data.share_link'));
        $this->assertNotEmpty($response->json('data.qr_code'));

        $qrPath = storage_path('app/public/qrcodes/' . $uniqueCode . '.png');
        $this->assertFileExists($qrPath);
        if (file_exists($qrPath)) {
            unlink($qrPath);
        }

        // Assert the database has relationship status updated
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'relationship_status' => 2,
        ]);

        // Assert user has synced hobbies in database
        foreach ($hobbyItemIds as $id) {
            $this->assertDatabaseHas('user_hobby', [
                'user_id' => $user->id,
                'hobby_item_id' => $id,
            ]);
        }

        // Assert the response contains the synced hobbies
        $response->assertJsonPath('data.hobbies.0.items.0.id', $hobbyItemIds[0]);
    }

    public function test_get_profile_returns_synced_hobbies()
    {
        // Arrange: Create a user
        $user = User::factory()->create();
        $hobbyItems = HobbyItem::limit(2)->get();
        $user->hobbies()->sync($hobbyItems->pluck('id')->toArray());

        // Authenticate user
        Passport::actingAs($user);

        // Act: Get profile details
        $response = $this->postJson(route('get.profile'));

        // Assert: Success and data matches
        $response->assertStatus(200);
        $response->assertJsonPath('data.hobbies.0.items.0.name', $hobbyItems[0]->name);
        $response->assertJsonPath('data.hobbies.0.items.1.name', $hobbyItems[1]->name);

        $uniqueCode = $response->json('data.unique_code');
        $this->assertNotEmpty($uniqueCode);
        $this->assertEquals(10, strlen($uniqueCode));
        $this->assertEquals('https://dummy-link.com/user/' . $uniqueCode, $response->json('data.share_link'));
        $this->assertNotEmpty($response->json('data.qr_code'));

        $qrPath = storage_path('app/public/qrcodes/' . $uniqueCode . '.png');
        $this->assertFileExists($qrPath);
        if (file_exists($qrPath)) {
            unlink($qrPath);
        }
    }

    public function test_setup_and_get_profile_with_onboarding_fields()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Authenticate user via Passport
        Passport::actingAs($user);

        $payload = [
            'display_name' => 'John Doe',
            'relationship_status' => 2,
            'alcohol' => 1,
            'smoking' => 2,
            'exercise' => 3,
            'diet' => 4,
            'sleep_rhythm' => 1,
            'kids_have' => 2,
            'kids_future' => 3,
            'pets_current' => [1, 2],
            'pets_future' => 1,
            'living_preference' => 2,
            'travel_importance' => 3,
            'preferred_communication' => [2, 3],
            'love_language' => [1, 5],
            'social_energy' => 1,
            'personality_type' => 2,
            'education' => 3,
            'religion' => ['Christian', 'Buddhist'],
            'show_religion' => false,
            'coming_out_status' => 'Out to close friends',
            'show_coming_out_status' => false,
            'political_views' => ['Liberal', 'Centrist'],
            'show_political_views' => false,
        ];

        // Act: Setup profile
        $response = $this->postJson(route('setup.profile'), $payload);

        // Assert: Success and correct response structure and data
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Profile setup successfully',
            'data' => [
                'alcohol' => 1,
                'smoking' => 2,
                'exercise' => 3,
                'diet' => 4,
                'sleep_rhythm' => 1,
                'kids_have' => 2,
                'kids_future' => 3,
                'pets_current' => [1, 2],
                'pets_future' => 1,
                'living_preference' => 2,
                'travel_importance' => 3,
                'preferred_communication' => [2, 3],
                'love_language' => [1, 5],
                'social_energy' => 1,
                'personality_type' => 2,
                'education' => 3,
                'religion' => ['Christian', 'Buddhist'],
                'show_religion' => false,
                'coming_out_status' => 'Out to close friends',
                'show_coming_out_status' => false,
                'political_views' => ['Liberal', 'Centrist'],
                'show_political_views' => false,
            ]
        ]);

        // Assert the database has all new fields updated
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'alcohol' => 1,
            'smoking' => 2,
            'exercise' => 3,
            'diet' => 4,
            'sleep_rhythm' => 1,
            'kids_have' => 2,
            'kids_future' => 3,
            'pets_current' => json_encode([1, 2]),
            'pets_future' => 1,
            'living_preference' => 2,
            'travel_importance' => 3,
            'preferred_communication' => json_encode([2, 3]),
            'love_language' => json_encode([1, 5]),
            'social_energy' => 1,
            'personality_type' => 2,
            'education' => 3,
            'religion' => json_encode(['Christian', 'Buddhist']),
            'show_religion' => 0,
            'coming_out_status' => 'Out to close friends',
            'show_coming_out_status' => 0,
            'political_views' => json_encode(['Liberal', 'Centrist']),
            'show_political_views' => 0,
        ]);

        // Act: Get profile details
        $getResponse = $this->postJson(route('get.profile'));

        // Assert: Check structure and values in get profile
        $getResponse->assertStatus(200);
        $getResponse->assertJson([
            'status' => true,
            'data' => [
                'alcohol' => 1,
                'smoking' => 2,
                'exercise' => 3,
                'diet' => 4,
                'sleep_rhythm' => 1,
                'kids_have' => 2,
                'kids_future' => 3,
                'pets_current' => [1, 2],
                'pets_future' => 1,
                'living_preference' => 2,
                'travel_importance' => 3,
                'preferred_communication' => [2, 3],
                'love_language' => [1, 5],
                'social_energy' => 1,
                'personality_type' => 2,
                'education' => 3,
                'religion' => ['Christian', 'Buddhist'],
                'show_religion' => false,
                'coming_out_status' => 'Out to close friends',
                'show_coming_out_status' => false,
                'political_views' => ['Liberal', 'Centrist'],
                'show_political_views' => false,
            ]
        ]);
    }

    public function test_setup_and_get_profile_with_height()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Authenticate user via Passport
        Passport::actingAs($user);

        $payload = [
            'display_name' => 'Jane Height Doe',
            'height' => 175,
            'hair_length' => 1,
            'tattoos' => 2,
        ];

        // Act: Setup profile
        $response = $this->postJson(route('setup.profile'), $payload);

        // Assert: Success and correct response structure and data
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Profile setup successfully',
            'data' => [
                'height' => 175,
                'hair_length' => 1,
                'tattoos' => 2,
            ]
        ]);

        // Assert nationality is not in the json response keys
        $response->assertJsonMissing(['nationality']);

        // Assert the database has the height updated
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'height' => 175,
            'hair_length' => 1,
            'tattoos' => 2,
        ]);

        // Act: Get profile details
        $getResponse = $this->postJson(route('get.profile'));

        // Assert: Check height and absence of nationality
        $getResponse->assertStatus(200);
        $getResponse->assertJson([
            'status' => true,
            'data' => [
                'height' => 175,
                'hair_length' => 1,
                'tattoos' => 2,
            ]
        ]);
        $getResponse->assertJsonMissing(['nationality']);
    }

    public function test_setup_and_get_profile_with_interests_and_values()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Get some items from Values (Type 5) and Interests (Type 6)
        $valueItem = \App\Models\HobbyItem::whereHas('hobby', function($q) {
            $q->where('type', 5);
        })->first();

        $interestItem = \App\Models\HobbyItem::whereHas('hobby', function($q) {
            $q->where('type', 6);
        })->first();

        $hobbyItem = \App\Models\HobbyItem::whereHas('hobby', function($q) {
            $q->where('type', 1);
        })->first();

        Passport::actingAs($user);

        $payload = [
            'display_name' => 'John Full Profile',
            'hobbies' => [$hobbyItem->id],
            'interests' => [$interestItem->id],
            'values' => [$valueItem->id],
        ];

        // Act: Setup profile
        $response = $this->postJson(route('setup.profile'), $payload);

        // Assert: Success and correct separated response structures
        $response->assertStatus(200);
        $response->assertJsonPath('data.hobbies.0.items.0.id', $hobbyItem->id);
        $response->assertJsonPath('data.interests.0.id', $interestItem->id);
        $response->assertJsonPath('data.values.0.id', $valueItem->id);

        // Assert the database has all synced items in pivot table
        $this->assertDatabaseHas('user_hobby', [
            'user_id' => $user->id,
            'hobby_item_id' => $hobbyItem->id,
        ]);
        $this->assertDatabaseHas('user_hobby', [
            'user_id' => $user->id,
            'hobby_item_id' => $interestItem->id,
        ]);
        $this->assertDatabaseHas('user_hobby', [
            'user_id' => $user->id,
            'hobby_item_id' => $valueItem->id,
        ]);

        // Act: Get profile details
        $getResponse = $this->postJson(route('get.profile'));

        // Assert: Check separated arrays in get profile
        $getResponse->assertStatus(200);
        $getResponse->assertJsonPath('data.hobbies.0.items.0.id', $hobbyItem->id);
        $getResponse->assertJsonPath('data.interests.0.id', $interestItem->id);
        $getResponse->assertJsonPath('data.values.0.id', $valueItem->id);
    }

    public function test_setup_and_get_profile_with_toggles()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Authenticate user via Passport
        Passport::actingAs($user);

        $payload = [
            'coming_out_status' => 'Out to family',
            'show_coming_out_status' => false,
            'religion' => ['Spiritual', 'Hindu'],
            'show_religion' => false,
            'political_views' => ['Progressive / left', 'Center'],
            'show_political_views' => false,
        ];

        // Act: Setup profile
        $response = $this->postJson(route('setup.profile'), $payload);

        // Assert: Success and correct response structure and data
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Profile setup successfully',
            'data' => [
                'coming_out_status' => 'Out to family',
                'show_coming_out_status' => false,
                'religion' => ['Spiritual', 'Hindu'],
                'show_religion' => false,
                'political_views' => ['Progressive / left', 'Center'],
                'show_political_views' => false,
            ]
        ]);

        // Assert the database has all new fields updated
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'coming_out_status' => 'Out to family',
            'show_coming_out_status' => false,
            'religion' => json_encode(['Spiritual', 'Hindu']),
            'show_religion' => false,
            'political_views' => json_encode(['Progressive / left', 'Center']),
            'show_political_views' => false,
        ]);

        // Act: Get profile details
        $getResponse = $this->postJson(route('get.profile'));

        // Assert: Check structure and values in get profile
        $getResponse->assertStatus(200);
        $getResponse->assertJson([
            'status' => true,
            'data' => [
                'coming_out_status' => 'Out to family',
                'show_coming_out_status' => false,
                'religion' => ['Spiritual', 'Hindu'],
                'show_religion' => false,
                'political_views' => ['Progressive / left', 'Center'],
                'show_political_views' => false,
            ]
        ]);
    }

    public function test_setup_and_get_profile_with_music_taste()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Authenticate user via Passport
        Passport::actingAs($user);

        $payload = [
            'Music_taste' => ['Pop', 'Rock'],
        ];

        // Act: Setup profile
        $response = $this->postJson(route('setup.profile'), $payload);

        // Assert: Success and correct response structure and data
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Profile setup successfully',
            'data' => [
                'music_tests' => ['Pop', 'Rock'],
                'Music_taste' => ['Pop', 'Rock'],
                'music_taste' => ['Pop', 'Rock'],
            ]
        ]);

        // Assert the database has the music_tests updated
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'music_tests' => json_encode(['Pop', 'Rock']),
        ]);

        // Act: Get profile details
        $getResponse = $this->postJson(route('get.profile'));

        // Assert: Check structure and values in get profile
        $getResponse->assertStatus(200);
        $getResponse->assertJson([
            'status' => true,
            'data' => [
                'music_tests' => ['Pop', 'Rock'],
                'Music_taste' => ['Pop', 'Rock'],
                'music_taste' => ['Pop', 'Rock'],
            ]
        ]);
    }

    public function test_setup_and_get_profile_with_music_taste_lowercase()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Authenticate user via Passport
        Passport::actingAs($user);

        $payload = [
            'music_taste' => ['Jazz', 'Blues'],
        ];

        // Act: Setup profile
        $response = $this->postJson(route('setup.profile'), $payload);

        // Assert: Success and correct response structure and data
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Profile setup successfully',
            'data' => [
                'music_tests' => ['Jazz', 'Blues'],
                'Music_taste' => ['Jazz', 'Blues'],
                'music_taste' => ['Jazz', 'Blues'],
            ]
        ]);

        // Assert the database has the music_tests updated
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'music_tests' => json_encode(['Jazz', 'Blues']),
        ]);

        // Act: Get profile details
        $getResponse = $this->postJson(route('get.profile'));

        // Assert: Check structure and values in get profile
        $getResponse->assertStatus(200);
        $getResponse->assertJson([
            'status' => true,
            'data' => [
                'music_tests' => ['Jazz', 'Blues'],
                'Music_taste' => ['Jazz', 'Blues'],
                'music_taste' => ['Jazz', 'Blues'],
            ]
        ]);
    }

    public function test_setup_and_get_profile_with_sex_roles_and_toggle()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Authenticate user via Passport
        Passport::actingAs($user);

        $payload = [
            'importance' => 2,
            'role' => 3,
            'datingPace' => 1,
            'presentation' => 4,
            'AllshowOnProfile' => false,
        ];

        // Act: Setup profile
        $response = $this->postJson(route('setup.profile'), $payload);

        // Assert: Success and correct response structure and data
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Profile setup successfully',
            'data' => [
                'sex_importance' => 2,
                'importance' => 2,
                'role_position' => 3,
                'role' => 3,
                'dating_pace' => 1,
                'datingPace' => 1,
                'presentation_preference' => 4,
                'presentation' => 4,
                'all_show_on_profile' => false,
                'AllshowOnProfile' => false,
            ]
        ]);

        // Assert the database has the fields updated
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'sex_importance' => 2,
            'role_position' => 3,
            'dating_pace' => 1,
            'presentation_preference' => 4,
            'all_show_on_profile' => false,
        ]);

        // Act: Get profile details
        $getResponse = $this->postJson(route('get.profile'));

        // Assert: Check structure and values in get profile
        $getResponse->assertStatus(200);
        $getResponse->assertJson([
            'status' => true,
            'data' => [
                'sex_importance' => 2,
                'importance' => 2,
                'role_position' => 3,
                'role' => 3,
                'dating_pace' => 1,
                'datingPace' => 1,
                'presentation_preference' => 4,
                'presentation' => 4,
                'all_show_on_profile' => false,
                'AllshowOnProfile' => false,
            ]
        ]);
    }

    public function test_setup_and_get_profile_bug_verification()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Authenticate user via Passport
        Passport::actingAs($user);

        // We will test sending both 'eye_color' and 'eye' / 'what_i_am_looking_for'
        $payload = [
            'display_name' => 'Bug Tester',
            'body_type' => 2,
            'hair_color' => 3,
            'eye' => 4, // sent as eye
            'relationship_status' => 1,
            'what_i_am_looking_for' => 3,
            'dating_preferances' => [1, 2], // sent as multiple IDs (array)
            'language' => ['English', 'Spanish'], // sent as language
        ];

        // Act: Setup profile
        $response = $this->postJson(route('setup.profile'), $payload);

        // Assert setup profile returned values
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'data' => [
                'body_type' => 2,
                'hair_color' => 3,
                'eye_color' => 4,
                'relationship_status' => 1,
                'what_i_am_looking_for' => 3,
                'dating_preferences' => [1, 2],
                'dating_preferances' => [1, 2],
                'languages_spoken' => ['English', 'Spanish'],
                'languages' => ['English', 'Spanish'],
                'language' => ['English', 'Spanish'],
            ]
        ]);

        // Assert database has them
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'body_type' => 2,
            'hair_color' => 3,
            'eye_color' => 4,
            'relationship_status' => 1,
            'what_i_am_looking_for' => 3,
            'dating_preferences' => json_encode([1, 2]),
            'languages_spoken' => json_encode(['English', 'Spanish']),
        ]);

        // Act: Get profile
        $getResponse = $this->postJson(route('get.profile'));

        // Assert get profile returns the values
        $getResponse->assertStatus(200);
        $getResponse->assertJson([
            'status' => true,
            'data' => [
                'body_type' => 2,
                'hair_color' => 3,
                'eye_color' => 4,
                'relationship_status' => 1,
                'what_i_am_looking_for' => 3,
                'dating_preferences' => [1, 2],
                'dating_preferances' => [1, 2],
                'languages_spoken' => ['English', 'Spanish'],
                'languages' => ['English', 'Spanish'],
                'language' => ['English', 'Spanish'],
            ]
        ]);
    }

    public function test_setup_and_get_profile_dob_verification()
    {
        // Arrange: Create user
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Test with DD-MM-YYYY format
        $payload1 = [
            'display_name' => 'DOB Tester 1',
            'dob' => '15-12-1995',
        ];
        $response1 = $this->postJson(route('setup.profile'), $payload1);
        $response1->assertStatus(200);
        $response1->assertJsonPath('data.dob', '1995-12-15');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'dob' => '1995-12-15',
        ]);

        $getResponse1 = $this->postJson(route('get.profile'));
        $getResponse1->assertStatus(200);
        $getResponse1->assertJsonPath('data.dob', '1995-12-15');

        // Test with DD/MM/YYYY format
        $payload2 = [
            'display_name' => 'DOB Tester 2',
            'dob' => '25/08/1990',
        ];
        $response2 = $this->postJson(route('setup.profile'), $payload2);
        $response2->assertStatus(200);
        $response2->assertJsonPath('data.dob', '1990-08-25');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'dob' => '1990-08-25',
        ]);

        $getResponse2 = $this->postJson(route('get.profile'));
        $getResponse2->assertStatus(200);
        $getResponse2->assertJsonPath('data.dob', '1990-08-25');
    }
}

