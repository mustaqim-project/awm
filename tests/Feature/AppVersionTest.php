<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class AppVersionTest extends TestCase
{
    public function test_app_version_returns_manifest(): void
    {
        $response = $this->getJson('/app/version');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'app_name' => 'COOCA My Own',
            'latest_version' => '1.0.4',
            'latest_build' => 5,
        ]);

        $data = $response->json();
        $this->assertGreaterThan(0, $data['file_size']);
        $this->assertNotNull($data['updated_at']);
        $this->assertEquals(url('/cooca.apk'), $data['download_url']);
    }

    public function test_api_v1_app_version_returns_manifest(): void
    {
        $response = $this->getJson('/api/v1/app/version');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'app_name' => 'COOCA My Own',
            'latest_version' => '1.0.4',
            'latest_build' => 5,
        ]);

        $data = $response->json();
        $this->assertGreaterThan(0, $data['file_size']);
        $this->assertNotNull($data['updated_at']);
    }
}
