<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class Batch15Evergreen40BlogDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private const EVERGREEN_40_SLUGS = [
        // Pilar 1: Manajemen Keuangan & Pembukuan
        'cara-menghitung-contribution-margin-produk-umkm',
        'analisis-rasio-likuiditas-usaha-kecil-current-quick-ratio',
        'cara-membuat-anggaran-operasional-tahunan-budgeting-umkm',
        'manajemen-piutang-usaha-dagang-aging-schedule-umur-piutang',
        'cara-menghitung-depresiasi-aset-tetap-usaha-garis-lurus',
        'cara-mencatat-modal-awal-usaha-dan-ekuitas-pemilik-umkm',
        'cara-menghitung-roi-investasi-alat-dan-mesin-usaha-umkm',
        'sop-pencatatan-reimbursement-biaya-operasional-karyawan',

        // Pilar 2: Inventori, Stok & Manajemen Gudang
        'rumus-economic-order-quantity-eoq-efisiensi-kulakan-toko',
        'analisis-abc-persediaan-barang-toko-klasifikasi-stok',
        'cara-menghitung-inventory-turnover-ratio-perputaran-stok-toko',
        'sop-karantina-barang-rusak-dan-kedaluwarsa-di-gudang-toko',
        'cara-menata-layout-gudang-toko-kecil-agar-cepat-picking',
        'sop-transfer-stok-antar-cabang-toko-dan-surat-jalan-mutasi',
        'cara-menghitung-buffer-stock-bahan-baku-kuliner-dan-manufaktur',
        'metode-barcode-labeling-inventori-gudang-sku-kode-rak',

        // Pilar 3: Kasir, POS & Kontrol Fraud
        'sop-blind-cash-drop-kasir-untuk-mencegah-manipulasi-laci-uang',
        'pencegahan-fraud-kasir-metode-phantom-transaction-dan-void',
        'sop-penanganan-selisih-uang-kasir-kurang-dan-lebih-closing',
        'alur-transaksi-split-payment-kasir-pos-tunai-dan-qris',
        'sop-pembatalan-pesanan-void-kasir-wajib-persetujuan-supervisor',
        'cara-audit-log-kasir-pos-mendeteksi-aktivitas-mencurigakan',
        'sop-kas-kecil-kasir-modal-awal-laci-kas-pecahan-uang-kembalian',
        'panduan-integrasi-edc-bank-kasir-rekonsiliasi-slip-gesek',

        // Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak
        'cara-memilih-kode-kbli-usaha-umkm-di-oss-rba-agar-tidak-salah-izin',
        'syarat-pirt-pangan-olahan-risiko-rendah-dinas-kesehatan-spp-irt',
        'tata-cara-pencatatan-pajak-peredaran-bruto-umkm-sesuai-pp-55',
        'cara-membuat-perjanjian-kerja-waktu-tertentu-pkwtt-pkwt-karyawan-toko',
        'prosedur-sertifikasi-halal-sjph-umkm-manual-sistem-jaminan-produk',
        'cara-aktivasi-akun-coretax-djp-bagi-pengusaha-badan-dan-orang-pribadi',
        'aturan-nota-penjualan-berkekuatan-hukum-sebagai-bukti-transaksi',
        'pembukuan-sederhana-sak-emkm-untuk-usaha-kecil-tiga-laporan-wajib',

        // Pilar 5: CRM, Retensi Pelanggan & Omnichannel
        'cara-menghitung-customer-lifetime-value-clv-usaha-ritel',
        'strategi-membership-tier-loyalitas-pelanggan-toko-offline',
        'alur-follow-up-keranjang-terbengkalai-whatsapp-katalog-toko',
        'sop-penanganan-ulasan-negatif-di-google-maps-dan-media-sosial',
        'cara-membuat-program-referral-pelanggan-bawa-teman-toko-ritel',
        'segmentasi-database-pelanggan-berdasarkan-rfm-recency-frequency-monetary',
        'cara-membuat-broadcast-whatsapp-resmi-tidak-diblokir-pelanggan',
        'strategi-cross-selling-dan-up-selling-di-kasir-ritel-dan-fnb',
    ];

    public function test_all_40_evergreen_articles_exist_and_render_without_dashes(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        $emDash = chr(0xE2) . chr(0x80) . chr(0x94);
        $enDash = chr(0xE2) . chr(0x80) . chr(0x93);

        $this->assertCount(40, self::EVERGREEN_40_SLUGS);

        foreach (self::EVERGREEN_40_SLUGS as $slug) {
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

            // Verify cover image
            $imgPath = public_path('images/blog/' . $slug . '.jpg');
            $this->assertFileExists($imgPath, "Cover image must exist for [{$slug}]");
            $this->assertGreaterThan(0, filesize($imgPath), "Cover image for [{$slug}] must not be empty");
        }

        // Deep HTTP Rendering verification on key sample articles across all 5 pillars
        $samples = [
            'cara-menghitung-contribution-margin-produk-umkm' => 'Contribution Margin',
            'rumus-economic-order-quantity-eoq-efisiensi-kulakan-toko' => 'Economic Order Quantity',
            'sop-blind-cash-drop-kasir-untuk-mencegah-manipulasi-laci-uang' => 'Blind Cash Drop',
            'cara-memilih-kode-kbli-usaha-umkm-di-oss-rba-agar-tidak-salah-izin' => 'KBLI',
            'cara-menghitung-customer-lifetime-value-clv-usaha-ritel' => 'Customer Lifetime Value',
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
