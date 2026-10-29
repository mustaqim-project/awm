<?php

declare(strict_types=1);

namespace App\Domain\Template;

/**
 * Registry and specification of 24 granular feature parameters in COOCA.
 * Supports progressive disclosure: Starter (UMKM Mikro) -> Growth (Bertumbuh) -> Corporate (Scale-Up).
 */
final class FeatureRegistry
{
    // ─── 1. KASIR & POS PARAMETERS ───────────────────────────────────────
    public const POS_ALLOW_NEGATIVE_STOCK     = 'pos.allow_negative_stock';
    public const POS_REQUIRE_SUPERVISOR_PIN    = 'pos.require_supervisor_pin';
    public const POS_BLIND_CLOSE_SHIFT         = 'pos.blind_close_shift';
    public const POS_KITCHEN_ROUTING           = 'pos.kitchen_printer_routing';
    public const POS_WHOLESALE_PRICING         = 'pos.wholesale_tiered_pricing';
    public const POS_TIME_PRICING              = 'pos.time_based_pricing';
    public const POS_SHOW_IMAGES               = 'pos.show_product_images';
    public const POS_FOOD_WASTE_LOG            = 'pos.food_waste_log';

    // ─── 2. SPESIFIK VERTIKAL INDUSTRI ──────────────────────────────────
    public const VERTICAL_WORKSHOP_HISTORY     = 'vertical.workshop_service_history';
    public const VERTICAL_WORKSHOP_REMINDER    = 'vertical.workshop_service_reminder';
    public const VERTICAL_LAUNDRY_TRACKING     = 'vertical.laundry_public_tracking';
    public const VERTICAL_STYLIST_COMMISSION   = 'vertical.stylist_commission';
    public const VERTICAL_PHARMACY_PRESCRIPT   = 'vertical.pharmacy_prescription_book';
    public const VERTICAL_FEFO_DEDUCT          = 'vertical.fefo_auto_deduct';
    public const VERTICAL_TAILOR_MEASURE       = 'vertical.tailor_measurements';
    public const VERTICAL_PRINTING_CALC        = 'vertical.printing_area_calculator';
    public const VERTICAL_BAKERY_CLEARANCE     = 'vertical.bakery_clearance';

    // ─── 3. LOGISTIK & B2B PARAMETERS ────────────────────────────────────
    public const LOGISTICS_DELIVERY_ORDER      = 'logistics.delivery_order';
    public const B2B_CREDIT_LIMIT              = 'b2b.credit_limit_check';
    public const B2B_PROGRESSIVE_INVOICE       = 'b2b.progressive_invoice';

    // ─── 4. MANUFAKTUR & TATA KELOLA PARAMETERS ──────────────────────────
    public const MFG_PRODUCTION_WIP            = 'mfg.production_wip_routing';
    public const MFG_SIZE_COLOR_GRID           = 'mfg.size_color_grid';
    public const PROCUREMENT_MULTI_APPROVAL    = 'procurement.multi_level_approval';
    public const INVENTORY_MULTI_WAREHOUSE     = 'inventory.multi_warehouse_transfer';

    // ─── SCALING TIERS ──────────────────────────────────────────────────
    public const TIER_STARTER   = 'starter';
    public const TIER_GROWTH    = 'growth';
    public const TIER_CORPORATE = 'corporate';

    // ─── CATEGORIES ─────────────────────────────────────────────────────
    public const CAT_POS            = 'pos';
    public const CAT_VERTICAL       = 'vertical';
    public const CAT_LOGISTICS_B2B  = 'logistics_b2b';
    public const CAT_GOVERNANCE_MFG = 'governance_mfg';

    /**
     * Get all registered category metadata.
     *
     * @return array<string, array{name: string, description: string, icon: string}>
     */
    public static function getCategories(): array
    {
        return [
            self::CAT_POS => [
                'name' => 'Kasir & POS Operasional',
                'description' => 'Fleksibilitas kasir, pencegahan fraud laci kas, harga grosir, dan routing cetak struk.',
                'icon' => 'calculator',
            ],
            self::CAT_VERTICAL => [
                'name' => 'Fitur Vertikal Industri Spesifik',
                'description' => 'Parameter spesialis bengkel nopol, laundry tracking, resep apotek, jahit, dan cetak.',
                'icon' => 'briefcase',
            ],
            self::CAT_LOGISTICS_B2B => [
                'name' => 'Logistik, Distribusi & B2B',
                'description' => 'Surat jalan resmi (DO), plafon kredit piutang, dan penagihan termin proyek bertahap.',
                'icon' => 'truck',
            ],
            self::CAT_GOVERNANCE_MFG => [
                'name' => 'Tata Kelola, Manufaktur & Gudang',
                'description' => 'Perjalanan WIP stasiun produksi, matriks ukuran pakaian, approval PO, dan transfer multi-gudang.',
                'icon' => 'shield-check',
            ],
        ];
    }

