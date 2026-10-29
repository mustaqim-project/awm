<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoPromptFiveTest extends TestCase
{
    use RefreshDatabase;
    public function test_demo_and_test_stores_are_excluded_from_scope_public_listed(): void
    {
        $bizActive = Business::create([
            'name' => 'Active Regular Store',
            'is_active' => true,
            'is_demo' => false,
            'is_test' => false,
            'is_public_listed' => true,
        ]);

        $bizDemo = Business::create([
            'name' => 'Showcase Demo Store',
            'is_active' => true,
            'is_demo' => true,
            'is_test' => false,
            'is_public_listed' => true,
        ]);

        $bizTest = Business::create([
            'name' => 'Internal Test Store',
            'is_active' => true,
            'is_demo' => false,
            'is_test' => true,
            'is_public_listed' => true,
        ]);

        $listedIds = Business::publicListed()->pluck('id')->all();

        $this->assertContains($bizActive->id, $listedIds);
        $this->assertNotContains($bizDemo->id, $listedIds);
        $this->assertNotContains($bizTest->id, $listedIds);
    }

    public function test_redirects_for_duplicate_solusi_pages(): void
    {
        $responseLaundry = $this->get('/solusi/kasir-laundry');
        $responseLaundry->assertStatus(301);
        $responseLaundry->assertRedirect('/solutions/laundry');

        $responseWorkshop = $this->get('/solusi/kasir-bengkel-kecil');
        $responseWorkshop->assertStatus(301);
        $responseWorkshop->assertRedirect('/solutions/workshop');
    }

    public function test_retained_vertical_solusi_pages_render_and_meet_seo_rules(): void
    {
        $verticals = [
            'kasir-warung' => 'Aplikasi Kasir Warung',
            'kasir-cafe-kecil' => 'Aplikasi Kasir Cafe',
            'kasir-kios' => 'Aplikasi Kasir Konter HP',
            'kasir-salon' => 'Aplikasi Kasir Salon',
            'kasir-barbershop' => 'Aplikasi Kasir Barbershop',
            'kasir-irt' => 'Aplikasi Usaha Rumahan',
        ];

        foreach ($verticals as $slug => $expectedKeyword) {
            $response = $this->get('/solusi/' . $slug);
            $response->assertStatus(200);

            $html = $response->getContent();

            // 1. Single H1
            preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1Matches);
            $this->assertCount(1, $h1Matches[0], "Page /solusi/{$slug} should have exactly 1 <h1>");
            $this->assertStringContainsString($expectedKeyword, $h1Matches[1][0]);

            // 2. Title max 60 chars
            preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatch);
            $this->assertNotEmpty($titleMatch);
            $titleText = trim(html_entity_decode(strip_tags($titleMatch[1])));
            $this->assertLessThanOrEqual(60, mb_strlen($titleText), "Title for /solusi/{$slug} must be <= 60 chars, got " . mb_strlen($titleText));

            // 3. No meta keywords
            $this->assertStringNotContainsString('<meta name="keywords"', $html);

            // 4. FAQPage schema
            $this->assertStringContainsString('"@type": "FAQPage"', $html);

            // 5. Word count >= 800 words
            $bodyText = strip_tags($html);
            $wordCount = str_word_count($bodyText);
            $this->assertGreaterThanOrEqual(800, $wordCount, "Word count for /solusi/{$slug} should be >= 800, got {$wordCount}");
        }
    }
}
