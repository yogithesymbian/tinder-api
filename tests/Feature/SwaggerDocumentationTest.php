<?php

namespace Tests\Feature;

use Tests\TestCase;

class SwaggerDocumentationTest extends TestCase
{
    public function test_swagger_ui_is_accessible()
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200);
        $response->assertSee('Tinder API Documentation');
    }

    public function test_swagger_json_is_generated()
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        $json = $response->json();
        $this->assertEquals('Tinder API', $json['info']['title']);
        $this->assertEquals('1.0.0', $json['info']['version']);
    }

    public function test_swagger_documents_all_authentication_endpoints()
    {
        $response = $this->get('/docs');
        
        $json = $response->json();
        $paths = $json['paths'];
        
        // Check that all authentication endpoints are documented
        $this->assertArrayHasKey('/api/v1/register', $paths);
        $this->assertArrayHasKey('/api/v1/login', $paths);
        $this->assertArrayHasKey('/api/v1/logout', $paths);
        
        // Verify they are POST endpoints
        $this->assertArrayHasKey('post', $paths['/api/v1/register']);
        $this->assertArrayHasKey('post', $paths['/api/v1/login']);
        $this->assertArrayHasKey('post', $paths['/api/v1/logout']);
    }
}