    /**
     * Complete definitions of all 24 granular feature parameters.
     *
     * @return array<string, array{
     *     name: string,
     *     description: string,
     *     category: string,
     *     icon: string,
     *     default: bool,
     *     starter: bool,
     *     growth: bool,
     *     corporate: bool
     * }>
     */
    public static function definitions(): array
    {
        return [
            // ─── KASIR & POS ─────────────────────────────────────────────
            self::POS_ALLOW_NEGATIVE_STOCK => [
                'name' => 'Izinkan Transaksi Saat Stok Habis',
                'description' => 'Kasir dapat terus memproses transaksi penjualan meskipun stok tercatat nol atau minus.',
                'category' => self::CAT_POS,
                'icon' => 'package-check',
                'default' => true,
                'starter' => true,
                'growth' => false,
                'corporate' => false,
            ],
            self::POS_REQUIRE_SUPERVISOR_PIN => [
                'name' => 'Wajib PIN Supervisor untuk Void & Diskon',
                'description' => 'Mencegah manipulasi kasir dengan mewajibkan verifikasi PIN supervisor pada pembatalan pesanan.',
                'category' => self::CAT_POS,
                'icon' => 'lock',
                'default' => true,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::POS_BLIND_CLOSE_SHIFT => [
                'name' => 'Tutup Shift Kasir Buta (Blind Close)',
                'description' => 'Menyembunyikan angka omzet sistem di layar kasir sampai kasir selesai menghitung fisik uang laci.',
                'category' => self::CAT_POS,
                'icon' => 'eye-off',
                'default' => true,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::POS_KITCHEN_ROUTING => [
                'name' => 'Routing Cetak Printer Dapur & Bar',
                'description' => 'Memisahkan tiket pesanan makanan ke printer dapur dan pesanan minuman ke printer bar otomatis.',
                'category' => self::CAT_POS,
                'icon' => 'printer',
                'default' => false,
                'starter' => false,
                'growth' => false,
                'corporate' => true,
            ],
            self::POS_WHOLESALE_PRICING => [
                'name' => 'Harga Grosir Bertingkat Otomatis',
                'description' => 'Subtotal produk otomatis mendapatkan potongan harga grosir saat jumlah pembelian mencapai batas minimum.',
                'category' => self::CAT_POS,
                'icon' => 'tags',
                'default' => true,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::POS_TIME_PRICING => [
                'name' => 'Tarif Sewa Berbasis Waktu (Billiard / Rental)',
                'description' => 'Kalkulasi otomatis biaya billing sewa meja atau ruangan berdasarkan timer menit/jam pemakaian.',
                'category' => self::CAT_POS,
                'icon' => 'clock',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::POS_SHOW_IMAGES => [
                'name' => 'Tampilkan Gambar Thumbnail Produk di Kasir',
                'description' => 'Menampilkan foto produk pada katalog kasir. Sembunyikan untuk kasir barcode super cepat.',
                'category' => self::CAT_POS,
                'icon' => 'image',
                'default' => true,
                'starter' => true,
                'growth' => true,
                'corporate' => false,
            ],
            self::POS_FOOD_WASTE_LOG => [
                'name' => 'Pencatatan Produk Rusak & Basi (Waste Log)',
                'description' => 'Form cepat pencatatan barang kadaluarsa atau bahan sisa terbuang di akhir jam operasional.',
                'category' => self::CAT_POS,
                'icon' => 'trash-2',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],

            // ─── SPESIFIK VERTIKAL INDUSTRI ──────────────────────────────
            self::VERTICAL_WORKSHOP_HISTORY => [
                'name' => 'Riwayat Servis Kendaraan per Nopol',
                'description' => 'Popup riwayat servis dan part terdahulu otomatis muncul saat kasir/mekanik mengetik nopol kendaraan.',
                'category' => self::CAT_VERTICAL,
                'icon' => 'wrench',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::VERTICAL_WORKSHOP_REMINDER => [
                'name' => 'Pengingat Servis Rutin Pelanggan Bengkel',
                'description' => 'Otomasi jadwal kirim reminder WhatsApp ganti oli dan servis berkala (3 bulan atau 3.000 KM).',
                'category' => self::CAT_VERTICAL,
                'icon' => 'bell-ring',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::VERTICAL_LAUNDRY_TRACKING => [
                'name' => 'Portal Publik Pelacakan Laundry Pelanggan',
                'description' => 'Halaman pelacakan status cucian (Diterima, Cuci, Setrika, Selesai) yang dapat diakses publik via token URL.',
                'category' => self::CAT_VERTICAL,
                'icon' => 'sparkles',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::VERTICAL_STYLIST_COMMISSION => [
                'name' => 'Kalkulasi Komisi Stylist / Terapis Salon',
                'description' => 'Perhitungan komisi terapis atau kapster langsung terdistribusi transparan pada struk kasir.',
                'category' => self::CAT_VERTICAL,
                'icon' => 'scissors',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::VERTICAL_PHARMACY_PRESCRIPT => [
                'name' => 'Buku Register Resep Dokter (Apotek)',
                'description' => 'Wajib mencatat nama dokter, nomor SIP, dan riwayat peresepan obat keras pada transaksi apotek.',
                'category' => self::CAT_VERTICAL,
                'icon' => 'file-plus',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::VERTICAL_FEFO_DEDUCT => [
                'name' => 'Pengeluaran Stok Obat FEFO Otomatis',
                'description' => 'Stok dengan tanggal kedaluwarsa terdekat (First Expired First Out) otomatis dialokasikan lebih dahulu.',
                'category' => self::CAT_VERTICAL,
                'icon' => 'calendar-clock',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::VERTICAL_TAILOR_MEASURE => [
                'name' => 'Form Lembar Ukuran Badan Custom Jahit',
                'description' => 'Input parameter ukuran lingkar dada, pinggang, pinggul, dan panjang baju per pelanggan konveksi.',
                'category' => self::CAT_VERTICAL,
                'icon' => 'ruler',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::VERTICAL_PRINTING_CALC => [
                'name' => 'Kalkulator Luas Bahan Cetak Meteran (PxL)',
                'description' => 'Penghitungan harga cetak spanduk/banner otomatis berdasarkan perkalian panjang x lebar meter.',
                'category' => self::CAT_VERTICAL,
                'icon' => 'scaling',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::VERTICAL_BAKERY_CLEARANCE => [
                'name' => 'Jam Diskon Cuci Gudang Roti (Night Sale)',
                'description' => 'Penerapan diskon khusus secara otomatis pada jam tertentu sebelum toko tutup untuk produk fresh daily.',
                'category' => self::CAT_VERTICAL,
                'icon' => 'badge-percent',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],

            // ─── LOGISTIK & B2B ──────────────────────────────────────────
            self::LOGISTICS_DELIVERY_ORDER => [
                'name' => 'Surat Jalan Resmi Supir (Delivery Order)',
                'description' => 'Cetak dokumen Surat Jalan resmi pengiriman barang dengan tanda tangan supir dan penerima logistik.',
                'category' => self::CAT_LOGISTICS_B2B,
                'icon' => 'truck',
                'default' => false,
                'starter' => false,
                'growth' => false,
                'corporate' => true,
            ],
            self::B2B_CREDIT_LIMIT => [
                'name' => 'Validasi Batas Plafon Piutang B2B',
                'description' => 'Sistem otomatis mengunci penerbitan sales order baru jika piutang toko langganan melampaui plafon kredit.',
                'category' => self::CAT_LOGISTICS_B2B,
                'icon' => 'credit-card',
                'default' => false,
                'starter' => false,
                'growth' => false,
                'corporate' => true,
            ],
            self::B2B_PROGRESSIVE_INVOICE => [
                'name' => 'Faktur Termin & Progres Fisik Proyek',
                'description' => 'Penagihan bertahap berdasarkan milestone pengerjaan (DP 30%, Progres 50%, Pelunasan 20%).',
                'category' => self::CAT_LOGISTICS_B2B,
                'icon' => 'file-check',
                'default' => false,
                'starter' => false,
                'growth' => false,
                'corporate' => true,
            ],

            // ─── TATA KELOLA & MANUFAKTUR ─────────────────────────────────
            self::MFG_PRODUCTION_WIP => [
                'name' => 'Routing SPK Stasiun Kerja Produksi (WIP)',
                'description' => 'Pelacakan pergerakan barang dalam proses kerja antar divisi (Potong -> Jahit -> Sablon -> QC).',
                'category' => self::CAT_GOVERNANCE_MFG,
                'icon' => 'git-commit',
                'default' => false,
                'starter' => false,
                'growth' => false,
                'corporate' => true,
            ],
            self::MFG_SIZE_COLOR_GRID => [
                'name' => 'Matriks Varian Ukuran & Warna Garment',
                'description' => 'Input kuantitas produksi dan stok dalam format kisi-kisi warna (baris) dan ukuran S/M/L/XL (kolom).',
                'category' => self::CAT_GOVERNANCE_MFG,
                'icon' => 'layout-grid',
                'default' => false,
                'starter' => false,
                'growth' => true,
                'corporate' => true,
            ],
            self::PROCUREMENT_MULTI_APPROVAL => [
                'name' => 'Approval Bertingkat PO Pengadaan Supplier',
                'description' => 'Pembelian di atas plafon nominal tertentu mewajibkan persetujuan berjenjang (Supervisor -> Manager -> Direktur).',
                'category' => self::CAT_GOVERNANCE_MFG,
                'icon' => 'shield-alert',
                'default' => false,
                'starter' => false,
                'growth' => false,
                'corporate' => true,
            ],
            self::INVENTORY_MULTI_WAREHOUSE => [
                'name' => 'Transfer Stok Multi-Gudang & Cabang',
                'description' => 'Surat permintaan transfer stok resmi (Transfer Order) dengan alur status In-Transit antar lokasi gudang.',
                'category' => self::CAT_GOVERNANCE_MFG,
                'icon' => 'warehouse',
                'default' => false,
                'starter' => false,
                'growth' => false,
                'corporate' => true,
            ],
        ];
    }

    /**
     * Get all 24 feature keys as an array of strings.
     *
     * @return array<int, string>
     */
    public static function allKeys(): array
    {
        return array_keys(self::definitions());
    }

    /**
     * Verify if a given string is a valid registered feature key.
     */
    public static function isValidKey(string $key): bool
    {
        return array_key_exists($key, self::definitions());
    }

    /**
     * Normalize tier string and verify validity.
     */
    public static function normalizeTier(string $tier): string
    {
        $tier = strtolower(trim($tier));
        if ($tier === 'umkm') {
            return self::TIER_STARTER;
        }

        return in_array($tier, [self::TIER_STARTER, self::TIER_GROWTH, self::TIER_CORPORATE], true)
            ? $tier
            : self::TIER_STARTER;
    }

    /**
     * Get full boolean state mapping for a specific scaling tier.
     *
     * @return array<string, bool>
     */
    public static function getPresetForTier(string $tier): array
    {
        $normalizedTier = self::normalizeTier($tier);
        $defs = self::definitions();
        $preset = [];

        foreach ($defs as $key => $meta) {
            $preset[$key] = match ($normalizedTier) {
                self::TIER_STARTER => $meta['starter'],
                self::TIER_GROWTH => $meta['growth'],
                self::TIER_CORPORATE => $meta['corporate'],
                default => $meta['default'],
            };
        }

        return $preset;
    }

    /**
     * Get recommended disabled modules for this scaling tier to harmonize with ModuleRegistry.
     *
     * @return array<int, string>
     */
    public static function getDisabledModulesForTier(string $tier, ?string $templateCode = null): array
    {
        $normalizedTier = self::normalizeTier($tier);

        // Base modules disabled per tier (Starter disables heavy B2B, Multi-Warehouse & Corporate Accounting)
        $tierDisabled = match ($normalizedTier) {
            self::TIER_STARTER => [
                ModuleRegistry::MODULE_B2B_SALES,
                ModuleRegistry::MODULE_INVENTORY_WAREHOUSE,
                ModuleRegistry::MODULE_ACCOUNTING_CORPORATE,
                ModuleRegistry::MODULE_PROCUREMENT,
                ModuleRegistry::MODULE_LABOR_MACHINES,
            ],
            self::TIER_GROWTH => [
                ModuleRegistry::MODULE_ACCOUNTING_CORPORATE,
            ],
            self::TIER_CORPORATE => [],
        };

        // Combine with template specific exclusions if any
        if (! empty($templateCode)) {
            $templateDisabled = ModuleRegistry::getDisabledModulesForTemplate($templateCode);
            $tierDisabled = array_values(array_unique(array_merge($tierDisabled, $templateDisabled)));
        }

        return $tierDisabled;
    }
}

