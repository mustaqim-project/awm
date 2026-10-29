<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoPromptSixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('seo:merge-articles', ['--apply' => true]);
    }
    public function test_merged_articles_redirect_301_to_pillars(): void
    {
        $redirectPairs = [
            '/blog/nib-gratis-tapi-bingung-mulai-dari-mana-ini-7-langkah-praktis-untuk-um' => '/blog/cara-membuat-nib',
            '/blog/umkm-wajib-nib-di-2026-aturan-baru-yang-perlu-kamu-tahu-sebelum-mulai' => '/blog/cara-membuat-nib',
            '/blog/5-kesalahan-saat-daftar-nib-yang-bikin-pengajuan-ditolak-oss' => '/blog/cara-membuat-nib',
            '/blog/usaha-rumahan-tanpa-nib-ini-risiko-tersembunyi-yang-bikin-rugi-puluhan' => '/blog/cara-membuat-nib',
            '/blog/stok-opname-warung-sederhana-cara-hitung-selisih-tanpa-drama-dalam-30' => '/blog/cara-stok-opname',
            '/blog/stok-opname-30-menit-selesai-tahapan-yang-bisa-kamu-tiru-untuk-warung' => '/blog/cara-stok-opname',
            '/blog/catatan-keuangan-warung-buku-tulis-vs-aplikasi-mana-yang-lebih-akurat' => '/blog/cara-pembukuan-warung',
            '/blog/kelola-keuangan-warung-dari-nol-7-kebiasaan-owner-yang-bikin-rekening' => '/blog/cara-pembukuan-warung',
            '/blog/rapikan-keuangan-warung-dalam-14-hari-step-by-step-untuk-owner-sibuk' => '/blog/cara-pembukuan-warung',
            '/blog/cara-cek-nib-sudah-terbit-atau-belum-tanpa-harus-ke-kantor-oss' => '/blog/cara-cek-nib',
        ];

        foreach ($redirectPairs as $source => $target) {
            $response = $this->get($source);
            $response->assertStatus(301);
            $response->assertRedirect($target);
        }
    }

    public function test_pillar_articles_render_and_meet_word_count(): void
    {
        $pillars = [
            'cara-membuat-nib',
            'cara-stok-opname',
            'cara-pembukuan-warung',
        ];

        foreach ($pillars as $slug) {
            $post = Post::where('slug', $slug)->first();
            $this->assertNotNull($post, "Pillar {$slug} must exist in database");

            $wordCount = str_word_count(strip_tags((string) $post->content));
            $this->assertGreaterThanOrEqual(1000, $wordCount, "Pillar {$slug} content word count should be >= 1000, got {$wordCount}");

            $response = $this->get('/blog/' . $slug);
            $response->assertStatus(200);

            $html = $response->getContent();
            // Verify single H1
            preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1Matches);
            $this->assertCount(1, $h1Matches[0], "Page /blog/{$slug} should have exactly 1 <h1>");

            // Verify title <= 60 chars
            preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatch);
            $this->assertNotEmpty($titleMatch);
            $titleText = trim(html_entity_decode(strip_tags($titleMatch[1])));
            $this->assertLessThanOrEqual(60, mb_strlen($titleText), "Title for /blog/{$slug} must be <= 60 chars, got " . mb_strlen($titleText));
        }
    }
}
