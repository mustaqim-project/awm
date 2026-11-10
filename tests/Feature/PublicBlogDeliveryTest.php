<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicBlogDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_blog_index_renders_and_contains_article(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        $response = $this->get('/blog');
        $response->assertOk();
        $response->assertSee('Tren Bisnis 2027 dan Peluang Usaha Menguntungkan bagi UMKM', false);
    }

    public function test_public_can_view_published_tren_bisnis_2027_article(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        $post = Post::where('slug', 'tren-bisnis-2027-dan-peluang-usaha-umkm')->first();
        $this->assertNotNull($post, 'Post with slug tren-bisnis-2027-dan-peluang-usaha-umkm must exist in database');
        $this->assertTrue($post->is_published);

        // 1. Database level: Article content, title, excerpt must have zero em dash or en dash
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), $post->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), $post->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), (string) $post->content);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), (string) $post->content);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), (string) $post->meta_description);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), (string) $post->meta_description);

        $response = $this->get('/blog/tren-bisnis-2027-dan-peluang-usaha-umkm');
        $response->assertOk();

        // 2. Heading H1 and Meta
        $response->assertSee('Tren Bisnis 2027 dan Peluang Usaha Menguntungkan bagi UMKM', false);
        $response->assertSee('Lanskap Pasar dan Pergeseran Perilaku Belanja Konsumen Menuju 2027', false);
        $response->assertSee('5 Sektor Tren Bisnis 2027 dan Peluang Usaha Paling Menjanjikan', false);
        $response->assertSee('Transformasi dari Operasional Toko Manual Menuju Otomasi Terpadu', false);
        $response->assertSee('Simulasi Keuangan: Toko Konvensional vs Bisnis Adaptif 2027', false);
        $response->assertSee('Roadmap Aksi Mempersiapkan Usaha Anda Menyambut Tahun 2027', false);
        $response->assertSee('Pertanyaan Seputar Tren Bisnis 2027 dan Peluang Usaha (FAQ)', false);

        // 3. All 5 contextual internal links
        $response->assertSee('https://cooca.id/blog/cara-menyusun-anggaran-operasional-toko-umkm', false);
        $response->assertSee('https://cooca.id/blog/strategi-live-shopping-affiliate-tiktok-tokopedia-umkm', false);
        $response->assertSee('https://cooca.id/blog/cara-menyusun-sop-usaha-kecil-agar-bisnis-mandiri', false);
        $response->assertSee('https://cooca.id/blog/antisipasi-kenaikan-ump-2027-dan-efisiensi-biaya-gaji-umkm', false);
        $response->assertSee('https://cooca.id/blog/cara-menghitung-cash-conversion-cycle-umkm', false);

        // 4. Schema.org JSON-LD
        $response->assertSee('https://schema.org', false);
        $response->assertSee('FAQPage', false);
        $response->assertSee('Apa sektor bisnis yang paling menjanjikan untuk UMKM pada tahun 2027?', false);

        // 5. Rendered Article Reader Container must be free of em dash and en dash
        $html = $response->getContent();
        if (preg_match('/<article[^>]*class="[^"]*article-prose[^"]*"[^>]*>(.*?)<\/article>/s', $html, $articleMatch)) {
            $articleHtml = $articleMatch[1];
            $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), $articleHtml, 'Article prose must not contain em dash (U+2014)');
            $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), $articleHtml, 'Article prose must not contain en dash (U+2013)');
        }
    }
}
