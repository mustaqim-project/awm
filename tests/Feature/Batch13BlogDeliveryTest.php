<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class Batch13BlogDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private const BATCH13_SLUGS = [
        'billing-coretax-djp-kadaluarsa-batas-15-november',
        'sanksi-pengawasan-halal-bpjph-2026-self-declare',
        'kebijakan-mdr-qris-terbaru-usaha-mikro-batas-500-ribu',
        'kalkulasi-hpp-hampers-natal-tahun-baru-bundling-pre-order',
        'antisipasi-banjir-kelembapan-gudang-logistik-harbolnas-12-12',
        'sop-kasir-blind-cash-drop-tutup-shift-jam-sibuk',
        'strategi-blast-whatsapp-bisnis-resmi-promo-akhir-tahun-anti-banned',
        'manajemen-aging-piutang-usaha-b2b-penagihan-tutup-buku-akhir-tahun',
        'sop-alokasi-stok-gudang-omnichannel-flash-sale-12-12',
        'sop-karyawan-paruh-waktu-musiman-toko-ritel-libur-akhir-tahun',
        'penyesuaian-master-data-produk-tarif-ppn-12-persen-kasir-pos',
        'cara-rekonsiliasi-arus-kas-qris-edc-transfer-bank-toko-ritel',
        'strategi-upselling-paket-bundling-menu-musim-hujan-kafe-kuliner',
        'mitigasi-retur-paket-cod-gagal-kirim-harbolnas-12-12-musim-hujan',
        'panduan-penyusunan-laporan-laba-rugi-umkm-akhir-tahun-sak-emkm',
        'cara-membangun-program-loyalitas-member-poin-retensi-pelanggan-toko',
        'sop-penerimaan-bahan-baku-kuliner-musim-hujan-cek-mutu-surat-jalan',
        'pengajuan-sisa-plafon-kur-2026-sektor-produksi-tutup-tahun',
        'hak-akses-role-based-rbac-kasir-supervisor-cegah-fraud-toko',
        'aplikasi-kasir-pos-multi-gudang-terintegrasi-toko-fisik-dan-online',
    ];

    public function test_batch13_all_articles_exist_and_render_without_dashes(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        $emDash = chr(0xE2) . chr(0x80) . chr(0x94);
        $enDash = chr(0xE2) . chr(0x80) . chr(0x93);

        // Verify all 20 articles in Batch 13 exist and have ZERO em/en dashes
        foreach (self::BATCH13_SLUGS as $slug) {
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
            'billing-coretax-djp-kadaluarsa-batas-15-november' => 'Billing Coretax DJP Kadaluarsa Batas 15 November',
            'kebijakan-mdr-qris-terbaru-usaha-mikro-batas-500-ribu' => 'Kebijakan MDR QRIS Terbaru Usaha Mikro Batas 500 Ribu',
            'kalkulasi-hpp-hampers-natal-tahun-baru-bundling-pre-order' => 'Kalkulasi HPP Hampers Natal Tahun Baru Bundling Pre Order',
            'antisipasi-banjir-kelembapan-gudang-logistik-harbolnas-12-12' => 'Antisipasi Banjir Kelembapan Gudang Logistik Harbolnas 12 12',
            'aplikasi-kasir-pos-multi-gudang-terintegrasi-toko-fisik-dan-online' => 'Aplikasi Kasir POS Multi Gudang Terintegrasi Toko Fisik dan Online',
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
