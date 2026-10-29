<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoPromptEightTest extends TestCase
{
    use RefreshDatabase;

    public function test_commercial_pages_render_and_meet_seo_requirements(): void
    {
        $pages = [
            '/aplikasi-kasir-gratis' => [
                'expected_keyword' => 'aplikasi kasir gratis',
                'expected_title_part' => 'Aplikasi Kasir Gratis Selamanya',
                'min_words' => 900,
                'content_checks' => ['1 Toko', '50 Produk', '100 Transaksi'],
            ],
            '/aplikasi-kasir-android' => [
                'expected_keyword' => 'aplikasi kasir android',
                'expected_title_part' => 'Aplikasi Kasir Android',
                'min_words' => 900,
                'content_checks' => ['/download/apk', 'Bluetooth', 'Android 7.0'],
            ],
            '/aplikasi-kasir-offline' => [
                'expected_keyword' => 'aplikasi kasir offline',
                'expected_title_part' => 'Aplikasi Kasir Offline',
                'min_words' => 900,
                'content_checks' => ['IndexedDB', 'Antrean Sinkronisasi', 'QRIS dinamis'],
            ],
            '/solusi/kasir-apotek' => [
                'expected_keyword' => 'aplikasi kasir apotek',
                'expected_title_part' => 'Aplikasi Kasir Apotek',
                'min_words' => 900,
                'content_checks' => ['nomor batch', 'expired', 'FEFO', '<!-- KONFIRMASI:'],
            ],
            '/solusi/aplikasi-catering' => [
                'expected_keyword' => 'aplikasi catering',
                'expected_title_part' => 'Aplikasi Catering',
                'min_words' => 900,
                'content_checks' => ['HPP', 'DP', 'prasmanan', '/blog/hpp-catering-per-porsi-vs-laba'],
            ],
        ];

        foreach ($pages as $url => $data) {
            $response = $this->get($url);
            $response->assertStatus(200);

            $html = $response->getContent();

            // 1. Single H1 with keyword
            preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $h1Matches);
            $this->assertCount(1, $h1Matches[0], "Page {$url} must have exactly one <h1> tag.");
            $h1Text = strip_tags($h1Matches[1][0]);
            $this->assertStringContainsStringIgnoringCase(
                $data['expected_keyword'],
                $h1Text,
                "H1 on {$url} must contain target keyword '{$data['expected_keyword']}'."
            );

            // 2. Title length <= 60 chars
            preg_match('/<title\b[^>]*>(.*?)<\/title>/is', $html, $titleMatch);
            $this->assertNotEmpty($titleMatch, "Page {$url} must have a <title> tag.");
            $titleText = trim(html_entity_decode(strip_tags($titleMatch[1])));
            $this->assertLessThanOrEqual(
                60,
                mb_strlen($titleText),
                "Title for {$url} must be <= 60 chars, got " . mb_strlen($titleText) . " ('{$titleText}')"
            );
            $this->assertStringContainsString($data['expected_title_part'], $titleText);

            // 3. Meta description 120 - 160 chars
            preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/is', $html, $descMatch);
            $this->assertNotEmpty($descMatch, "Page {$url} must have a meta description.");
            $descLength = mb_strlen(html_entity_decode(trim($descMatch[1])));
            $this->assertGreaterThanOrEqual(120, $descLength, "Meta description for {$url} should be >= 120 chars, got {$descLength}");
            $this->assertLessThanOrEqual(160, $descLength, "Meta description for {$url} should be <= 160 chars, got {$descLength}");

            // 4. Zero meta keywords
            $this->assertStringNotContainsString('name="keywords"', $html);

            // 5. Schema SoftwareApplication & FAQPage
            $this->assertStringContainsString('"@type": "SoftwareApplication"', $html);
            $this->assertStringContainsString('"@type": "FAQPage"', $html);

            // 6. Word count >= 900
            $wordCount = str_word_count(strip_tags($html));
            $this->assertGreaterThanOrEqual(
                $data['min_words'],
                $wordCount,
                "Page {$url} must have at least {$data['min_words']} words, got {$wordCount}"
            );

            // 7. Specific content checks
            foreach ($data['content_checks'] as $check) {
                $this->assertStringContainsString(
                    $check,
                    $html,
                    "Page {$url} must contain '{$check}'"
                );
            }
        }
    }

    public function test_commercial_pages_are_in_sitemap(): void
    {
        $sitemapContent = file_get_contents(public_path('sitemap.xml'));

        $commercialUrls = [
            'https://cooca.id/aplikasi-kasir-gratis',
            'https://cooca.id/aplikasi-kasir-android',
            'https://cooca.id/aplikasi-kasir-offline',
            'https://cooca.id/solusi/kasir-apotek',
            'https://cooca.id/solusi/aplikasi-catering',
        ];

        foreach ($commercialUrls as $url) {
            $this->assertStringContainsString(
                "<loc>{$url}</loc>",
                $sitemapContent,
                "Sitemap must contain {$url}"
            );
        }
    }
}

