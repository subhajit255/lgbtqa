<?php

namespace Tests\Feature;

use Tests\TestCase;

class GeographyPaginationTest extends TestCase
{
    public function test_countries_api_pagination()
    {
        // Default pagination (15 items)
        $response = $this->getJson('/api/countries');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Countries Fetched',
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
        $response = $this->getJson('/api/countries?per_page=5&page_no=2');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'current_page' => 2,
                'per_page' => 5,
            ]
        ]);
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_states_api_pagination()
    {
        // 101 is India
        $response = $this->postJson('/api/states', [
            'country_id' => 101,
            'per_page' => 10,
            'page_no' => 1
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'States Fetched',
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
        $this->assertEquals(1, $response->json('data.current_page'));
        $this->assertEquals(10, $response->json('data.per_page'));
        $this->assertCount(10, $response->json('data.data'));
    }

    public function test_cities_api_pagination()
    {
        // Get cities
        $response = $this->postJson('/api/cities', [
            'country_id' => 101,
            'search' => 'a',
            'per_page' => 8,
            'page_no' => 1
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Cities Fetched',
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
        $this->assertEquals(1, $response->json('data.current_page'));
        $this->assertEquals(8, $response->json('data.per_page'));
        $this->assertCount(8, $response->json('data.data'));
    }

    public function test_validation_errors()
    {
        // Invalid per_page (must be integer)
        $response = $this->getJson('/api/countries?per_page=invalid');
        $response->assertStatus(422);

        // Invalid page_no (must be min 1)
        $response = $this->getJson('/api/countries?page_no=0');
        $response->assertStatus(422);
    }

    public function test_country_search()
    {
        // Search for India
        $response = $this->getJson('/api/countries?search=India');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Countries Fetched',
        ]);
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        $this->assertEquals('India', $data[0]['name']);
    }

    public function test_global_city_search()
    {
        // Search for Mumbai globally without country/state filters
        $response = $this->postJson('/api/cities', [
            'search' => 'Mumbai'
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Cities Fetched',
        ]);
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        $this->assertEquals('Mumbai', $data[0]['name']);
    }
}
