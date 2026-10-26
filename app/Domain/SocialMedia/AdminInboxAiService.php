<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\Ai\Providers\AiProviderInterface;
use App\Domain\Ai\Providers\AiProviderManager;
use App\Events\LiveChatMessageSent;
use App\Models\InboxThreadTakeover;
use App\Models\SocialMediaComment;
use App\Models\SystemSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class AdminInboxAiService
{
    public const SETTING_GROUP = 'admin_inbox_ai';

    public const DEFAULT_SYSTEM_INFO = <<<TXT
[PROFIL COOCA INDONESIA & BUSINESS OPERATING SYSTEM]
COOCA adalah platform SaaS Business Operating System (BOS) All-in-One yang dirancang khusus untuk memajukan UMKM Indonesia. COOCA menghubungkan seluruh operasional bisnis mulai dari kasir POS, manajemen inventori multi-gudang, kalkulasi HPP resep otomatis (BOM), pembukuan keuangan real-time, channel penjualan omnichannel (WhatsApp, Toko Online Digital, Shopee, Tokopedia, TikTok Shop), HRM & absensi GPS, hingga asisten AI bisnis dalam satu sistem terpadu tanpa software terpisah dan dengan sinkronisasi 0 detik delay.

[KATALOG RESMI PAKET & HARGA BERLANGGANAN]
(Sumber Data Tunggal: CMS Billing Packages Platform COOCA)
- Uji Coba Gratis (Free Trial): 14 Hari Akses Penuh ke semua fitur tanpa perlu kartu kredit dan tanpa komitmen via [Pendaftaran Free Trial 14 Hari](https://cooca.id/register).
- Paket Solo (Gratis): Cocok untuk usaha perorangan yang baru merintis pencatatan transaksi kasir sederhana.
- Paket Standard Plan:
  * Harga Bulanan: Rp 39.000 / bulan
  * Harga Tahunan: Rp 390.000 / tahun (Promo: Hemat 2 Bulan)
  * Target Segmen: UMKM Pemula (toko kelontong, kafe kecil, bengkel rumahan) dengan kuota hingga 1.000 transaksi kasir/bulan, 1 outlet/toko, dan master data produk dasar.
- Paket Premium Plan (Paling Populer & Rekomendasi):
  * Harga Bulanan: Rp 89.000 / bulan
  * Harga Tahunan: Rp 890.000 / tahun (Promo: Hemat 2 Bulan)
  * Target Segmen: Bisnis berkembang & multi-cabang (hingga 3 outlet cabang/gudang), kuota transaksi kasir tanpa batas, manajemen harga per cabang (Multi-Branch Pricing), sinkronisasi Omnichannel Marketplace (Shopee, Tokopedia, TikTok Shop), integrasi WhatsApp Gateway, dan analitik bisnis AI.
- Paket Prestige Plan (Enterprise & Waralaba):
  * Harga Bulanan: Rp 199.000 / bulan
  * Harga Tahunan: Rp 1.990.000 / tahun (Promo: Hemat 2 Bulan)
  * Target Segmen: Jaringan korporat, waralaba, dan distributor besar dengan outlet tanpa batas, kuota penyimpanan cloud besar, prioritas bantuan teknis 24/7, integrasi API kustom, dan SLA 99.9%.

[KUOTA TOKEN AI & PENYIMPANAN CLOUD (STORAGE)]
- Kuota Token AI Bawaan: 10.000.000 token / bulan sudah termasuk dalam paket langganan aktif.
- Top-Up Token AI Tambahan: Rp 50.000 per 1.000.000 token (dapat dibeli instan secara mandiri di dashboard).
- Kapasitas Dasar Storage Owner: 3 GB ruang penyimpanan cloud gratis untuk seluruh cabang.
- Top-Up Storage Tambahan: Rp 50.000 per 1 GB (bersifat permanen dan diakumulasikan).
- Metode Pembayaran Resmi: QRIS Dinamis Otomatis (GoPay, OVO, ShopeePay, DANA, BCA Mobile, Livin, BRImo) dan Virtual Account Bank (BCA, Mandiri, BNI, BRI, Permata) dengan verifikasi otomatis detik itu juga tanpa perlu kirim bukti transfer manual.

[PANDUAN SETUP AWAL & ONBOARDING (5 MENIT MEMULAI)]
1. Profil Usaha & Identitas Struk: Buka menu Pengaturan -> Profil Bisnis di [Pengaturan Profil Bisnis](https://cooca.id/settings?tab=general). Masukkan nama usaha, logo (PNG transparan 500x500px), alamat, dan nomor WhatsApp resmi. Data ini otomatis tercetak pada struk belanja kasir dan dokumen faktur resmi.
2. Pajak & Service Charge: Buka menu Pengaturan -> Pajak & Biaya di [Pengaturan Pajak & Biaya Resto](https://cooca.id/settings?tab=pos_financial). Atur pajak PB1/PPN (Tax Inclusive atau Tax Exclusive) serta Service Charge resto bila ada.
3. Multi-Gudang & Cabang: Buka menu Inventori -> Gudang di [Manajemen Gudang & Multi-Cabang](https://cooca.id/inventory). Buat minimal 1 gudang utama (contoh: Gudang Pusat / Toko Utama).
4. Staf & Hak Akses (RBAC): Buka menu Pengaturan -> Peran & Hak Akses di [Kelola Peran & Hak Akses Staf](https://cooca.id/roles). Daftarkan akun kasir atau staf gudang dengan membatasi akses ke laporan keuangan sensitif.
5. Import Excel: Unggah ratusan produk dan stok awal sekaligus via tombol Import Excel di sidebar katalog produk.

[PANDUAN OPERASIONAL KASIR MODERN (POS)]
- 4 Langkah Kasir Harian:
  1. Buka Shift: Masukkan modal kas awal laci (Starting Cash).
  2. Input Pesanan: Scan barcode (F4) atau klik menu produk. Untuk resto, tentukan meja tamu dan modifier (topping/level pedas).
  3. Pembayaran Cepat: Tekan F2 untuk Quick Pay. Pilih metode Tunai, QRIS Dinamis, Transfer Bank, atau Split Bill (bagi tagihan per orang/nominal).
  4. Tutup Shift: Hitung uang kas fisik laci, sistem mencocokkan selisih kas, lalu cetak laporan Z-Report kasir.
- Kitchen Display & Tiket Dapur (KDS): Tiket pesanan makanan otomatis terkirim ke printer dapur dan layar barista.
- Printer Kasir: Buka Pengaturan Kasir -> Printer. Mendukung printer thermal 58mm dan 80mm standar Bluetooth & USB.
- Anti-Fraud Kasir: Void transaksi, pembatalan pesanan yang sudah tercetak, atau diskon di atas batas wewenang mewajibkan PIN Supervisor 6-digit dan terekam di Audit Log.
- Mode Offline Caching: Jika internet toko terputus, kasir tetap bisa bertransaksi dan mencetak struk. Data otomatis tersinkronisasi kembali ke server saat internet menyala.

[PANDUAN STOK, RESEP BOM & HPP OTOMATIS]
- Resep Bill of Materials (BOM): Menu makanan/minuman dikaitkan ke bahan baku mentah di menu [Katalog Produk & Resep](https://cooca.id/products). Saat kasir menjual 1 porsi Es Kopi Susu, stok espresso, susu, gula aren, dan cup otomatis terpotong per gram/ml di gudang cabang tersebut.
- HPP Real-Time: Jika harga beli bahan mentah dari pemasok naik, HPP menu otomatis ter-update sehingga laba kotor selalu presisi.
- Stock Opname Berkala: Pada menu [Stock Opname Berkala](https://cooca.id/inventory/opnames), unduh Lembar Hitung Kosong untuk staf, input hasil hitung fisik, lalu klik Rekonsiliasi agar sistem menyesuaikan stok dan mencatat selisih ke laporan laba-rugi.
- Transfer Antar-Gudang: Distribusi stok dari Gudang Pusat ke Cabang melalui menu [Transfer Stok Antar-Cabang](https://cooca.id/inventory/transfers) dengan status Pending In-Transit dan tombol Konfirmasi Terima di cabang tujuan.

[PANDUAN PENGADAAN (PURCHASING) & PENJUALAN B2B]
- Pengadaan Supplier (PO): Buat Purchase Order di [Pengadaan Supplier & Purchase Orders](https://cooca.id/purchase-orders) -> Terima barang fisik gudang (GRN) -> Sistem mencatat faktur hutang dagang (AP) beserta jadwal jatuh tempo.
- Penjualan B2B & Grosir: Buat Surat Penawaran (Quotation PDF) -> Konversi jadi Sales Order (SO) di [Penjualan B2B & Sales Orders](https://cooca.id/sales-orders) -> Terbitkan Surat Jalan (Delivery Order) -> Terbitkan Faktur Penagihan (Invoice AR bertempo).

[PANDUAN PEMBUKUAN KEUANGAN & AKUNTANSI]
- Laporan Laba Rugi (P&L): Pantau omzet kotor, beban pokok penjualan (HPP), biaya operasional (listrik, sewa, gaji), dan laba bersih riil setiap saat di [Laporan Laba Rugi & Finansial](https://cooca.id/reports).
- Neraca Saldo: Memantau saldo kas fisik di laci, rekening bank, total valuasi stok gudang, dan kewajiban hutang.
- Rekonsiliasi Bank: Buka [Rekonsiliasi Bank](https://cooca.id/finance/reconciliations) untuk mencocokkan mutasi rekening koran CSV (BCA, Mandiri, BRI, BNI) dengan buku kas COOCA. Catat biaya admin bank melalui tombol Buat Pengeluaran Cepat.
- Ekspor Laporan: Seluruh laporan penjualan dan finansial dapat diunduh ke format XLSX dan PDF siap audit.

[PANDUAN KARYAWAN, PRESENSI GPS & PENGGAJIAN (HRM)]
- Presensi GPS Geofencing: Karyawan melakukan absensi masuk/pulang lewat smartphone dengan validasi radius toko (rekomendasi radius: 50-100 meter di Pengaturan Cabang).
- Pengaturan Shift: Atur shift kerja pagi/siang/malam dengan toleransi keterlambatan menit otomatis di [Presensi GPS & Jadwal Kerja](https://cooca.id/hrm).
- Perhitungan Gaji & Komisi: Bonus penjualan kasir terkalkulasi otomatis ke slip gaji bulanan dan dapat dicetak ke PDF resmi.

[PANDUAN CRM, LOYALITAS & OMNICHANNEL]
- Member Tiering: Akumulasi belanja menaikkan tingkat member (Silver -> Gold -> Platinum) dengan poin yang bisa dipotongkan sebagai diskon belanja di kasir POS.
- WhatsApp Broadcast & Struk Digital: Struk belanja ramah lingkungan otomatis terkirim ke WhatsApp pelanggan tanpa kertas.
- Integrasi Marketplace: Sinkronisasi produk, harga, dan stok otomatis ke Shopee, Tokopedia, dan TikTok Shop untuk mencegah overselling.
- QR Order Meja Dine-In: Pelanggan scan kode QR di meja, buka menu digital interaktif, pesan langsung dari HP, dan bayar via QRIS otomatis.

[KOLEKSI 8 KALKULATOR BISNIS UMKM GRATIS (cooca.id/kalkulator)]
- Akses lengkap di [Koleksi 8 Kalkulator Bisnis UMKM](https://cooca.id/kalkulator):
  1. Kalkulator HPP: Menghitung biaya bahan baku + operasional per unit/porsi.
  2. Kalkulator BEP: Mengetahui titik impas penjualan agar tidak merugi.
  3. Kalkulator Margin & Markup: Menentukan harga jual berdasarkan target margin keuntungan.
  4. Kalkulator Laba Bersih: Menghitung laba riil setelah dikurangi seluruh beban usaha.
  5. Kalkulator Gaji Karyawan: Menghitung gaji pokok, komisi, dan tunjangan staf.
  6. Kalkulator PPh Final 0.5%: Menghitung kewajiban pajak UMKM omzet bruto (PP 23/2018).
  7. Kalkulator Omzet Harian: Menentukan target omzet harian untuk mencapai target bulanan.
  8. Simulasi What-If: Simulasi dampak kenaikan biaya bahan baku terhadap laba bersih.

[ARTIKEL PANDUAN & BLOG RESMI COOCA (cooca.id/blog)]
- [Setup Multi-Outlet COOCA UMKM dalam 24 Jam: Step-by-step Migrasi](https://cooca.id/blog/setup-multi-outlet-cooca-umkm-dalam-24-jam-step-by-step-migrasi)
- [Panduan Lengkap POS & Manajemen Kasir Modern](https://cooca.id/blog/panduan-lengkap-pos-kasir-modern)
- [Cara Hitung HPP Resep & Manajemen Stok Anti-Bocor](https://cooca.id/blog/cara-hitung-hpp-resep-manajemen-stok-anti-bocor)
- [SOP Buka Tutup Shift Kasir dan Rekonsiliasi Kas Laci](https://cooca.id/blog/sop-buka-tutup-shift-kasir-dan-rekonsiliasi-kas-laci)
- [Strategi Kelola Utang Piutang Usaha Tanpa Macet](https://cooca.id/blog/strategi-kelola-utang-piutang-usaha-tanpa-macet)

[PANDUAN PEMECAHAN MASALAH TEKNIS (TROUBLESHOOTING)]
- Printer thermal bluetooth tidak mencetak: Pastikan bluetooth aktif dan sudah di-pairing, buka menu Pengaturan Kasir -> Printer di COOCA, pilih nama printer dan lakukan "Test Print", pastikan kertas thermal tidak terpasang terbalik.
- Selisih kas laci saat tutup shift: Periksa apakah ada pengeluaran kas kecil yang lupa dicatat di tombol "Uang Keluar Kas" atau transaksi non-tunai yang keliru diinput sebagai tunai.
- Karyawan gagal absen "di luar jangkauan": Buka Pengaturan -> Cabang, periksa titik koordinat latitude/longitude di Google Maps, naikkan radius geofence menjadi 50 atau 100 meter.
- PIN Supervisor salah saat void pesanan: Owner dapat mereset 6 digit PIN Supervisor baru di Pengaturan -> Keuangan & Kasir POS.
- Logo struk tidak muncul / rusak: Gunakan file format PNG transparan dengan resolusi ideal 500x500 pixel.
- Struk tidak terkirim ke WhatsApp: Periksa status koneksi di Pengaturan -> WhatsApp Gateway, pastikan nomor pelanggan diawali kode negara 628...
- Muncul Akses Ditolak (403): Minta Owner memberikan hak akses melalui menu [Kelola Peran & Hak Akses Staf](https://cooca.id/roles) (contoh: izin accounting.view untuk laporan keuangan).

[JAM OPERASIONAL & LAYANAN RESMI]
- WhatsApp Hotline Resmi Customer Service: +62 852-8786-4176 (Tersedia panduan foto & video call)
- Email Dukungan Resmi: support@cooca.id
- Jam Layanan Customer Service: Setiap Hari (Senin - Minggu) pukul 08:00 - 22:00 WIB (SLA respon < 15 menit).
- Layanan Darurat Server & Infrastruktur: Siaga 24 Jam 7 Hari seminggu (24/7).
- Website Resmi: [Website Resmi COOCA Indonesia](https://cooca.id)
- Pendaftaran Uji Coba Gratis: [Pendaftaran Free Trial 14 Hari](https://cooca.id/register)
TXT;

    public const DEFAULT_DOS = [
        'Sapa pelanggan dengan ramah, hangat, dan sebutkan nama pelanggan jika nama mereka tertera.',
        'Gunakan bahasa Indonesia yang santun, profesional, lugas, dan bersahaja (human-friendly, tanpa kata berbelit-belit).',
        'Jawab pertanyaan pelanggan secara to-the-point dan berikan solusi praktis berbasis informasi resmi sistem.',
        'Tuliskan balasan dalam format teks polos (plain text) yang natural, profesional, ramah, dan siap kirim ke pelanggan tanpa sintaks bold (**).',
        'Jika merujuk ke halaman, fitur, atau artikel blog, gunakan judul link yang deskriptif dan mudah dipahami, contoh: [Setup Multi-Outlet COOCA UMKM dalam 24 Jam: Step-by-step Migrasi](https://cooca.id/blog/setup-multi-outlet-cooca-umkm-dalam-24-jam-step-by-step-migrasi) atau [Kelola Peran & Hak Akses Staf](https://cooca.id/roles).',
        'Berikan panduan teknis operasional langkah-demi-langkah yang jelas untuk pemilik bisnis, staf kasir, dan admin toko (seputar setup awal, kasir POS, shift, printer bluetooth, stok opname, resep HPP, pembelian PO, invoicing B2B, presensi GPS, dan rekonsiliasi bank).',
        'Sebutkan harga paket berlangganan secara akurat sesuai katalog resmi: Standard (Rp 39.000/bln atau Rp 390.000/thn), Premium (Rp 89.000/bln atau Rp 890.000/thn), Prestige (Rp 199.000/bln atau Rp 1.990.000/thn), serta Free Trial 14 Hari.',
        'Arahkan calon pengguna untuk mendaftar Free Trial 14 Hari di [Pendaftaran Free Trial 14 Hari](https://cooca.id/register) jika mereka tertarik mencoba COOCA.',
        'Jika pelanggan menyampaikan kendala/keluhan teknis, sampaikan simpati tulus dan berikan diagnosis awal serta langkah pemecahan masalah (troubleshooting) yang relevan.',
        'Gunakan format balasan yang rapi dan mudah dibaca di layar ponsel (hindari paragraf yang terlalu panjang).',
        'Gunakan emoji secara proporsional dan bersahabat (misal: 😊, 🙏, 🚀) tanpa berlebihan.',
    ];

    public const DEFAULT_DONTS = [
        'DILARANG KERAS MENGGUNAKAN KARAKTER ** ATAU FORMAT MARKDOWN BOLD. Seluruh balasan harus berupa teks polos yang rapi dan siap kirim ke pelanggan.',
        'DILARANG MENGGUNAKAN URL MENTAH SEPERTI /roles, /settings, ATAU /inventory sebagai teks balasan kepada pengguna. Jika merujuk ke halaman atau artikel, gunakan judul yang deskriptif dan mudah dipahami, contoh: [Setup Multi-Outlet COOCA UMKM dalam 24 Jam: Step-by-step Migrasi](https://cooca.id/blog/setup-multi-outlet-cooca-umkm-dalam-24-jam-step-by-step-migrasi) atau [Kelola Peran & Hak Akses Staf](https://cooca.id/roles).',
        'Dilarang mengarahkan pengguna umum atau pelanggan ke modul/rute internal Admin (/admin/*). Seluruh panduan teknis pengguna harus mengarah ke modul tenant seperti Pengaturan, Produk, Inventori, POS, Finansial, atau HRM.',
        'Dilarang memberikan diskon khusus, potongan harga sepihak, atau janji finansial di luar tarif resmi yang telah ditentukan.',
        'Dilarang menyebut, membandingkan, atau mendiskreditkan nama merek kompetitor mana pun.',
        'Dilarang membagikan data internal perusahaan, nomor telepon pribadi karyawan, token API, atau kredensial sistem apa pun.',
        'Dilarang berhalusinasi (hallucination) atau mengarang fitur, harga, atau kebijakan yang tidak tercantum dalam Informasi Sistem.',
        'Dilarang berdebat, bersikap defensif, menggunakan nada sarkastik, atau merespons dengan emosi negatif terhadap pelanggan yang komplain.',
        'Dilarang meminta password, PIN, atau kode OTP akun milik pelanggan (ingatkan bahwa COOCA tidak pernah meminta password).',
        'Dilarang menjanjikan tenggat waktu perbaikan teknis instan yang tidak dapat dipertanggungjawabkan tim teknis.',
    ];

    public const DEFAULT_GREETING = 'Halo Kak {customer_name}! Terima kasih sudah menghubungi COOCA Official. Ada yang bisa kami bantu seputar kebutuhan bisnis Anda hari ini? 😊';

    public function __construct(
        private readonly ?AiPlatformEngineService $engineService = null,
        private readonly ?AiProviderManager $providerManager = null
    ) {}

    private function getEngineService(): AiPlatformEngineService
    {
        return $this->engineService ?? app(AiPlatformEngineService::class);
    }

    private function getProviderManager(): AiProviderManager
    {
        return $this->providerManager ?? app(AiProviderManager::class);
    }

    /**
     * Get all AI Chatbot configuration and guidelines for Admin Inbox.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $enabled = SystemSetting::get('admin_inbox_ai_enabled');
        $mode = SystemSetting::get('admin_inbox_ai_mode', 'auto_pilot'); // 'auto_pilot' by default for instant AI response
        $channelsRaw = SystemSetting::get('admin_inbox_ai_channels');
        $model = SystemSetting::get('admin_inbox_ai_model');
        $temperature = (float) SystemSetting::get('admin_inbox_ai_temperature', '0.3');
        $systemInfo = SystemSetting::get('admin_inbox_ai_system_info');
        $dosRaw = SystemSetting::get('admin_inbox_ai_dos');
        $dontsRaw = SystemSetting::get('admin_inbox_ai_donts');
        $greeting = SystemSetting::get('admin_inbox_ai_greeting_template');

        // Resolve channels
        $channels = ['live_chat', 'whatsapp', 'instagram', 'messenger', 'facebook_comments', 'instagram_comments'];
        if ($channelsRaw !== null && $channelsRaw !== '') {
            $decoded = json_decode($channelsRaw, true);
            if (is_array($decoded)) {
                $channels = $decoded;
            }
        }

        // Resolve Dos
        $dos = self::DEFAULT_DOS;
        if ($dosRaw !== null && $dosRaw !== '') {
            $decoded = json_decode($dosRaw, true);
            if (is_array($decoded) && ! empty($decoded)) {
                $dos = $decoded;
            } else {
                $lines = array_filter(array_map('trim', explode("\n", $dosRaw)));
                if (! empty($lines)) {
                    $dos = array_values($lines);
                }
            }
        }

        // Resolve Donts
        $donts = self::DEFAULT_DONTS;
        if ($dontsRaw !== null && $dontsRaw !== '') {
            $decoded = json_decode($dontsRaw, true);
            if (is_array($decoded) && ! empty($decoded)) {
                $donts = $decoded;
            } else {
                $lines = array_filter(array_map('trim', explode("\n", $dontsRaw)));
                if (! empty($lines)) {
                    $donts = array_values($lines);
                }
            }
        }

        $engineConfig = $this->getEngineService()->getConfig();

        return [
            'enabled' => $enabled === null ? true : filter_var($enabled, FILTER_VALIDATE_BOOLEAN),
            'mode' => in_array($mode, ['assistant', 'auto_pilot'], true) ? $mode : 'auto_pilot',
            'channels' => $channels,
            'model' => $model ?: ($engineConfig['model'] ?? AiPlatformEngineService::DEFAULT_MODEL),
            'temperature' => $temperature > 0 ? $temperature : 0.3,
            'system_info' => $systemInfo !== null && trim($systemInfo) !== '' ? $systemInfo : self::DEFAULT_SYSTEM_INFO,
            'dos' => $dos,
            'donts' => $donts,
            'greeting_template' => $greeting !== null && trim($greeting) !== '' ? $greeting : self::DEFAULT_GREETING,
            'platform_engine' => [
                'provider' => $engineConfig['provider'] ?? 'openai_compatible',
                'endpoint' => $engineConfig['endpoint'] ?? '',
                'active_keys_count' => $engineConfig['active_keys_count'] ?? 0,
                'available_models' => $engineConfig['available_models'] ?? [],
            ],
        ];
    }

    /**
     * Save AI Chatbot settings, system information, and Do's & Don'ts.
     *
     * @param array<string, mixed> $data
     */
    public function saveSettings(array $data): void
    {
        if (isset($data['enabled'])) {
            SystemSetting::set('admin_inbox_ai_enabled', $data['enabled'] ? 'true' : 'false', self::SETTING_GROUP);
        }

        if (isset($data['mode']) && in_array($data['mode'], ['assistant', 'auto_pilot'], true)) {
            SystemSetting::set('admin_inbox_ai_mode', (string) $data['mode'], self::SETTING_GROUP);
        }

        if (isset($data['channels']) && is_array($data['channels'])) {
            SystemSetting::set('admin_inbox_ai_channels', json_encode(array_values($data['channels'])), self::SETTING_GROUP);
        }

        if (isset($data['model'])) {
            SystemSetting::set('admin_inbox_ai_model', trim((string) $data['model']), self::SETTING_GROUP);
        }

        if (isset($data['temperature'])) {
            $temp = max(0.0, min(1.0, (float) $data['temperature']));
            SystemSetting::set('admin_inbox_ai_temperature', (string) $temp, self::SETTING_GROUP);
        }

        if (isset($data['system_info'])) {
            SystemSetting::set('admin_inbox_ai_system_info', trim((string) $data['system_info']), self::SETTING_GROUP);
        }

        if (isset($data['dos'])) {
            $dosArray = is_array($data['dos']) ? $data['dos'] : array_filter(array_map('trim', explode("\n", (string) $data['dos'])));
            SystemSetting::set('admin_inbox_ai_dos', json_encode(array_values($dosArray)), self::SETTING_GROUP);
        }

        if (isset($data['donts'])) {
            $dontsArray = is_array($data['donts']) ? $data['donts'] : array_filter(array_map('trim', explode("\n", (string) $data['donts'])));
            SystemSetting::set('admin_inbox_ai_donts', json_encode(array_values($dontsArray)), self::SETTING_GROUP);
        }

        if (isset($data['greeting_template'])) {
            SystemSetting::set('admin_inbox_ai_greeting_template', trim((string) $data['greeting_template']), self::SETTING_GROUP);
        }
    }

    /**
     * Build the structured system prompt integrating system info, rules, and channel context.
     *
     * @param array<string, mixed> $settings
     */
    public function buildSystemPrompt(array $settings, string $channel, ?string $customerName = null): string
    {
        $systemInfo = (string) ($settings['system_info'] ?? self::DEFAULT_SYSTEM_INFO);
        $dos = (array) ($settings['dos'] ?? self::DEFAULT_DOS);
        $donts = (array) ($settings['donts'] ?? self::DEFAULT_DONTS);

        $dosList = implode("\n", array_map(fn($d, $i) => ($i + 1) . '. ' . $d, $dos, array_keys($dos)));
        $dontsList = implode("\n", array_map(fn($d, $i) => ($i + 1) . '. ' . $d, $donts, array_keys($donts)));

        $channelReadable = match ($channel) {
            'whatsapp' => 'WhatsApp Resmi (WhatsApp Cloud API)',
            'instagram', 'instagram_dm' => 'Instagram Direct Message',
            'messenger' => 'Facebook Messenger',
            'live_chat' => 'Live Chat Website Interaktif',
            'facebook_comments' => 'Komentar Postingan Facebook Page',
            'instagram_comments' => 'Komentar Postingan Instagram',
            default => 'Omnichannel Inbox',
        };

        $customerContext = $customerName ? "Nama pelanggan yang sedang berinteraksi: {$customerName}." : "Nama pelanggan belum diketahui (sapa dengan ramah).";

        return <<<PROMPT
Anda adalah AI Customer Assistant Resmi dari COOCA Indonesia (SaaS Business Operating System All-in-One untuk UMKM).
Tugas Anda adalah melayani dan merespons pesan masuk dari pelanggan melalui saluran: {$channelReadable}.
{$customerContext}

============================================================
INFORMASI SISTEM & PENGETAHUAN RESMI COOCA (KNOWLEDGE BASE)
============================================================
{$systemInfo}

============================================================
PEDOMAN KERJA UTAMA - HAL YANG WAJIB DILAKUKAN (DO'S)
============================================================
{$dosList}

============================================================
PANTANGAN KERJA MUTLAK - HAL YANG DILARANG KERAS (DON'TS)
============================================================
{$dontsList}

============================================================
ATURAN FORMAT BALASAN (OUTPUT GUIDELINES):
============================================================
1. Berikan respon LANGSUNG berupa teks balasan yang siap dikirim kepada pelanggan.
2. JANGAN sertakan kata pembuka meta seperti "Berikut adalah balasannya:", tanda kutip pembungkus, atau penjelasan internal.
3. DILARANG KERAS MENGGUNAKAN KARAKTER ASTERIS GANDA (**) ATAU FORMAT MARKDOWN BOLD. Tulislah dalam format teks biasa (plain text) yang rapi, natural, dan mudah dibaca tanpa simbol bold.
4. JANGAN gunakan URL mentah seperti /roles sebagai teks yang ditampilkan kepada pengguna. Jika merujuk ke halaman atau artikel, gunakan judul yang deskriptif dan mudah dipahami, contoh: [Setup Multi-Outlet COOCA UMKM dalam 24 Jam: Step-by-step Migrasi](https://cooca.id/blog/setup-multi-outlet-cooca-umkm-dalam-24-jam-step-by-step-migrasi) atau [Kelola Peran & Hak Akses Staf](https://cooca.id/roles).
5. Sesuaikan panjang respon dengan saluran (misal: WhatsApp/DM lebih personal dan ringkas, komentar media sosial lebih padat).
6. Pastikan mematuhi seluruh aturan DO'S dan TIDAK MELANGGAR satu pun aturan DON'TS.
PROMPT;
    }

    /**
     * Sanitize reply text: remove markdown bold (**), convert raw URLs, and clean up formatting.
     */
    public function sanitizeReplyText(string $text): string
    {
        // 1. Remove ** bold markers completely while preserving text inside
        $text = str_replace('**', '', $text);

        // 2. Remove other stray asterisks used for bold/italic formatting
        $text = preg_replace('/(?<!\*)\*(?!\*)/u', '', $text);

        // 3. Clean surrounding quotes or markdown code block fences
        $text = preg_replace('/^```[a-z]*\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text, " \t\n\r\0\x0B\"'");

        // 4. Ensure raw URLs like /roles, /settings are converted or replaced with descriptive links
        $text = $this->transformRawUrlsToDescriptiveLinks($text);

        return trim($text);
    }

    /**
     * Transform raw URL paths into descriptive, clickable Markdown links.
     */
    public function transformRawUrlsToDescriptiveLinks(string $text): string
    {
        $placeholders = [];
        $tokenIndex = 0;

        // 1. Protect existing markdown links [Text](url) by replacing with placeholder tokens
        $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/u', function ($matches) use (&$placeholders, &$tokenIndex) {
            $token = "%%LINK_PLACEHOLDER_{$tokenIndex}%%";
            $placeholders[$token] = $matches[0];
            $tokenIndex++;
            return $token;
        }, $text);

        $urlMap = [
            'https://cooca.id/settings?tab=general'       => '[Pengaturan Profil Bisnis](https://cooca.id/settings?tab=general)',
            '/settings?tab=general'                       => '[Pengaturan Profil Bisnis](https://cooca.id/settings?tab=general)',
            'https://cooca.id/settings?tab=pos_financial' => '[Pengaturan Pajak & Biaya Resto](https://cooca.id/settings?tab=pos_financial)',
            '/settings?tab=pos_financial'                 => '[Pengaturan Pajak & Biaya Resto](https://cooca.id/settings?tab=pos_financial)',
            'https://cooca.id/roles'                      => '[Kelola Peran & Hak Akses Staf](https://cooca.id/roles)',
            '/roles'                                      => '[Kelola Peran & Hak Akses Staf](https://cooca.id/roles)',
            'https://cooca.id/inventory/opnames'          => '[Stock Opname Berkala](https://cooca.id/inventory/opnames)',
            '/inventory/opnames'                          => '[Stock Opname Berkala](https://cooca.id/inventory/opnames)',
            'https://cooca.id/inventory/transfers'        => '[Transfer Stok Antar-Cabang](https://cooca.id/inventory/transfers)',
            '/inventory/transfers'                        => '[Transfer Stok Antar-Cabang](https://cooca.id/inventory/transfers)',
            'https://cooca.id/inventory'                  => '[Manajemen Gudang & Multi-Cabang](https://cooca.id/inventory)',
            '/inventory'                                  => '[Manajemen Gudang & Multi-Cabang](https://cooca.id/inventory)',
            'https://cooca.id/products'                   => '[Katalog Produk & Resep](https://cooca.id/products)',
            '/products'                                   => '[Katalog Produk & Resep](https://cooca.id/products)',
            'https://cooca.id/purchase-orders'            => '[Pengadaan Supplier & Purchase Orders](https://cooca.id/purchase-orders)',
            '/purchase-orders'                            => '[Pengadaan Supplier & Purchase Orders](https://cooca.id/purchase-orders)',
            'https://cooca.id/sales-orders'               => '[Penjualan B2B & Sales Orders](https://cooca.id/sales-orders)',
            '/sales-orders'                               => '[Penjualan B2B & Sales Orders](https://cooca.id/sales-orders)',
            'https://cooca.id/reports'                    => '[Laporan Laba Rugi & Finansial](https://cooca.id/reports)',
            '/reports'                                    => '[Laporan Laba Rugi & Finansial](https://cooca.id/reports)',
            'https://cooca.id/finance/reconciliations'    => '[Rekonsiliasi Bank](https://cooca.id/finance/reconciliations)',
            '/finance/reconciliations'                    => '[Rekonsiliasi Bank](https://cooca.id/finance/reconciliations)',
            'https://cooca.id/hrm'                        => '[Presensi GPS & Jadwal Kerja](https://cooca.id/hrm)',
            '/hrm'                                        => '[Presensi GPS & Jadwal Kerja](https://cooca.id/hrm)',
            'https://cooca.id/settings'                   => '[Pengaturan Sistem](https://cooca.id/settings)',
            '/settings'                                   => '[Pengaturan Sistem](https://cooca.id/settings)',
            'https://cooca.id/billing'                    => '[Paket Langganan & Tagihan](https://cooca.id/billing)',
            '/billing'                                    => '[Paket Langganan & Tagihan](https://cooca.id/billing)',
            'https://cooca.id/kalkulator'                 => '[Koleksi 8 Kalkulator Bisnis UMKM](https://cooca.id/kalkulator)',
            'cooca.id/kalkulator'                         => '[Koleksi 8 Kalkulator Bisnis UMKM](https://cooca.id/kalkulator)',
            'https://cooca.id/register'                   => '[Pendaftaran Free Trial 14 Hari](https://cooca.id/register)',
            '/register'                                   => '[Pendaftaran Free Trial 14 Hari](https://cooca.id/register)',
        ];

        // Sort keys by string length descending so longer paths match first
        uksort($urlMap, fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($urlMap as $raw => $replacement) {
            $escaped = preg_quote($raw, '/');
            $pattern = '/(?<![a-zA-Z0-9_\-\.\/])' . $escaped . '(?![a-zA-Z0-9_\-\/])/u';

            $text = preg_replace_callback($pattern, function () use ($replacement, &$placeholders, &$tokenIndex) {
                $token = "%%LINK_PLACEHOLDER_{$tokenIndex}%%";
                $placeholders[$token] = $replacement;
                $tokenIndex++;
                return $token;
            }, $text);
        }

        // Restore all link placeholders
        if (! empty($placeholders)) {
            $text = strtr($text, $placeholders);
        }

        return $text;
    }

    /**
     * Generate an intelligent reply suggestion for a specific comment or chat thread.
     *
     * @param array<string, mixed> $options
     * @return array{success: bool, reply: string, model: string, latency_ms: int, error?: string}
     */
    public function generateReplySuggestion(SocialMediaComment $comment, array $options = []): array
    {
        $settings = $this->getSettings();

        if (! ($settings['enabled'] ?? true)) {
            return [
                'success' => false,
                'reply' => '',
                'model' => '',
                'latency_ms' => 0,
                'error' => 'AI Chatbot sedang dinonaktifkan di pengaturan sistem.',
            ];
        }

        $channel = strtolower((string) $comment->platform);
        $customerName = (string) ($comment->from_name ?: 'Pelanggan');

        // Build conversation history messages
        $systemPrompt = $this->buildSystemPrompt($settings, $channel, $customerName);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Retrieve conversation context based on channel
        $historyMessages = $this->extractConversationHistory($comment);
        foreach ($historyMessages as $item) {
            $messages[] = $item;
        }

        // Add latest incoming message if not already included
        $latestCustomerMessage = trim((string) $comment->message);
        if (empty($historyMessages)) {
            $messages[] = [
                'role' => 'user',
                'content' => "Pesan pelanggan: \"{$latestCustomerMessage}\"",
            ];
        }

        // Resolve AI Provider & Key via Engine Service
        $startTime = microtime(true);
        $resolvedAi = $this->getProviderManager()->resolveForBusiness(null);
        /** @var AiProviderInterface $provider */
        $provider = $resolvedAi['provider'];
        $apiKey = (string) ($resolvedAi['api_key'] ?? '');
        $model = (string) ($settings['model'] ?: $resolvedAi['model']);
        $keyId = (string) ($resolvedAi['key_id'] ?? '');

        try {
            $chatOptions = [
                'temperature' => (float) ($settings['temperature'] ?? 0.3),
                'endpoint' => $resolvedAi['endpoint'] ?? null,
                'max_tokens' => 1000,
                'timeout' => 30,
            ];

            $response = $provider->chat($apiKey, $model, $messages, $chatOptions);
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            $replyContent = trim((string) ($response['content'] ?? ''));

            // Sanitize reply text: remove **, markdown bold, code blocks, and convert raw URLs
            $replyContent = $this->sanitizeReplyText($replyContent);

            if ($keyId !== '') {
                $this->getEngineService()->recordKeyUsage($keyId, true);
            }

            return [
                'success' => true,
                'reply' => $replyContent,
                'model' => $model,
                'latency_ms' => $latencyMs,
            ];
        } catch (Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            Log::warning("[AdminInboxAi] Failed to generate AI reply: {$e->getMessage()}", [
                'comment_id' => $comment->id,
                'model' => $model,
            ]);

            if ($keyId !== '') {
                $this->getEngineService()->recordKeyUsage($keyId, false, $e->getMessage());
            }

            // Fallback: rule-based friendly greeting sanitized
            $fallbackReply = str_replace('{customer_name}', $customerName, (string) ($settings['greeting_template'] ?? self::DEFAULT_GREETING));
            $fallbackReply = $this->sanitizeReplyText($fallbackReply);

            return [
                'success' => false,
                'reply' => $fallbackReply,
                'model' => 'fallback-template',
                'latency_ms' => $latencyMs,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Automatic Reply Pipeline for Incoming Inbox Messages.
     * Respects human takeover lifecycle, prevents double replies via atomic locks, and sends to channel.
     */
    public function processAutoReplyIfEligible(SocialMediaComment $comment): ?SocialMediaComment
    {
        // Ignore outbound messages from Cooca official/admin
        if ($comment->is_from_page) {
            return null;
        }

        $settings = $this->getSettings();

        // 1. Check if AI Auto-Reply is enabled and in auto_pilot mode
        if (! ($settings['enabled'] ?? true)) {
            return null;
        }

        if (($settings['mode'] ?? 'auto_pilot') !== 'auto_pilot') {
            return null;
        }

        // 2. Check if the channel is enabled in AI configuration
        $channel = strtolower((string) $comment->platform);
        $channelKey = match ($channel) {
            'instagram', 'instagram_dm' => 'instagram',
            'facebook', 'messenger'     => 'messenger',
            'live_chat'                 => 'live_chat',
            'whatsapp'                  => 'whatsapp',
            'facebook_comments'         => 'facebook_comments',
            'instagram_comments'        => 'instagram_comments',
            default                     => $channel,
        };

        if (! in_array($channelKey, (array) ($settings['channels'] ?? []), true)) {
            return null;
        }

        // 3. Resolve thread key and verify human takeover status
        $threadKey = InboxThreadTakeover::resolveThreadKey(
            $comment->platform,
            $comment->from_id,
            $comment->session_token,
            $comment->parent_comment_id,
            $comment->platform_comment_id
        );

        if (InboxThreadTakeover::isTakenOver($threadKey)) {
            Log::info("[AdminInboxAi] Auto-reply skipped: Thread {$threadKey} is taken over by human agent.");
            return null;
        }

        // 4. Acquire atomic lock for the thread to prevent double replies (AI vs Human or simultaneous webhooks)
        $lock = Cache::lock("inbox_ai_reply_lock_{$threadKey}", 15);
        if (! $lock->get()) {
            Log::info("[AdminInboxAi] Auto-reply skipped: Concurrency lock active for thread {$threadKey}.");
            return null;
        }

        try {
            // Re-fetch comment and verify it has not already been replied to
            $freshComment = SocialMediaComment::find($comment->id);
            if (! $freshComment || $freshComment->status === 'replied') {
                return null;
            }

            // Re-check takeover inside lock
            if (InboxThreadTakeover::isTakenOver($threadKey)) {
                return null;
            }

            // Generate AI reply suggestion
            $aiResult = $this->generateReplySuggestion($freshComment);
            $replyText = trim((string) ($aiResult['reply'] ?? ''));

            if (empty($replyText)) {
                return null;
            }

            // Ensure format compliance (no **, no raw URLs)
            $replyText = $this->sanitizeReplyText($replyText);

            // Dispatch reply directly to destination channel
            $replyRecord = $this->dispatchReplyToChannel($freshComment, $replyText);

            // Record AI reply timestamp
            $cid = (string) ($freshComment->from_id ?: $freshComment->session_token ?: $freshComment->id);
            InboxThreadTakeover::recordAiReply($threadKey, $channelKey, $cid);

            return $replyRecord;
        } catch (Throwable $e) {
            Log::error("[AdminInboxAi] Error during auto-reply dispatch: {$e->getMessage()}", [
                'comment_id' => $comment->id,
                'thread_key' => $threadKey,
            ]);
            return null;
        } finally {
            $lock->release();
        }
    }

    /**
     * Dispatch auto-reply to the corresponding communication channel.
     */
    public function dispatchReplyToChannel(SocialMediaComment $incomingComment, string $replyText): SocialMediaComment
    {
        $channel = strtolower((string) $incomingComment->platform);
        $replyId = (string) Str::uuid();

        $token = (string) (
            SystemSetting::get('meta_system_user_token')
            ?: SystemSetting::get('social_media_page_token')
            ?: SystemSetting::get('instagram_access_token')
            ?: config('services.meta_whatsapp.token', '')
        );

        $waToken = (string) (
            SystemSetting::get('meta_wa_token')
            ?: config('services.meta_whatsapp.token')
            ?: $token
        );

        $pageId = (string) (
            SystemSetting::get('meta_page_id')
            ?: SystemSetting::get('social_media_page_id')
            ?: '1340316975827711'
        );

        $phoneNumberId = (string) (
            SystemSetting::get('meta_wa_phone_number_id')
            ?: SystemSetting::get('whatsapp_phone_number_id')
            ?: config('services.meta_whatsapp.phone_number_id')
            ?: '1304859399385592'
        );

        // 1. Live Chat Website
        if ($channel === 'live_chat') {
            $reply = SocialMediaComment::create([
                'business_id'         => null,
                'is_platform'         => true,
                'platform'            => 'live_chat',
                'platform_comment_id' => 'lc_ai_' . $replyId,
                'platform_post_id'    => 'live_chat_website',
                'parent_comment_id'   => $incomingComment->platform_comment_id,
                'from_id'             => $incomingComment->session_token ?: $incomingComment->from_id,
                'from_name'           => 'Cooca Indonesia (AI Assistant)',
                'from_email'          => 'support@cooca.id',
                'session_token'       => $incomingComment->session_token ?: $incomingComment->from_id,
                'message'             => $replyText,
                'is_from_page'        => true,
                'is_human_takeover'   => false,
                'status'              => 'replied',
                'created_time'        => now(),
            ]);

            $incomingComment->update(['status' => 'replied']);

            try {
                event(new LiveChatMessageSent($reply));
            } catch (Throwable $e) {
                Log::warning("[AdminInboxAi] LiveChat broadcast error: {$e->getMessage()}");
            }

            return $reply;
        }

        // 2. WhatsApp Cloud API
        if ($channel === 'whatsapp') {
            $waTarget = preg_replace('/[^0-9]/', '', (string) $incomingComment->from_id);
            if (str_starts_with($waTarget, '0')) {
                $waTarget = '62' . substr($waTarget, 1);
            }

            if (! empty($waToken) && ! empty($phoneNumberId) && ! empty($waTarget)) {
                try {
                    $res = Http::withToken($waToken)->timeout(12)->post("https://graph.facebook.com/v21.0/{$phoneNumberId}/messages", [
                        'messaging_product' => 'whatsapp',
                        'recipient_type'    => 'individual',
                        'to'                => $waTarget,
                        'type'              => 'text',
                        'text'              => [
                            'preview_url' => false,
                            'body'        => $replyText,
                        ],
                    ]);
                    if ($res->successful()) {
                        $replyId = (string) (data_get($res->json(), 'messages.0.id') ?? $replyId);
                    } else {
                        Log::warning("[AdminInboxAi] WhatsApp reply error: " . $res->body());
                    }
                } catch (Throwable $e) {
                    Log::warning("[AdminInboxAi] WhatsApp HTTP exception: {$e->getMessage()}");
                }
            }

            $reply = SocialMediaComment::create([
                'business_id'         => null,
                'is_platform'         => true,
                'platform'            => 'whatsapp',
                'platform_comment_id' => Str::limit($replyId, 500, ''),
                'platform_post_id'    => Str::limit((string) $incomingComment->platform_post_id, 500, ''),
                'parent_comment_id'   => Str::limit((string) ($incomingComment->platform_comment_id ?: $incomingComment->id), 500, ''),
                'from_id'             => Str::limit((string) $incomingComment->from_id, 500, ''),
                'from_name'           => 'Cooca Indonesia (AI Assistant)',
                'message'             => $replyText,
                'is_from_page'        => true,
                'is_human_takeover'   => false,
                'status'              => 'replied',
                'created_time'        => now(),
            ]);

            $incomingComment->update(['status' => 'replied']);
            return $reply;
        }

        // 3. Messenger / Instagram DM
        if (in_array($channel, ['messenger', 'instagram', 'instagram_dm'], true)) {
            $targetRecipient = $incomingComment->from_id ?: $incomingComment->platform_comment_id;
            $targetPath = ! empty($pageId) ? "{$pageId}/messages" : "me/messages";

            if (! empty($token) && ! empty($targetRecipient)) {
                try {
                    $res = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$targetPath}", [
                        'recipient'    => ['id' => $targetRecipient],
                        'message'      => ['text' => $replyText],
                        'access_token' => $token,
                    ]);
                    if ($res->successful()) {
                        $replyId = (string) ($res->json('message_id') ?? $res->json('id') ?? $replyId);
                    } else {
                        Log::warning("[AdminInboxAi] Direct message reply error: " . $res->body());
                    }
                } catch (Throwable $e) {
                    Log::warning("[AdminInboxAi] Direct message HTTP exception: {$e->getMessage()}");
                }
            }

            $reply = SocialMediaComment::create([
                'business_id'         => null,
                'is_platform'         => true,
                'platform'            => $incomingComment->platform,
                'platform_comment_id' => Str::limit($replyId, 500, ''),
                'platform_post_id'    => Str::limit((string) $incomingComment->platform_post_id, 500, ''),
                'parent_comment_id'   => Str::limit((string) ($incomingComment->platform_comment_id ?: $incomingComment->id), 500, ''),
                'from_id'             => Str::limit((string) $incomingComment->from_id, 500, ''),
                'from_name'           => 'Cooca Indonesia (AI Assistant)',
                'message'             => $replyText,
                'is_from_page'        => true,
                'is_human_takeover'   => false,
                'status'              => 'replied',
                'created_time'        => now(),
            ]);

            $incomingComment->update(['status' => 'replied']);
            return $reply;
        }

        // 4. Post Comments (Facebook / Instagram)
        if (in_array($channel, ['facebook_comments', 'instagram_comments', 'facebook'], true)) {
            if (! empty($token) && ! empty($incomingComment->platform_comment_id)) {
                try {
                    $endpoint = ($channel === 'instagram_comments')
                        ? "https://graph.facebook.com/v21.0/{$incomingComment->platform_comment_id}/replies"
                        : "https://graph.facebook.com/v21.0/{$incomingComment->platform_comment_id}/comments";

                    $res = Http::timeout(12)->post($endpoint, [
                        'message'      => $replyText,
                        'access_token' => $token,
                    ]);
                    if ($res->successful()) {
                        $replyId = (string) ($res->json('id') ?? $replyId);
                    }
                } catch (Throwable $e) {
                    Log::warning("[AdminInboxAi] Comment reply exception: {$e->getMessage()}");
                }
            }

            $reply = SocialMediaComment::create([
                'business_id'             => null,
                'is_platform'             => true,
                'social_media_post_id'    => $incomingComment->social_media_post_id,
                'platform'                => $incomingComment->platform,
                'platform_comment_id'     => Str::limit($replyId, 500, ''),
                'platform_post_id'        => Str::limit((string) $incomingComment->platform_post_id, 500, ''),
                'parent_comment_id'       => Str::limit((string) ($incomingComment->platform_comment_id ?: $incomingComment->id), 500, ''),
                'from_id'                 => Str::limit((string) $incomingComment->from_id, 500, ''),
                'from_name'               => 'Cooca Indonesia (AI Assistant)',
                'message'                 => $replyText,
                'is_from_page'            => true,
                'is_human_takeover'       => false,
                'status'                  => 'replied',
                'created_time'            => now(),
            ]);

            $incomingComment->update(['status' => 'replied']);
            return $reply;
        }

        // Default fallback
        $reply = SocialMediaComment::create([
            'business_id'         => null,
            'is_platform'         => true,
            'platform'            => $incomingComment->platform,
            'platform_comment_id' => Str::limit($replyId, 500, ''),
            'platform_post_id'    => Str::limit((string) $incomingComment->platform_post_id, 500, ''),
            'parent_comment_id'   => Str::limit((string) ($incomingComment->platform_comment_id ?: $incomingComment->id), 500, ''),
            'from_id'             => Str::limit((string) $incomingComment->from_id, 500, ''),
            'from_name'           => 'Cooca Indonesia (AI Assistant)',
            'message'             => $replyText,
            'is_from_page'        => true,
            'is_human_takeover'   => false,
            'status'              => 'replied',
            'created_time'        => now(),
        ]);

        $incomingComment->update(['status' => 'replied']);
        return $reply;
    }

    /**
     * Test prompt simulation in the AI Sandbox without affecting customer chats.
     *
     * @param array<string, mixed> $customSettings
     * @return array{success: bool, reply: string, model: string, latency_ms: int, error?: string}
     */
    public function testAiResponse(string $sampleMessage, array $customSettings = []): array
    {
        $currentSettings = $this->getSettings();
        $mergedSettings = array_merge($currentSettings, $customSettings);

        $systemPrompt = $this->buildSystemPrompt($mergedSettings, 'whatsapp', 'Calon Mitra UMKM');

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $sampleMessage],
        ];

        $startTime = microtime(true);
        $resolvedAi = $this->getProviderManager()->resolveForBusiness(null);
        /** @var AiProviderInterface $provider */
        $provider = $resolvedAi['provider'];
        $apiKey = (string) ($resolvedAi['api_key'] ?? '');
        $model = (string) ($mergedSettings['model'] ?: $resolvedAi['model']);
        $keyId = (string) ($resolvedAi['key_id'] ?? '');

        try {
            $chatOptions = [
                'temperature' => (float) ($mergedSettings['temperature'] ?? 0.3),
                'endpoint' => $resolvedAi['endpoint'] ?? null,
                'max_tokens' => 800,
                'timeout' => 25,
            ];

            $response = $provider->chat($apiKey, $model, $messages, $chatOptions);
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            $replyContent = trim((string) ($response['content'] ?? ''));
            $replyContent = $this->sanitizeReplyText($replyContent);

            if ($keyId !== '') {
                $this->getEngineService()->recordKeyUsage($keyId, true);
            }

            return [
                'success' => true,
                'reply' => $replyContent,
                'model' => $model,
                'latency_ms' => $latencyMs,
            ];
        } catch (Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($keyId !== '') {
                $this->getEngineService()->recordKeyUsage($keyId, false, $e->getMessage());
            }

            return [
                'success' => false,
                'reply' => '',
                'model' => $model,
                'latency_ms' => $latencyMs,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Extract structured conversation history for LLM context.
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function extractConversationHistory(SocialMediaComment $comment): array
    {
        $messages = [];
        $channel = strtolower((string) $comment->platform);

        // Case 1: Post Comments (Facebook / Instagram Comments)
        if (in_array($channel, ['facebook_comments', 'instagram_comments', 'facebook', 'instagram'], true)) {
            if ($comment->post) {
                $caption = trim((string) ($comment->post->caption ?? $comment->post->title ?? ''));
                if ($caption !== '') {
                    $messages[] = [
                        'role' => 'user',
                        'content' => "[Konteks Postingan Media Sosial]: \"" . Str::limit($caption, 350) . "\"",
                    ];
                }
            }

            if (! empty($comment->parent_comment_id)) {
                $parent = SocialMediaComment::where('is_platform', true)
                    ->where('platform_comment_id', $comment->parent_comment_id)
                    ->first();
                if ($parent && ! empty($parent->message)) {
                    $messages[] = [
                        'role' => 'user',
                        'content' => "[Komentar Induk]: \"{$parent->message}\"",
                    ];
                }
            }

            $messages[] = [
                'role' => 'user',
                'content' => "[Komentar Masuk dari {$comment->from_name}]: \"{$comment->message}\"",
            ];

            return $messages;
        }

        // Case 2: Direct Messages (WhatsApp, Instagram DM, Messenger, Live Chat)
        $query = SocialMediaComment::where('is_platform', true);

        if ($channel === 'live_chat') {
            $query->where('platform', 'live_chat')
                ->where(function ($q) use ($comment) {
                    if (! empty($comment->session_token)) {
                        $q->where('session_token', $comment->session_token);
                    } elseif (! empty($comment->from_id)) {
                        $q->where('from_id', $comment->from_id);
                    }
                });
        } elseif ($channel === 'whatsapp') {
            $query->where('platform', 'whatsapp')
                ->where(function ($q) use ($comment) {
                    $q->where('from_id', $comment->from_id)
                      ->orWhere('parent_comment_id', $comment->platform_comment_id)
                      ->orWhere('platform_comment_id', $comment->parent_comment_id);
                });
        } else {
            $targetChannels = in_array($channel, ['instagram', 'instagram_dm']) ? ['instagram', 'instagram_dm'] : ['messenger'];
            $query->whereIn('platform', $targetChannels)
                ->where(function ($q) use ($comment) {
                    $q->where('from_id', $comment->from_id)
                      ->orWhere('parent_comment_id', $comment->platform_comment_id);
                });
        }

        $threadComments = $query->orderBy('created_time', 'asc')
            ->take(12)
            ->get();

        if ($threadComments->isEmpty()) {
            $threadComments = collect([$comment]);
        }

        foreach ($threadComments as $tc) {
            $text = trim((string) $tc->message);
            if ($text === '' || $text === '[Foto]') {
                continue;
            }

            if ($tc->is_from_page) {
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $text,
                ];
            } else {
                $messages[] = [
                    'role' => 'user',
                    'content' => $text,
                ];
            }
        }

        return $messages;
    }
}
