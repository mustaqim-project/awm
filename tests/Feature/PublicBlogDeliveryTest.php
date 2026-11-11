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

        $latestPost = Post::published()->latest('published_at')->first();
        $this->assertNotNull($latestPost);
        $response->assertSee($latestPost->title, false);

        // Verify search can find specific article
        $searchResponse = $this->get('/blog?q=Tren+Bisnis+2027');
        $searchResponse->assertOk();
        $searchResponse->assertSee('Tren Bisnis 2027 dan Peluang Usaha Menguntungkan bagi UMKM', false);
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

    public function test_public_can_view_published_rekonsiliasi_11_11_article(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        $post = Post::where('slug', 'strategi-rekonsiliasi-keuangan-pasca-11-11-umkm')->first();
        $this->assertNotNull($post, 'Post with slug strategi-rekonsiliasi-keuangan-pasca-11-11-umkm must exist in database');
        $this->assertTrue($post->is_published);

        // 1. Database level: Article content, title, excerpt must have zero em dash or en dash
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), $post->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), $post->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), (string) $post->content);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), (string) $post->content);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), (string) $post->meta_description);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), (string) $post->meta_description);

        $response = $this->get('/blog/strategi-rekonsiliasi-keuangan-pasca-11-11-umkm');
        $response->assertOk();

        // 2. Heading H1 and Meta
        $response->assertSee('Strategi Rekonsiliasi Keuangan Toko Online Pasca Pesta Diskon 11.11', false);
        $response->assertSee('Mengenal Anatomi Jebakan Omzet Semu pada Festival Belanja Tanggal Kembar', false);
        $response->assertSee('4 Tahap Menjalankan Strategi Rekonsiliasi Keuangan Toko Online', false);
        $response->assertSee('Simulasi Keuangan: Omzet Nominal Dashboard vs Saldo Kas Bersih Riil', false);
        $response->assertSee('Mitigasi Lonjakan Retur COD dan Penyelamatan Nilai Barang Rusak', false);
        $response->assertSee('Cara Mengamankan Likuiditas Arus Kas Menjelang Puncak Harbolnas 12.12', false);
        $response->assertSee('Pertanyaan Seputar Rekonsiliasi Keuangan Toko Online Pasca 11.11 (FAQ)', false);

        // 3. All 5 contextual internal links
        $response->assertSee('https://cooca.id/blog/mengelola-jeda-pencairan-saldo-marketplace-arus-kas', false);
        $response->assertSee('https://cooca.id/blog/cara-mengatasi-selisih-timbangan-paket-denda-kurir', false);
        $response->assertSee('https://cooca.id/blog/cara-mencegah-retur-cod-paket-fiktif-marketplace', false);
        $response->assertSee('https://cooca.id/blog/sop-pick-and-pack-gudang-toko-online-harbolnas', false);
        $response->assertSee('https://cooca.id/blog/cara-membuat-proyeksi-kas-mingguan-umkm', false);

        // 4. Schema.org JSON-LD
        $response->assertSee('https://schema.org', false);
        $response->assertSee('FAQPage', false);
        $response->assertSee('Apa itu strategi rekonsiliasi keuangan toko online pasca kampanye promo?', false);

        // 5. Rendered Article Reader Container must be free of em dash and en dash
        $html = $response->getContent();
        if (preg_match('/<article[^>]*class="[^"]*article-prose[^"]*"[^>]*>(.*?)<\/article>/s', $html, $articleMatch)) {
            $articleHtml = $articleMatch[1];
            $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), $articleHtml, 'Article prose must not contain em dash (U+2014)');
            $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), $articleHtml, 'Article prose must not contain en dash (U+2013)');
        }
    }

    public function test_public_can_view_published_batch9_articles_without_dashes(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        // Test Article 392
        $post392 = Post::where('slug', 'evaluasi-net-profit-margin-pasca-11-11-umkm')->first();
        $this->assertNotNull($post392, 'Post with slug evaluasi-net-profit-margin-pasca-11-11-umkm must exist');
        $this->assertTrue($post392->is_published);

        // Zero em dash / en dash in database
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), $post392->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), $post392->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), (string) $post392->content);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), (string) $post392->content);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), (string) $post392->meta_description);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), (string) $post392->meta_description);

        $response392 = $this->get('/blog/evaluasi-net-profit-margin-pasca-11-11-umkm');
        $response392->assertOk();
        $response392->assertSee('Evaluasi Net Profit Margin Pasca 11 11 untuk UMKM: Cara Bedakan Omzet dan Laba Riil', false);
        $response392->assertSee('Evaluasi Net Profit Margin Pasca 11 11 Sangat Krusial bagi Bisnis', false);
        $response392->assertSee('https://cooca.id/blog/evaluasi-margin-keuntungan-kategori-produk', false);
        $response392->assertSee('https://schema.org', false);
        $response392->assertSee('FAQPage', false);

        // Test Article 395 (Tax deadline)
        $post395 = Post::where('slug', 'batas-akhir-setor-pph-final-15-november-2026-umkm')->first();
        $this->assertNotNull($post395, 'Post with slug batas-akhir-setor-pph-final-15-november-2026-umkm must exist');
        $this->assertTrue($post395->is_published);

        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), $post395->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), $post395->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), (string) $post395->content);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), (string) $post395->content);

        $response395 = $this->get('/blog/batas-akhir-setor-pph-final-15-november-2026-umkm');
        $response395->assertOk();
        $response395->assertSee('Batas Akhir Setor PPh Final 15 November 2026 dan Panduan Bayar Pajak UMKM 0,5 Persen', false);
        $response395->assertSee('https://cooca.id/blog/panduan-lengkap-pph-final-umkm-setengah-persen', false);
    }
}
