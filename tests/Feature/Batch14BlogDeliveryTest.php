<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class Batch14BlogDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private const BATCH14_SLUGS = [
        'aturan-upah-minimum-umkm-pasca-putusan-mk-pp-49-2025',
        'strategi-packing-gudang-harbolnas-12-12-alur-batch-shipping',
        'sop-stock-opname-tutup-buku-akhir-tahun-sak-emkm',
        'cara-menyusun-laporan-keuangan-sak-emkm-akhir-tahun',
        'manajemen-penagihan-piutang-b2b-akhir-tahun-anti-macet',
        'mitigasi-retur-cod-marketplace-musim-hujan-cek-whatsapp',
        'kalkulasi-biaya-kemasan-musim-hujan-ke-hpp-produk',
        'sinkronisasi-stok-kasir-pos-dan-marketplace-harbolnas-12-12',
        'evaluasi-biaya-operasional-dan-overhead-akhir-tahun-umkm',
        'rekonsiliasi-settlement-marketplace-akhir-tahun-toko-online',
        'sop-serah-terima-kasir-shift-malam-dan-brankas-libur-nataru',
        'strategi-promo-tukar-poin-loyalitas-member-akhir-tahun',
        'sop-penerimaan-barang-retur-dan-klaim-supplier-toko',
        'cara-menghitung-target-omzet-dan-bep-cabang-baru-2027',
        'panduan-surat-jalan-dan-delivery-order-pengiriman-grosir-b2b',
        'strategi-menu-engineering-kafe-resto-evaluasi-akhir-tahun',
        'sop-antrean-kasir-jam-sibuk-split-bill-struk-dapur-cepat',
        'panduan-perpanjangan-sertifikat-halal-dan-izin-pirt-umkm',
        'strategi-whatsapp-broadcast-tersegmentasi-promo-gajian-12-12',
        'manajemen-kasbon-karyawan-dan-potong-payroll-slip-gaji-digital',
    ];

    public function test_batch14_all_articles_exist_and_render_without_dashes(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        $emDash = chr(0xE2) . chr(0x80) . chr(0x94);
        $enDash = chr(0xE2) . chr(0x80) . chr(0x93);

        // Verify all 20 articles in Batch 14 exist and have ZERO em/en dashes
        foreach (self::BATCH14_SLUGS as $slug) {
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
            'aturan-upah-minimum-umkm-pasca-putusan-mk-pp-49-2025' => 'Aturan Upah Minimum UMKM Pasca Putusan MK',
            'strategi-packing-gudang-harbolnas-12-12-alur-batch-shipping' => 'Strategi Packing Gudang Harbolnas 12 12',
            'sop-stock-opname-tutup-buku-akhir-tahun-sak-emkm' => 'SOP Stock Opname Tutup Buku Akhir Tahun SAK EMKM',
            'sinkronisasi-stok-kasir-pos-dan-marketplace-harbolnas-12-12' => 'Sinkronisasi Stok Kasir POS dan Marketplace Harbolnas 12 12',
            'manajemen-kasbon-karyawan-dan-potong-payroll-slip-gaji-digital' => 'Manajemen Kasbon Karyawan dan Potong Payroll Slip Gaji',
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
