<?php

namespace Tests\Feature;

use App\Models\Cms;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CmsPageApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_fetch_cms_page_by_name()
    {
        // Seeding is already run in migration, but let's double check/create a specific test one to be fully decoupled
        $page = Cms::create([
            'title' => 'Test Page Title',
            'alias' => 'test-page-alias',
            'description' => '<h1>Hello World</h1><p>Test HTML description content.</p>',
            'is_active' => 1,
        ]);

        $response = $this->getJson("/api/cms/{$page->title}");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'Cms Page Fetched',
        ]);

        $response->assertJsonFragment([
            'uuid' => $page->uuid,
            'title' => 'Test Page Title',
            'description' => '<h1>Hello World</h1><p>Test HTML description content.</p>',
        ]);
    }

    public function test_cannot_fetch_inactive_cms_page()
    {
        $page = Cms::create([
            'title' => 'Inactive Page',
            'alias' => 'inactive-page',
            'description' => '<p>Inactive page content</p>',
            'is_active' => 0,
        ]);

        $response = $this->getJson("/api/cms/{$page->title}");

        $response->assertStatus(404);
        $response->assertJson([
            'status' => false,
            'response_code' => 404,
            'message' => 'Cms Page Not Found',
        ]);
    }

    public function test_returns_404_for_non_existent_name()
    {
        $response = $this->getJson('/api/cms/non-existent-title');

        $response->assertStatus(404);
        $response->assertJson([
            'status' => false,
            'response_code' => 404,
            'message' => 'Cms Page Not Found',
        ]);
    }

    public function test_can_fetch_all_cms_pages_grouped()
    {
        $response = $this->getJson('/api/cms/all');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'response_code' => 200,
            'message' => 'All CMS Pages Fetched',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'faqs_and_supports' => [
                    '*' => [
                        'uuid',
                        'title',
                        'description',
                        'image_path',
                        'short_desc',
                    ]
                ],
                'legal_and_privacy' => [
                    '*' => [
                        'uuid',
                        'title',
                        'description',
                        'image_path',
                        'short_desc',
                    ]
                ]
            ]
        ]);
    }
}
