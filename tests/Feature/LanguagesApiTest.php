<?php

namespace Tests\Feature;

use Tests\TestCase;

class LanguagesApiTest extends TestCase
{
    public function test_languages_api_pagination()
    {
        // Default pagination (15 items)
        $response = $this->getJson('/api/languages');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Languages Fetched',
        ]);
        $response->assertJsonStructure([
            'data' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
                'data',
            ]
        ]);
        $this->assertCount(15, $response->json('data.data'));

        // Custom page size (per_page = 5, page_no = 2)
        $response = $this->getJson('/api/languages?per_page=5&page_no=2');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'current_page' => 2,
                'per_page' => 5,
            ]
        ]);
        $this->assertCount(5, $response->json('data.data'));
        
        // Assert the second page starting element is indeed 'am' (Amharic)
        $data = $response->json('data.data');
        $this->assertEquals('am', $data[0]['code']);
        $this->assertEquals('Amharic', $data[0]['name']);
    }

    public function test_validation_errors()
    {
        // Invalid per_page (must be integer)
        $response = $this->getJson('/api/languages?per_page=invalid');
        $response->assertStatus(422);

        // Invalid page_no (must be min 1)
        $response = $this->getJson('/api/languages?page_no=0');
        $response->assertStatus(422);
    }

    public function test_language_search_by_name()
    {
        // Search for English
        $response = $this->getJson('/api/languages?search=english');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Languages Fetched',
        ]);
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        $this->assertEquals('en', $data[0]['code']);
        $this->assertEquals('English', $data[0]['name']);
    }

    public function test_language_search_by_code()
    {
        // Search for 'zh' (Chinese)
        $response = $this->getJson('/api/languages?search=zh');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Languages Fetched',
        ]);
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        $this->assertEquals('zh', $data[0]['code']);
        $this->assertEquals('Chinese', $data[0]['name']);
    }

    public function test_master_dropdown_does_not_contain_language_properties()
    {
        $response = $this->getJson('/api/master/dropdown');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertArrayNotHasKey('languages_spoken', $data);
        $this->assertArrayNotHasKey('languages_spoken_strings', $data);
        $this->assertArrayNotHasKey('languages_learning', $data);
        $this->assertArrayNotHasKey('languages_learning_strings', $data);
        $this->assertArrayNotHasKey('languages_written', $data);
        $this->assertArrayNotHasKey('languages_written_strings', $data);
    }
}
