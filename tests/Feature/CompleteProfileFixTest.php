<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

final class CompleteProfileFixTest extends TestCase
{
    public function test_complete_profile_rendered_javascript_is_free_of_html_encoded_syntax_errors(): void
    {
        $user = new User(['name' => 'Budi Owner', 'phone' => '08123456789']);
        $business = new Business(['name' => 'Kedai Kopi Budi', 'phone' => '08123456789']);

        $view = view('auth.complete-profile', [
            'user' => $user,
            'business' => $business,
            'primaryLocation' => null,
            'errors' => new ViewErrorBag(),
        ])->render();

        // 1. Ensure alert() does not contain &quot; which causes SyntaxError: Unexpected token '&'
        $this->assertStringNotContainsString('alert(&quot;', $view);
        $this->assertStringContainsString('alert("', $view);

        // 2. Ensure businessSetupLocation is declared and registered
        $this->assertStringContainsString('function businessSetupLocation()', $view);
        $this->assertStringContainsString('window.businessSetupLocation = businessSetupLocation', $view);
        $this->assertStringContainsString("Alpine.data('businessSetupLocation'", $view);

        // 3. Ensure marketingLayout is declared and registered
        $this->assertStringContainsString('function marketingLayout()', $view);
        $this->assertStringContainsString('window.marketingLayout = marketingLayout', $view);
        $this->assertStringContainsString("Alpine.data('marketingLayout'", $view);

        // 4. Ensure no i[data-lucide] tags directly hold :class that get broken during Lucide DOM replacement
        $this->assertDoesNotMatchRegularExpression('/<i data-lucide="chevron-down"[^>]*:class=/', $view);
        $this->assertDoesNotMatchRegularExpression('/<i data-lucide="crosshair"[^>]*:class=/', $view);
    }

    public function test_web_security_headers_allow_google_analytics_and_cdn_assets(): void
    {
        $response = $this->get('/login');
        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertNotEmpty($csp);

        // Script-src includes GTM and Google Analytics
        $this->assertStringContainsString('https://www.googletagmanager.com', $csp);
        $this->assertStringContainsString('https://www.google-analytics.com', $csp);

        // Connect-src includes OpenStreetMap, Nominatim, CDNs for maps/sourcemaps, and Google Analytics
        $this->assertStringContainsString('https://nominatim.openstreetmap.org', $csp);
        $this->assertStringContainsString('https://*.tile.openstreetmap.org', $csp);
        $this->assertStringContainsString('https://cdn.jsdelivr.net', $csp);
        $this->assertStringContainsString('https://unpkg.com', $csp);
        $this->assertStringContainsString('https://www.google-analytics.com', $csp);
        $this->assertStringContainsString('https://analytics.google.com', $csp);

        // Img-src includes GTM and Google Analytics
        $this->assertStringContainsString('https://www.googletagmanager.com', $csp);
        $this->assertStringContainsString('https://www.google-analytics.com', $csp);
    }
}
