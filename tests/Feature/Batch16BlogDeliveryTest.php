<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class Batch16BlogDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private const BATCH16_SLUGS = [
        'cara-menghitung-hpp-teh-artisan-light-milk-tea',
        'aturan-biaya-mdr-qris-dilarang-dibebankan-konsumen',
        'aturan-upah-umkm-pasca-penetapan-ump-2027',
    ];

    public function test_batch16_all_articles_exist_and_render_without_dashes(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        $emDash = chr(0xE2) . chr(0x80) . chr(0x94);
        $enDash = chr(0xE2) . chr(0x80) . chr(0x93);

        // Verify all Batch 16 articles exist, are published, and have ZERO em/en dashes
        foreach (self::BATCH16_SLUGS as $slug) {
            $post = Post::where('slug', $slug)->first();
            $this->assertNotNull($post, "Post with slug [{$slug}] must exist in database");
            $this->assertTrue((bool) $post->is_published, "Post [{$slug}] must have is_published = true");

            // Verify no em dash or en dash in database fields
            $this->assertStringNotContainsString($emDash, $post->title, "Post [{$slug}] title has em dash");
            $this->assertStringNotContainsString($enDash, $post->title, "Post [{$slug}] title has en dash");
            $this->assertStringNotContainsString($emDash, (string) $post->content, "Post [{$slug}] content has em dash");
            $this->assertStringNotContainsString($enDash, (string) $post->content, "Post [{$slug}] content has en dash");
            $this->assertStringNotContainsString($emDash, (string) $post->meta_description, "Post [{$slug}] meta_description has em dash");
            $this->assertStringNotContainsString($enDash, (string) $post->meta_description, "Post [{$slug}] meta_description has en dash");

            // Verify cover image local asset exists
            $localImagePath = public_path("images/blog/{$slug}.jpg");
            $this->assertFileExists($localImagePath, "Cover image file for [{$slug}] must exist in public/images/blog/");
        }

        // Deep HTTP Rendering verification on all 3 articles
        $samples = [
            'cara-menghitung-hpp-teh-artisan-light-milk-tea' => 'Cara Menghitung HPP Teh Artisan Light Milk Tea',
            'aturan-biaya-mdr-qris-dilarang-dibebankan-konsumen' => 'Aturan Biaya MDR QRIS Dilarang Dibebankan Konsumen',
            'aturan-upah-umkm-pasca-penetapan-ump-2027' => 'Aturan Upah UMKM Pasca Penetapan UMP 2027',
        ];

        foreach ($samples as $slug => $expectedKeywordSubstring) {
            $response = $this->get("/blog/{$slug}");
            $response->assertOk();
            $response->assertSee($expectedKeywordSubstring, false);
            $response->assertSee('https://schema.org', false);
            $response->assertSee('FAQPage', false);
            $response->assertSee('Article', false);
            $response->assertDontSee($emDash, false);
            $response->assertDontSee($enDash, false);
        }
    }
}
