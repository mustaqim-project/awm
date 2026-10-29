<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoPromptSevenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('seo:create-hpp-bep-pillars', ['--apply' => true]);
    }

    public function test_hpp_and_bep_pillars_exist_and_meet_seo_requirements(): void
    {
        $pillars = [
            'cara-menghitung-hpp' => [
                'expected_title' => 'Cara Menghitung HPP: Rumus, Contoh & Kalkulator Gratis',
                'expected_calc' => '/kalkulator/hpp',
                'min_words' => 1800,
            ],
            'cara-menghitung-bep' => [
                'expected_title' => 'Cara Menghitung BEP: Rumus, Contoh & Kalkulator Gratis',
                'expected_calc' => '/kalkulator/bep',
                'min_words' => 1800,
            ],
        ];

        foreach ($pillars as $slug => $data) {
            $post = Post::where('slug', $slug)->first();
            $this->assertNotNull($post, "Pillar post /blog/{$slug} must exist.");

            $wordCount = str_word_count(strip_tags($post->content));
            $this->assertGreaterThanOrEqual(
                $data['min_words'],
                $wordCount,
                "Post {$slug} must have >= {$data['min_words']} words, got {$wordCount}"
            );

            $this->assertLessThanOrEqual(
                60,
                mb_strlen($post->seo_title ?: $post->title),
                "Post {$slug} title must be <= 60 characters."
            );

            $response = $this->get('/blog/' . $slug);
            $response->assertStatus(200);

            $html = $response->getContent();

            // 1. Exactly 1 H1
            preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $h1Matches);
            $this->assertCount(1, $h1Matches[0], "Page /blog/{$slug} must have exactly one <h1> tag.");

            // 2. Zero meta keywords
            $this->assertStringNotContainsString('name="keywords"', $html);

            // 3. Article schema
            $this->assertStringContainsString('"@type": "Article"', $html);

            // 4. FAQPage schema
            $this->assertStringContainsString('"@type": "FAQPage"', $html);

            // 5. BreadcrumbList schema
            $this->assertStringContainsString('"@type": "BreadcrumbList"', $html);

            // 6. Contains link to calculator
            $this->assertStringContainsString($data['expected_calc'], $html);
        }
    }

    public function test_child_articles_contain_backlinks_to_pillars(): void
    {
        $hppChildren = [
            'hpp-bakso-per-porsi',
            'hpp-usaha-frozen-food',
            'cara-hitung-hpp-konveksi',
            'biaya-overhead-usaha-kecil',
            'hpp-satu-porsi-nasi-goreng-cara-hitung-real-dalam-5-menit',
            'hpp-roti-kue-biaya-sering-tidak',
            'hpp-catering-per-porsi-vs-laba',
            'hpp-minuman-caf-vs-boba-mana-lebih-cepat-balik-modal',
            'cara-menghitung-hpp-kopi',
        ];

        foreach ($hppChildren as $childSlug) {
            $post = Post::where('slug', $childSlug)->first();
            if ($post) {
                $this->assertStringContainsString(
                    '/blog/cara-menghitung-hpp',
                    $post->content,
                    "Child post {$childSlug} must contain a backlink to /blog/cara-menghitung-hpp"
                );
            }
        }

        $bepChildren = [
            'bep-multi-produk',
            'bep-warung-kopi-berapa-cangkir-per-hari-untuk-balik-modal',
            'hitung-bep-cafe-kecil-15-meja',
        ];

        foreach ($bepChildren as $childSlug) {
            $post = Post::where('slug', $childSlug)->first();
            if ($post) {
                $this->assertStringContainsString(
                    '/blog/cara-menghitung-bep',
                    $post->content,
                    "Child post {$childSlug} must contain a backlink to /blog/cara-menghitung-bep"
                );
            }
        }
    }
}

