<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\Ai\Providers\AiProviderInterface;
use App\Domain\Ai\Providers\AiProviderManager;
use App\Models\SocialMediaComment;
use App\Models\SystemSetting;
use Illuminate\Support\Carbon;
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
- Uji Coba Gratis (Free Trial): 14 Hari Akses Penuh ke semua fitur tanpa perlu kartu kredit dan tanpa komitmen.
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
1. Profil Usaha & Identitas Struk: Buka menu Pengaturan -> Profil Bisnis (/settings?tab=general). Masukkan nama usaha, logo (PNG transparan 500x500px), alamat, dan nomor WhatsApp resmi. Data ini otomatis tercetak pada struk belanja kasir dan dokumen faktur resmi.
2. Pajak & Service Charge: Buka menu Pengaturan -> Pajak & Biaya (/settings?tab=pos_financial). Atur pajak PB1/PPN (Tax Inclusive atau Tax Exclusive) serta Service Charge resto bila ada.
3. Multi-Gudang & Cabang: Buka menu Inventori -> Gudang (/inventory). Buat minimal 1 gudang utama (contoh: Gudang Pusat / Toko Utama).
4. Staf & Hak Akses (RBAC): Buka menu Pengaturan -> Peran & Hak Akses (/roles). Daftarkan akun kasir atau staf gudang dengan membatasi akses ke laporan keuangan sensitif.
5. Import Excel: Unggah ratusan produk dan stok awal sekaligus via tombol Import Excel di sidebar.

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
- Resep Bill of Materials (BOM): Menu makanan/minuman dikaitkan ke bahan baku mentah di menu Produk -> Atur Resep (/products/{id}/bom). Saat kasir menjual 1 porsi Es Kopi Susu, stok espresso, susu, gula aren, dan cup otomatis terpotong per gram/ml di gudang cabang tersebut.
- HPP Real-Time: Jika harga beli bahan mentah dari pemasok naik, HPP menu otomatis ter-update sehingga laba kotor selalu presisi.
- Stock Opname Berkala: Pada menu Inventori -> Stock Opname (/inventory/opnames), unduh Lembar Hitung Kosong untuk staf, input hasil hitung fisik, lalu klik Rekonsiliasi agar sistem menyesuaikan stok dan mencatat selisih ke laporan laba-rugi.
- Transfer Antar-Gudang: Distribusi stok dari Gudang Pusat ke Cabang melalui menu Inventori -> Transfer Stok (/inventory/transfers) dengan status Pending In-Transit dan tombol Konfirmasi Terima di cabang tujuan.

[PANDUAN PENGADAAN (PURCHASING) & PENJUALAN B2B]
- Pengadaan Supplier (PO): Buat Purchase Order di /purchase-orders -> Terima barang fisik gudang (GRN) -> Sistem mencatat faktur hutang dagang (AP) beserta jadwal jatuh tempo.
- Penjualan B2B & Grosir: Buat Surat Penawaran (Quotation PDF) -> Konversi jadi Sales Order (SO) di /sales-orders -> Terbitkan Surat Jalan (Delivery Order) -> Terbitkan Faktur Penagihan (Invoice AR bertempo).

[PANDUAN PEMBUKUAN KEUANGAN & AKUNTANSI]
- Laporan Laba Rugi (P&L): Pantau omzet kotor, beban pokok penjualan (HPP), biaya operasional (listrik, sewa, gaji), dan laba bersih riil setiap saat di /reports.
- Neraca Saldo: Memantau saldo kas fisik di laci, rekening bank, total valuasi stok gudang, dan kewajiban hutang.
- Rekonsiliasi Bank: Buka /finance/reconciliations untuk mencocokkan mutasi rekening koran CSV (BCA, Mandiri, BRI, BNI) dengan buku kas COOCA. Catat biaya admin bank melalui tombol Buat Pengeluaran Cepat.
- Ekspor Laporan: Seluruh laporan penjualan dan finansial dapat diunduh ke format XLSX dan PDF siap audit.

