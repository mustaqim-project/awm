<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoPromptTenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('seo:merge-articles', ['--apply' => true]);
        $this->artisan('seo:create-hpp-bep-pillars', ['--apply' => true]);
        $this->artisan('seo:topic-clusters', ['--apply' => true]);
    }

    public function test_blog_article_footer_displays_author_and_updated_at_without_terverifikasi(): void
    {
        $post = Post::where('slug', 'cara-menghitung-hpp')->first();
        $this->assertNotNull($post);

        $response = $this->get('/blog/' . $post->slug);
        $response->assertStatus(200);

        $html = $response->getContent();

        // 1. Author and Updated date are present
        $this->assertStringContainsString('Penulis:', $html);
        $this->assertStringContainsString('Diperbarui:', $html);
        $this->assertStringContainsString($post->author_name ?? 'Tim Edukasi COOCA', $html);

        // 2. The word "terverifikasi" is removed from article trust claims
        $this->assertStringNotContainsString('Materi terverifikasi sesuai regulasi UMKM Indonesia', $html);
        $this->assertStringNotContainsString('Panduan UMKM terverifikasi', $html);
    }

    public function test_tax_and_legal_articles_display_official_regulatory_links(): void
    {
        $post = Post::where('slug', 'cara-membuat-nib')->first();
        $this->assertNotNull($post);

        $response = $this->get('/blog/' . $post->slug);
        $response->assertStatus(200);

        $html = $response->getContent();

        // Must display official government regulation citations
        $this->assertStringContainsString('Dasar Regulasi & Referensi Resmi Pemerintah RI', $html);
        $this->assertStringContainsString('https://oss.go.id', $html);
        $this->assertStringContainsString('https://peraturan.bpk.go.id', $html);
    }

    public function test_blog_schema_article_and_breadcrumb_are_valid(): void
    {
        $post = Post::where('slug', 'cara-menghitung-hpp')->first();
        $this->assertNotNull($post);

        $response = $this->get('/blog/' . $post->slug);
        $response->assertStatus(200);

        $html = $response->getContent();

        // Article schema
        $this->assertStringContainsString('"@type": "Article"', $html);
        $this->assertStringContainsString('"author":', $html);
        $this->assertStringContainsString('"datePublished":', $html);
        $this->assertStringContainsString('"dateModified":', $html);
        $this->assertStringContainsString('"name": "COOCA"', $html);

        // BreadcrumbList schema
        $this->assertStringContainsString('"@type": "BreadcrumbList"', $html);
    }

    public function test_organization_schema_on_homepage(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $html = $response->getContent();

        // Organization schema
        $this->assertStringContainsString('"@type": "Organization"', $html);
        $this->assertStringContainsString('"name": "COOCA"', $html);
        $this->assertStringContainsString('"alternateName": "COOCA Aplikasi Kasir & ERP UMKM"', $html);
        $this->assertStringContainsString('https://instagram.com/cooca.indonesia', $html);
        $this->assertStringContainsString('https://tiktok.com/@cooca.id', $html);
        $this->assertStringContainsString('https://linkedin.com/company/cooca-indonesia', $html);
        $this->assertStringContainsString('https://www.facebook.com/profile.php?id=61594556787735', $html);
    }

    public function test_brand_consistency_cooca_in_titles(): void
    {
        $urls = [
            '/' => 'COOCA',
            '/kontak' => 'COOCA',
            '/marketplace' => 'COOCA',
            '/terms' => 'COOCA',
            '/sitemap' => 'COOCA',
        ];

        foreach ($urls as $url => $brand) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $html = $response->getContent();

            preg_match('/<title\b[^>]*>(.*?)<\/title>/is', $html, $matches);
            $this->assertNotEmpty($matches, "Page {$url} must have a <title> tag");
            $title = $matches[1];

            $this->assertStringContainsString($brand, $title, "Title on {$url} ('{$title}') must contain '{$brand}'");
            $this->assertStringNotContainsString('Cooca', $title, "Title on {$url} ('{$title}') should not contain mixed-case 'Cooca'");
            $this->assertStringNotContainsString('COOCA.ID', $title, "Title on {$url} ('{$title}') should not contain 'COOCA.ID'");
        }
    }
}
