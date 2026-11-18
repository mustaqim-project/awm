<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class Batch15BlogDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private const BATCH15_SLUGS = [
        'panduan-coretax-djp-umkm-kode-billing-pph-final',
        'cara-menghitung-hpp-hampers-natal-tahun-baru-kuliner',
        'cara-menyusun-proyeksi-arus-kas-q1-awal-tahun-umkm',
        'strategi-bundling-dan-flash-sale-harbolnas-12-12-umkm',
        'sop-pengemasan-paket-musim-hujan-ekspedisi-anti-rusak',
        'format-evaluasi-kpi-karyawan-toko-dan-bonus-akhir-tahun',
        'manfaat-perpres-38-2026-kewirausahaan-nasional-dan-nib-oss',
        'sop-reservasi-meja-restoran-dan-kafe-libur-nataru',
        'cara-stock-opname-cepat-dengan-barcode-scanner-toko',
        'manajemen-stok-toko-fisik-dan-live-shopping-video-commerce',
        'cara-mencatat-biaya-barang-rusak-dan-kedaluwarsa-sak-emkm',
        'strategi-reaktivasi-pelanggan-dormant-whatsapp-akhir-tahun',
        'panduan-padisera-padi-umkm-dan-ekatalog-lkpp-v6',
        'sop-pengelolaan-kas-kembalian-dan-modal-awal-laci-kasir',
        'strategi-purchase-order-bahan-baku-antisipasi-libur-supplier',
        'menu-engineering-musim-hujan-substitusi-bahan-baku-kafe',
        'tata-cara-pembagian-dividen-dan-laba-ditahan-akhir-tahun-cv-pt',
        'kampanye-kedaluwarsa-poin-loyalitas-member-dorong-repeat-order',
        'audit-keamanan-kasir-pos-cegah-modus-void-pembatalan-palsu',
        'panduan-merekrut-karyawan-part-time-musiman-harbolnas-liburan',
    ];

    public function test_batch15_all_articles_exist_and_render_without_dashes(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        $emDash = chr(0xE2) . chr(0x80) . chr(0x94);
        $enDash = chr(0xE2) . chr(0x80) . chr(0x93);

        // Verify all 20 articles in Batch 15 exist and have ZERO em/en dashes
        foreach (self::BATCH15_SLUGS as $slug) {
            $post = Post::where('slug', $slug)->first();
            $this->assertNotNull($post, "Post with slug [{$slug}] must exist in database");
            $this->assertTrue($post->is_published, "Post [{$slug}] must have is_published = true");

            // Verify no em dash or en dash in database fields
            $this->assertStringNotContainsString($emDash, $post->title, "Post [{$slug}] title has em dash");
            $this->assertStringNotContainsString($enDash, $post->title, "Post [{$slug}] title has en dash");
            $this->assertStringNotContainsString($emDash, (string) $post->content, "Post [{$slug}] content has em dash");
            $this->assertStringNotContainsString($enDash, (string) $post->content, "Post [{$slug}] content has en dash");
            $this->assertStringNotContainsString($emDash, (string) $post->meta_description, "Post [{$slug}] meta_description has em dash");
            $this->assertStringNotContainsString($enDash, (string) $post->meta_description, "Post [{$slug}] meta_description has en dash");
        }

        // Deep HTTP Rendering verification on key sample articles across categories
        $samples = [
            'panduan-coretax-djp-umkm-kode-billing-pph-final' => 'Panduan Coretax DJP UMKM PPh Final',
            'cara-menghitung-hpp-hampers-natal-tahun-baru-kuliner' => 'Cara Menghitung HPP Hampers Natal Tahun Baru',
            'strategi-bundling-dan-flash-sale-harbolnas-12-12-umkm' => 'Strategi Bundling Flash Sale Harbolnas 12 12',
            'sop-reservasi-meja-restoran-dan-kafe-libur-nataru' => 'SOP Reservasi Meja Restoran Libur Nataru',
            'cara-stock-opname-cepat-dengan-barcode-scanner-toko' => 'Cara Stock Opname Cepat dengan Barcode Scanner',
            'audit-keamanan-kasir-pos-cegah-modus-void-pembatalan-palsu' => 'Audit Keamanan Kasir POS Cegah Modus Void',
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