[PANDUAN KARYAWAN, PRESENSI GPS & PENGGAJIAN (HRM)]
- Presensi GPS Geofencing: Karyawan melakukan absensi masuk/pulang lewat smartphone dengan validasi radius toko (rekomendasi radius: 50-100 meter di Pengaturan Cabang).
- Pengaturan Shift: Atur shift kerja pagi/siang/malam dengan toleransi keterlambatan menit otomatis di /hrm.
- Perhitungan Gaji & Komisi: Bonus penjualan kasir terkalkulasi otomatis ke slip gaji bulanan dan dapat dicetak ke PDF resmi.

[PANDUAN CRM, LOYALITAS & OMNICHANNEL]
- Member Tiering: Akumulasi belanja menaikkan tingkat member (Silver -> Gold -> Platinum) dengan poin yang bisa dipotongkan sebagai diskon belanja di kasir POS.
- WhatsApp Broadcast & Struk Digital: Struk belanja ramah lingkungan otomatis terkirim ke WhatsApp pelanggan tanpa kertas.
- Integrasi Marketplace: Sinkronisasi produk, harga, dan stok otomatis ke Shopee, Tokopedia, dan TikTok Shop untuk mencegah overselling.
- QR Order Meja Dine-In: Pelanggan scan kode QR di meja, buka menu digital interaktif, pesan langsung dari HP, dan bayar via QRIS otomatis.

[KOLEKSI 8 KALKULATOR BISNIS UMKM GRATIS (cooca.id/kalkulator)]
1. Kalkulator HPP: Menghitung biaya bahan baku + operasional per unit/porsi.
2. Kalkulator BEP: Mengetahui titik impas penjualan agar tidak merugi.
3. Kalkulator Margin & Markup: Menentukan harga jual berdasarkan target margin keuntungan.
4. Kalkulator Laba Bersih: Menghitung laba riil setelah dikurangi seluruh beban usaha.
5. Kalkulator Gaji Karyawan: Menghitung gaji pokok, komisi, dan tunjangan staf.
6. Kalkulator PPh Final 0.5%: Menghitung kewajiban pajak UMKM omzet bruto (PP 23/2018).
7. Kalkulator Omzet Harian: Menentukan target omzet harian untuk mencapai target bulanan.
8. Simulasi What-If: Simulasi dampak kenaikan biaya bahan baku terhadap laba bersih.

[PANDUAN PEMECAHAN MASALAH TEKNIS (TROUBLESHOOTING)]
- Printer thermal bluetooth tidak mencetak: Pastikan bluetooth aktif dan sudah di-pairing, buka menu Pengaturan Kasir -> Printer di COOCA, pilih nama printer dan lakukan "Test Print", pastikan kertas thermal tidak terpasang terbalik.
- Selisih kas laci saat tutup shift: Periksa apakah ada pengeluaran kas kecil yang lupa dicatat di tombol "Uang Keluar Kas" atau transaksi non-tunai yang keliru diinput sebagai tunai.
- Karyawan gagal absen "di luar jangkauan": Buka Pengaturan -> Cabang, periksa titik koordinat latitude/longitude di Google Maps, naikkan radius geofence menjadi 50 atau 100 meter.
- PIN Supervisor salah saat void pesanan: Owner dapat mereset 6 digit PIN Supervisor baru di Pengaturan -> Keuangan & Kasir POS.
- Logo struk tidak muncul / rusak: Gunakan file format PNG transparan dengan resolusi ideal 500x500 pixel.
- Struk tidak terkirim ke WhatsApp: Periksa status koneksi di Pengaturan -> WhatsApp Gateway, pastikan nomor pelanggan diawali kode negara 628...
- Muncul Akses Ditolak (403): Minta Owner memberikan hak akses melalui Pengaturan -> Peran & Hak Akses (contoh: izin accounting.view untuk laporan keuangan).

