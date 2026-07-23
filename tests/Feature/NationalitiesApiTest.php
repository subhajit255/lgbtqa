<?php

namespace Tests\Feature;

use Tests\TestCase;

class NationalitiesApiTest extends TestCase
{
    public function test_nationalities_api_pagination()
    {
        // Default pagination (15 items)
        $response = $this->getJson('/api/nationalities');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Nationalities Fetched',
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
        $response = $this->getJson('/api/nationalities?per_page=5&page_no=2');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'current_page' => 2,
                'per_page' => 5,
            ]
        ]);
        $this->assertCount(5, $response->json('data.data'));
        
        $data = $response->json('data.data');
        $this->assertEquals(6, $data[0]['id']);
        $this->assertEquals('Angolan', $data[0]['name']);
    }

    public function test_validation_errors()
    {
        $response = $this->getJson('/api/nationalities?per_page=invalid');
        $response->assertStatus(422);

        $response = $this->getJson('/api/nationalities?page_no=0');
        $response->assertStatus(422);
    }

    public function test_nationality_search_by_name()
    {
        $response = $this->getJson('/api/nationalities?search=indian');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Nationalities Fetched',
        ]);
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        $this->assertEquals(82, $data[0]['id']);
        $this->assertEquals('Indian', $data[0]['name']);
    }

    public function test_nationality_search_ranking()
    {
        $response = $this->getJson('/api/nationalities?search=Dominican');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Nationalities Fetched',
        ]);
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        $this->assertEquals('Dominican', $data[0]['name']);
    }
}
