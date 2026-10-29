<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeoH1AndMetaKeywordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_has_exactly_one_h1_and_no_meta_keywords(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        // 1. Must NOT have <meta name="keywords">
        $response->assertDontSee('<meta name="keywords"', false);

        // 2. Must have exactly one <h1>
        preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/si', $response->getContent(), $matches);
        $this->assertCount(1, $matches[0], 'Homepage should have exactly one <h1> tag.');
    }

    public function test_blog_post_strips_duplicate_h1_and_has_no_meta_keywords(): void
    {
        $post = Post::create([
            'title' => 'Panduan HPP Kuliner Praktis',
            'slug' => 'panduan-hpp-kuliner-praktis',
            'category' => 'HPP & Harga Jual',
            'excerpt' => 'Panduan lengkap cara menghitung HPP kuliner untuk usaha kecil.',
            'content' => "<h1>Panduan HPP Kuliner Praktis</h1>\n<p>Paragraf pembuka.</p>\n<h1>Heading Tambahan Yang Harusnya H2</h1>\n<p>Paragraf kedua.</p>",
            'author_name' => 'Tim Edukasi COOCA',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->get('/blog/' . $post->slug);
        $response->assertOk();

        // 1. Must NOT have <meta name="keywords">
        $response->assertDontSee('<meta name="keywords"', false);

        // 2. Must have exactly one <h1> on the whole page (the hero title)
        preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/si', $response->getContent(), $matches);
        $this->assertCount(1, $matches[0], 'Blog post page should have exactly one <h1> tag.');
        $this->assertStringContainsString('Panduan HPP Kuliner Praktis', strip_tags($matches[0][0]));

        // 3. The secondary <h1> in content should be demoted to <h2>
        $response->assertSee('<h2>Heading Tambahan Yang Harusnya H2</h2>', false);
        $response->assertDontSee('<h1>Heading Tambahan Yang Harusnya H2</h1>', false);
    }

    public function test_seo_check_h1_artisan_command_passes_with_zero_violations(): void
    {
        Post::create([
            'title' => 'Cara Hitung BEP Usaha',
            'slug' => 'cara-hitung-bep-usaha',
            'category' => 'Keuangan',
            'excerpt' => 'Cara menghitung BEP usaha kecil.',
            'content' => "<p>Isi artikel tanpa h1 dobel.</p>",
            'author_name' => 'Tim Edukasi COOCA',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $exitCode = Artisan::call('seo:check-h1', ['--limit' => 10]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode, 'Artisan command seo:check-h1 should return 0 exit code.');
        $this->assertStringContainsString('100% COMPLIANT', $output);
        $this->assertStringContainsString('0 (NOL)', $output);
    }

    public function test_key_public_pages_have_single_h1_and_no_meta_keywords(): void
    {
        $pages = [
            '/kalkulator',
            '/kalkulator/hpp',
            '/solusi/kasir-warung',
            '/sitemap',
        ];

        foreach ($pages as $path) {
            $response = $this->get($path);
            $response->assertOk();

            // No meta keywords
            $this->assertFalse(
                (bool) preg_match('/<meta\s+name=[\x22\x27]keywords[\x22\x27]/i', $response->getContent()),
                "Page {$path} should not have meta keywords."
            );

            // Exactly 1 H1
            preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/si', $response->getContent(), $matches);
            $this->assertCount(1, $matches[0], "Page {$path} should have exactly 1 <h1> tag.");
        }
    }
}
