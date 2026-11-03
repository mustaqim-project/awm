<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class PublicApkDownloadRouteTest extends TestCase
{
    public function test_public_download_apk_route_is_defined_and_resolves_url(): void
    {
        $this->assertTrue(Route::has('public.download.apk'), 'Route [public.download.apk] must be defined.');
        $this->assertStringEndsWith('/download/apk', route('public.download.apk'));
    }

    public function test_download_apk_aliases_redirect_to_named_route(): void
    {
        $responseDownloadApk = $this->get('/download-apk');
        $responseDownloadApk->assertRedirect(route('public.download.apk'));

        $responseCoocaApk = $this->get('/download/cooca.apk');
        $responseCoocaApk->assertRedirect(route('public.download.apk'));
    }

    public function test_download_apk_serves_valid_response(): void
    {
        $response = $this->get(route('public.download.apk'));

        if (file_exists(public_path('cooca.apk')) || file_exists(public_path('downloads/cooca.apk'))) {
            $response->assertStatus(200);
            $response->assertHeader('Content-Type', 'application/vnd.android.package-archive');
        } else {
            $this->assertContains($response->getStatusCode(), [302, 404]);
        }
    }

    public function test_app_version_json_endpoint_is_accessible(): void
    {
        $response = $this->get('/app/version');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'app_name' => 'COOCA My Own',
        ]);
    }
}
