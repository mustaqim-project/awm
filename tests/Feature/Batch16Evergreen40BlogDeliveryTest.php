<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class Batch16Evergreen40BlogDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private const EVERGREEN_40_SLUGS = [
        // Pilar 1: Manajemen Keuangan & Pembukuan
        'cara-menghitung-biaya-tenaga-kerja-langsung-btkl-umkm',
        'cara-mengelola-dana-darurat-usaha-kecil-agar-tidak-bangkrut',
        'cara-menghitung-rasio-perputaran-modal-kerja-toko-ritel',
        'sop-pemisahan-uang-pribadi-dan-bisnis-umkm-solo-owner',
        'cara-menghitung-diskon-bertingkat-agar-margin-tidak-boncos',
        'cara-menyusun-laporan-arus-kas-metode-langsung-toko-jasa',
        'cara-menghitung-rasio-biaya-operasional-usaha-ritel-umkm',
        'cara-menghitung-target-laba-bersih-usaha-biaya-hidup-pemilik',

        // Pilar 2: Inventori, Stok & Manajemen Gudang
        'sop-penerimaan-barang-suplier-gudang-toko-bebas-selisih',
        'cara-mencegah-penyusutan-barang-dagangan-shrinkage-toko',
        'cara-menentukan-lead-time-pemesanan-barang-ke-distributor',
        'penerapan-sistem-fifo-gudang-makanan-bahan-baku-umkm',
        'cara-menghitung-biaya-pemesanan-barang-ordering-cost-toko',
        'sop-retur-barang-rusak-ke-suplier-dan-faktur-pengganti',
        'cara-menata-stok-barang-toko-sembako-kelontong-rapi',
        'cara-membuat-kode-sku-barang-toko-ritel-terstandar',

        // Pilar 3: Kasir, POS & Kontrol Fraud
        'sop-kasir-mengatasi-selisih-pembayaran-qris-debit-toko',
        'cara-mencegah-modus-pencurian-kasir-struk-gantung-pos',
        'sop-pelayanan-kasir-ramah-dan-cepat-antrean-panjang-toko',
        'cara-membaca-laporan-penjualan-kasir-harian-sales-summary',
        'sop-keamanan-laci-kasir-pos-pembatasan-uang-laci-kas',
        'cara-menangani-transaksi-gagal-qris-pelanggan-terpotong-saldo',
        'cara-mengaudit-transaksi-void-kasir-toko-pola-kecurangan',
        'sop-penggunaan-barcode-scanner-kasir-cepat-dan-akurat',

        // Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak
        'cara-mengurus-izin-edar-pirt-makanan-olahan-kemasan-umkm',
        'hak-dan-kewajiban-pemilik-usaha-karyawan-uu-cipta-kerja',
        'cara-mendaftarkan-bpjs-ketenagakerjaan-karyawan-toko-warung',
        'panduan-memilih-bentuk-usaha-ud-cv-pt-perorangan-umkm',
        'syarat-cara-bebas-pph-final-omzet-di-bawah-500-juta',
        'format-surat-penawaran-harga-quotation-resmi-b2b-umkm',
        'cara-menghitung-pph-pasal-21-karyawan-toko-harian-bulanan',
        'sop-surat-perjanjian-kerahasiaan-nda-resep-usaha-kuliner',

        // Pilar 5: CRM, Retensi Pelanggan & Pemasaran Omnichannel
        'cara-mengumpulkan-database-whatsapp-pelanggan-kasir-ramah',
        'strategi-harga-khusus-pelanggan-grosir-volume-belanja-toko',
        'cara-menghitung-rasio-retensi-pelanggan-toko-ritel-umkm',
        'sop-pengemasan-paket-pesanan-online-aman-ekspedisi-murah',
        'cara-membuat-pesan-pengingat-pembayaran-invoice-whatsapp',
        'strategi-omnichannel-toko-offline-dan-tiktok-live-stok-sinkron',
        'cara-membuat-kartu-nama-bisnis-digital-qr-code-katalog',
        'strategi-menangani-pelanggan-minta-diskon-di-luar-kebijakan',
    ];

    public function test_all_40_evergreen_articles_for_2026_11_19_exist_and_render_without_dashes(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        $emDash = chr(0xE2) . chr(0x80) . chr(0x94);
        $enDash = chr(0xE2) . chr(0x80) . chr(0x93);

        $this->assertCount(40, self::EVERGREEN_40_SLUGS);

        foreach (self::EVERGREEN_40_SLUGS as $slug) {
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

            // Verify cover image local asset
            $imgPath = public_path('images/blog/' . $slug . '.jpg');
            $this->assertFileExists($imgPath, "Cover image must exist for [{$slug}]");
            $this->assertGreaterThan(0, filesize($imgPath), "Cover image for [{$slug}] must not be empty");
        }

        // Deep HTTP Rendering verification on sample articles across all 5 pillars
        $samples = [
            'cara-menghitung-biaya-tenaga-kerja-langsung-btkl-umkm' => 'Biaya Tenaga Kerja Langsung',
            'sop-penerimaan-barang-suplier-gudang-toko-bebas-selisih' => 'Penerimaan Barang',
            'sop-kasir-mengatasi-selisih-pembayaran-qris-debit-toko' => 'Selisih Pembayaran Non Tunai',
            'cara-mengurus-izin-edar-pirt-makanan-olahan-kemasan-umkm' => 'Izin Edar PIRT',
            'cara-mengumpulkan-database-whatsapp-pelanggan-kasir-ramah' => 'Database WhatsApp Pelanggan',
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