[JAM OPERASIONAL & LAYANAN RESMI]
- WhatsApp Hotline Resmi Customer Service: +62 852-8786-4176 (Tersedia panduan foto & video call)
- Email Dukungan Resmi: support@cooca.id
- Jam Layanan Customer Service: Setiap Hari (Senin - Minggu) pukul 08:00 - 22:00 WIB (SLA respon < 15 menit).
- Layanan Darurat Server & Infrastruktur: Siaga 24 Jam 7 Hari seminggu (24/7).
- Website Resmi: https://cooca.id
- Pendaftaran Uji Coba Gratis: https://cooca.id/register
TXT;

    public const DEFAULT_DOS = [
        'Sapa pelanggan dengan ramah, hangat, dan sebutkan nama pelanggan jika nama mereka tertera.',
        'Gunakan bahasa Indonesia yang santun, profesional, lugas, dan bersahaja (human-friendly, tanpa kata berbelit-belit).',
        'Jawab pertanyaan pelanggan secara to-the-point dan berikan solusi praktis berbasis informasi resmi sistem.',
        'Berikan panduan teknis operasional langkah-demi-langkah yang jelas untuk pemilik bisnis, staf kasir, dan admin toko (seputar setup awal, kasir POS, shift, printer bluetooth, stok opname, resep HPP, pembelian PO, invoicing B2B, presensi GPS, dan rekonsiliasi bank).',
        'Sebutkan harga paket berlangganan secara akurat sesuai katalog resmi: Standard (Rp 39.000/bln atau Rp 390.000/thn), Premium (Rp 89.000/bln atau Rp 890.000/thn), Prestige (Rp 199.000/bln atau Rp 1.990.000/thn), serta Free Trial 14 Hari.',
        'Arahkan calon pengguna untuk mendaftar Free Trial 14 Hari di https://cooca.id/register jika mereka tertarik mencoba COOCA.',
        'Jika pelanggan menyampaikan kendala/keluhan teknis, sampaikan simpati tulus dan berikan diagnosis awal serta langkah pemecahan masalah (troubleshooting) yang relevan.',
        'Gunakan format balasan yang rapi dan mudah dibaca di layar ponsel (hindari paragraf yang terlalu panjang).',
        'Gunakan emoji secara proporsional dan bersahabat (misal: 😊, 🙏, 🚀) tanpa berlebihan.',
    ];

    public const DEFAULT_DONTS = [
        'Dilarang mengarahkan pengguna umum atau pelanggan ke modul/rute internal Admin (/admin/*). Seluruh panduan teknis pengguna harus mengarah ke modul tenant seperti /settings, /products, /inventory, /pos, /finance, /hrm, /billing.',
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
        $mode = SystemSetting::get('admin_inbox_ai_mode', 'assistant'); // 'assistant' or 'auto_pilot'
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
            'mode' => in_array($mode, ['assistant', 'auto_pilot'], true) ? $mode : 'assistant',
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
3. Sesuaikan panjang respon dengan saluran (misal: WhatsApp/DM lebih personal dan ringkas, komentar media sosial lebih padat).
4. Pastikan mematuhi seluruh aturan DO'S dan TIDAK MELANGGAR satu pun aturan DON'TS.
PROMPT;
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

            // Clean any unwanted surrounding quotation marks
            $replyContent = preg_replace('/^["\']|["\']$/u', '', $replyContent);

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

            // Fallback: rule-based friendly greeting
            $fallbackReply = str_replace('{customer_name}', $customerName, (string) ($settings['greeting_template'] ?? self::DEFAULT_GREETING));

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
            $replyContent = preg_replace('/^["\']|["\']$/u', '', $replyContent);

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
            // Load post details
            if ($comment->post) {
                $caption = trim((string) ($comment->post->caption ?? $comment->post->title ?? ''));
                if ($caption !== '') {
                    $messages[] = [
                        'role' => 'user',
                        'content' => "[Konteks Postingan Media Sosial]: \"" . Str::limit($caption, 350) . "\"",
                    ];
                }
            }

            // If this is a reply to parent comment
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
