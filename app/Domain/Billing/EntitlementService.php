<?php

declare(strict_types=1);

namespace App\Domain\Billing;

use App\Models\Admin;
use App\Models\AiTokenUsage;
use App\Models\BillingPackage;
use App\Models\BomHeader;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\AiTokenTopup;
use App\Models\Location;
use App\Models\Material;
use App\Models\OwnerStorageTopup;
use App\Domain\Storage\OwnerStorageQuotaService;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PosOrder;
use App\Models\QuotaMonthlyUsage;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPromo;
use App\Models\SubscriptionPromoUsage;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class EntitlementService
{
    // ── 4-Tier Quota Mapping (§Blueprint v2.3) ────────────────────────────────
    public const TIER_PRICES = [
        BusinessSubscription::TIER_FREE => [
            'monthly' => 0,
            'annual'  => 0,
        ],
        BusinessSubscription::TIER_STANDARD => [
            'monthly' => 39000,
            'annual'  => 390000,
        ],
        BusinessSubscription::TIER_PREMIUM => [
            'monthly' => 89000,
            'annual'  => 890000,
        ],
        BusinessSubscription::TIER_PRESTIGE => [
            'monthly' => 199000,
            'annual'  => 1990000,
        ],
    ];

    public const TIER_PRODUCT_LIMITS = [
        BusinessSubscription::TIER_FREE => 10,
        BusinessSubscription::TIER_STANDARD => 100, // 100 Item per User Spec
        BusinessSubscription::TIER_PREMIUM => null, // Unlimited
        BusinessSubscription::TIER_PRESTIGE => null, // Unlimited
    ];

    public const TIER_RECIPE_LIMITS = [
        BusinessSubscription::TIER_FREE => 3,
        BusinessSubscription::TIER_STANDARD => 20, // 20 Resep per User Spec
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_MATERIAL_LIMITS = [
        BusinessSubscription::TIER_FREE => 10,
        BusinessSubscription::TIER_STANDARD => 30,
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_CUSTOMER_LIMITS = [
        BusinessSubscription::TIER_FREE => 15,
        BusinessSubscription::TIER_STANDARD => 100,
        BusinessSubscription::TIER_PREMIUM => 1000,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_SUPPLIER_LIMITS = [
        BusinessSubscription::TIER_FREE => 2,
        BusinessSubscription::TIER_STANDARD => 5,
        BusinessSubscription::TIER_PREMIUM => null, // Unlimited per Blueprint v2.3
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_OUTLET_LIMITS = [
        BusinessSubscription::TIER_FREE => 1,
        BusinessSubscription::TIER_STANDARD => 2,
        BusinessSubscription::TIER_PREMIUM => 5,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_WAREHOUSE_LIMITS = [
        BusinessSubscription::TIER_FREE => 1,
        BusinessSubscription::TIER_STANDARD => 2,
        BusinessSubscription::TIER_PREMIUM => 5,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_TOTAL_LOCATION_LIMITS = [
        BusinessSubscription::TIER_FREE => 2, // 1 Toko + 1 Gudang
        BusinessSubscription::TIER_STANDARD => 2, // 2 Lokasi
        BusinessSubscription::TIER_PREMIUM => 5, // 5 Lokasi
        BusinessSubscription::TIER_PRESTIGE => null, // Unlimited
    ];

    public const TIER_USER_PER_OWNER_LIMITS = [
        BusinessSubscription::TIER_FREE => 1,
        BusinessSubscription::TIER_STANDARD => 3,
        BusinessSubscription::TIER_PREMIUM => 10,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_BUSINESS_LIMITS = [
        BusinessSubscription::TIER_FREE => 1,
        BusinessSubscription::TIER_STANDARD => 1,
        BusinessSubscription::TIER_PREMIUM => 3,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_MONTHLY_POS_LIMITS = [
        BusinessSubscription::TIER_FREE => 30,
        BusinessSubscription::TIER_STANDARD => 1000, // 1.000 / bln per User Spec
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_MONTHLY_INVOICE_LIMITS = [
        BusinessSubscription::TIER_FREE => 3,
        BusinessSubscription::TIER_STANDARD => 15, // 15 faktur per bulan per Blueprint v2.3
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_MONTHLY_PO_LIMITS = [
        BusinessSubscription::TIER_FREE => 3,
        BusinessSubscription::TIER_STANDARD => 15, // 15 PO per bulan per Blueprint v2.3
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_WHATSAPP_LIMITS = [
        BusinessSubscription::TIER_FREE => 10,
        BusinessSubscription::TIER_STANDARD => 50,
        BusinessSubscription::TIER_PREMIUM => 200, // 200 Pesan per User Spec
        BusinessSubscription::TIER_PRESTIGE => 1000,
    ];

    public const TIER_SOCIAL_LIMITS = [
        BusinessSubscription::TIER_FREE => 3,
        BusinessSubscription::TIER_STANDARD => 10,
        BusinessSubscription::TIER_PREMIUM => 30,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    public const TIER_TABLE_LIMITS = [
        BusinessSubscription::TIER_FREE => 0,
        BusinessSubscription::TIER_STANDARD => 5,
        BusinessSubscription::TIER_PREMIUM => null,
        BusinessSubscription::TIER_PRESTIGE => null,
    ];

    // ── 4-Tier Feature Access & Lock Definitions (§Blueprint v2.3) ───────────
    public const FEATURE_DEFINITIONS = [
        'ai' => [
            'label' => 'Akses Fitur AI & Prediksi Cerdas (BYOAI / Gemini)',
            'desc' => 'Asisten bisnis AI, analisis tren penjualan, dan otomatisasi konten.',
            'icon' => 'sparkles',
            'category' => 'AI & Integrasi Modern',
        ],
        'mcp' => [
            'label' => 'Gateway MCP (Model Context Protocol)',
            'desc' => 'Koneksi eksternal AI agent ke database via protokol MCP terisolasi.',
            'icon' => 'cpu',
            'category' => 'AI & Integrasi Modern',
        ],
        'transfer_stock' => [
            'label' => 'Surat Jalan Transfer Stok Antar-Cabang & Gudang',
            'desc' => 'Perpindahan stok bahan baku dan produk multi-lokasi dengan otorisasi terima.',
            'icon' => 'truck',
            'category' => 'Multi-Cabang & Restoran',
        ],
        'branch_pricing' => [
            'label' => 'Multi-Pricing (Beda Harga Jual per Cabang Toko)',
            'desc' => 'Penyesuaian harga jual dinamis berdasarkan lokasi pasar gerai.',
            'icon' => 'tag',
            'category' => 'Multi-Cabang & Restoran',
        ],
        'kds' => [
            'label' => 'Kitchen Display System (KDS Layar Dapur Real-time)',
            'desc' => 'Layar pesanan antrian dapur interaktif untuk operasional kafe/resto.',
            'icon' => 'monitor',
            'category' => 'Multi-Cabang & Restoran',
        ],
        'self_order' => [
            'label' => 'Self-Order QR Meja Pelanggan',
            'desc' => 'Pelanggan pesan dan bayar mandiri langsung dari meja makan.',
            'icon' => 'qr-code',
            'category' => 'Multi-Cabang & Restoran',
        ],
        'pos_pin' => [
            'label' => 'Otorisasi PIN Kasir, Shift & Laci Kas',
            'desc' => 'Kontrol pencegahan kecurangan kasir, audit shift dan void transaksi.',
            'icon' => 'key',
            'category' => 'Kasir POS & Operasional',
        ],
        'commission' => [
            'label' => 'Sistem Komisi Kinerja Staf & Kasir',
            'desc' => 'Perhitungan insentif otomatis per transaksi struk kasir atau SPK jasa.',
            'icon' => 'award',
            'category' => 'HRM & Penggajian',
        ],
        'loan' => [
            'label' => 'Buku Kasbon & Cicilan Pinjaman Karyawan',
            'desc' => 'Pencatatan pinjaman staf dengan pemotongan otomatis saat penggajian.',
            'icon' => 'wallet',
            'category' => 'HRM & Penggajian',
        ],
        'daily_worker' => [
            'label' => 'Skema Pekerja Harian Lepas (Daily Worker)',
            'desc' => 'Manajemen upah harian, shift panggilan, dan borongan pekerjaan.',
            'icon' => 'clock',
            'category' => 'HRM & Penggajian',
        ],
        'bpjs' => [
            'label' => 'Kalkulasi BPJS Ketenagakerjaan & Kesehatan',
            'desc' => 'Perhitungan premi BPJS TK (JKK, JKM, JHT, JP) & BPJS Kesehatan otomatis.',
            'icon' => 'shield-check',
            'category' => 'HRM & Penggajian',
        ],
        'thr' => [
            'label' => 'Kalkulasi THR Prorata Berbasis Tanggal Masuk (Join Date)',
            'desc' => 'Perhitungan tunjangan hari raya otomatis sesuai regulasi ketenagakerjaan.',
            'icon' => 'gift',
            'category' => 'HRM & Penggajian',
        ],
        'payroll_wa' => [
            'label' => 'Slip Gaji Digital Otomatis via WhatsApp',
            'desc' => 'Pengiriman slip gaji resmi terenkripsi langsung ke nomor WA staf.',
            'icon' => 'send',
            'category' => 'HRM & Penggajian',
        ],
        'pph21' => [
            'label' => 'Pajak Karyawan PPh 21 TER (PP 58/2023) & Rekonsiliasi Des',
            'desc' => 'Perhitungan pajak bulanan Tarif Efektif Rata-rata A/B/C dan bukti potong 1721-A1.',
            'icon' => 'file-text',
            'category' => 'Perpajakan & Akuntansi',
        ],
        'branch_accounting' => [
            'label' => 'Jurnal Akuntansi & Laba Rugi per Cabang / Konsolidasi',
            'desc' => 'Laporan laba rugi spesifik cabang dan konsolidasi multi-cabang.',
            'icon' => 'bar-chart-3',
            'category' => 'Perpajakan & Akuntansi',
        ],
        'import' => [
            'label' => 'Impor Massal Data Excel / CSV',
            'desc' => 'Impor produk, bahan baku, pelanggan, dan kontak dari file spreadsheet.',
            'icon' => 'upload',
            'category' => 'Manajemen Data',
        ],
        'export' => [
            'label' => 'Ekspor Massal Data Excel / CSV / PDF',
            'desc' => 'Ekspor seluruh pembukuan, transaksi kasir, dan master data.',
            'icon' => 'download',
            'category' => 'Manajemen Data',
        ],
    ];

    public const DEFAULT_TIER_FEATURES = [
        BusinessSubscription::TIER_FREE => [
            'ai' => false,
            'mcp' => false,
            'transfer_stock' => false,
            'branch_pricing' => false,
            'kds' => false,
            'self_order' => false,
            'pos_pin' => false,
            'commission' => false,
            'loan' => false,
            'daily_worker' => false,
            'bpjs' => false,
            'thr' => false,
            'payroll_wa' => false,
            'pph21' => false,
            'branch_accounting' => false,
            'import' => false,
            'export' => false,
        ],
        BusinessSubscription::TIER_STANDARD => [
            'ai' => true,
            'mcp' => true,
            'transfer_stock' => false,
            'branch_pricing' => false,
            'kds' => false,
            'self_order' => false,
            'pos_pin' => true,
            'commission' => false,
            'loan' => false,
            'daily_worker' => false,
            'bpjs' => false,
            'thr' => false,
            'payroll_wa' => false,
            'pph21' => false,
            'branch_accounting' => false,
            'import' => true,
            'export' => true,
        ],
        BusinessSubscription::TIER_PREMIUM => [
            'ai' => true,
            'mcp' => true,
            'transfer_stock' => true,
            'branch_pricing' => true,
            'kds' => true,
            'self_order' => true,
            'pos_pin' => true,
            'commission' => true,
            'loan' => true,
            'daily_worker' => true,
            'bpjs' => true,
            'thr' => true,
            'payroll_wa' => false,
            'pph21' => false,
            'branch_accounting' => true,
            'import' => true,
            'export' => true,
        ],
        BusinessSubscription::TIER_PRESTIGE => [
            'ai' => true,
            'mcp' => true,
            'transfer_stock' => true,
            'branch_pricing' => true,
            'kds' => true,
            'self_order' => true,
            'pos_pin' => true,
            'commission' => true,
            'loan' => true,
            'daily_worker' => true,
            'bpjs' => true,
            'thr' => true,
            'payroll_wa' => true,
            'pph21' => true,
            'branch_accounting' => true,
            'import' => true,
            'export' => true,
        ],
    ];

    // ── Business-level Master Data Limits (Legacy Backward Compatibility) ─────
    public const FREE_PRODUCT_LIMIT           = 10;
    public const FREE_RECIPE_LIMIT            = 3;
    public const FREE_MATERIAL_LIMIT          = 10;
    public const FREE_CUSTOMER_LIMIT          = 15;
    public const FREE_SUPPLIER_LIMIT          = 2;
    public const FREE_OUTLET_LIMIT            = 1;
    public const FREE_WAREHOUSE_LIMIT         = 1;

    // ── Business-level Monthly Transaction Quotas (Legacy Backward Compatibility)
    public const FREE_INVOICE_MONTHLY_LIMIT   = 3;
    public const FREE_PO_MONTHLY_LIMIT        = 3;
    public const FREE_POS_MONTHLY_LIMIT       = 30;
    public const FREE_SOCIAL_POST_MONTHLY_LIMIT = 3;
    public const FREE_WHATSAPP_MONTHLY_LIMIT  = 10;

    // ── Owner-level Limits ────────────────────────────────────────────────────
    public const FREE_BUSINESS_LIMIT          = 1;
    public const FREE_USER_PER_OWNER_LIMIT    = 1;

    // ── Plan Pricing & AI ─────────────────────────────────────────────────────
    public const DEFAULT_CORE_AI_MONTHLY_TOKENS = 0;
    public const DEFAULT_MONTHLY_PRICE        = 49_000.0;
    public const DEFAULT_ANNUAL_PRICE         = 490_000.0;
    public const DEFAULT_SOCIAL_MONTHLY_PRICE = 89_000.0;

    public const PRICE_STANDARD_MONTHLY = 39_000.0;
    public const PRICE_STANDARD_ANNUAL  = 390_000.0;
    public const PRICE_PREMIUM_MONTHLY  = 89_000.0;
    public const PRICE_PREMIUM_ANNUAL   = 890_000.0;
    public const PRICE_PRESTIGE_MONTHLY = 199_000.0;
    public const PRICE_PRESTIGE_ANNUAL  = 1_990_000.0;

    /**
     * Get configured monthly price for Core plan (from BillingPackage subscription catalog).
     */
    public function getMonthlyPrice(): float
    {
        $setting = SystemSetting::get('subscription_price_monthly');
        if ($setting !== null && is_numeric($setting)) {
            return (float) $setting;
        }

        $pkgPrice = BillingPackage::where('type', BillingPackage::TYPE_SUBSCRIPTION)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('code', 'core-monthly')
                    ->orWhere('duration_days', '<=', 31);
            })
            ->orderBy('sort_order')
            ->value('price');

        if ($pkgPrice !== null && is_numeric($pkgPrice)) {
            return (float) $pkgPrice;
        }

        return self::DEFAULT_MONTHLY_PRICE;
    }

    /**
     * Get configured annual price for Core plan (from BillingPackage subscription catalog).
     */
    public function getAnnualPrice(): float
    {
        $setting = SystemSetting::get('subscription_price_annual');
        if ($setting !== null && is_numeric($setting)) {
            return (float) $setting;
        }

        $pkgPrice = BillingPackage::where('type', BillingPackage::TYPE_SUBSCRIPTION)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('code', 'core-annual')
                    ->orWhere('duration_days', '>=', 360);
            })
            ->orderBy('sort_order')
            ->value('price');

        if ($pkgPrice !== null && is_numeric($pkgPrice)) {
            return (float) $pkgPrice;
        }

        return self::DEFAULT_ANNUAL_PRICE;
    }

    /**
     * Get dynamically configured price for a specific tier and cycle, checking Early Adopter and price locks.
     */
    public function getTierPrice(string $tier, string $cycle = 'monthly', ?Business $business = null): float
    {
        $earlyAdopterService = app(EarlyAdopterPricingService::class);
        $pricing = $earlyAdopterService->calculatePrice($tier, $cycle, $business);

        return (float) $pricing['price'];
    }

    /**
     * Get official base normal price (unaffected by Early Adopter batches or price locks).
     */
    public function getBaseTierPrice(string $tier, string $cycle = 'monthly'): float
    {
        return app(EarlyAdopterPricingService::class)->getBasePrice($tier, $cycle);
    }

    /**
     * Get all tier prices matrix for checkout UI and comparison tables.
     *
     * @return array<string, array{monthly: float, annual: float}>
     */
    public function getAllTierPrices(?Business $business = null): array
    {
        return [
            'standard' => [
                'monthly' => $this->getTierPrice('standard', 'monthly', $business),
                'annual'  => $this->getTierPrice('standard', 'annual', $business),
            ],
            'premium' => [
                'monthly' => $this->getTierPrice('premium', 'monthly', $business),
                'annual'  => $this->getTierPrice('premium', 'annual', $business),
            ],
            'prestige' => [
                'monthly' => $this->getTierPrice('prestige', 'monthly', $business),
                'annual'  => $this->getTierPrice('prestige', 'annual', $business),
            ],
        ];
    }

    /**
     * Get configured monthly AI token allowance for Core plan (No free tokens, strictly top-up).
     */
    public function getCoreMonthlyAiTokens(): int
    {
        $value = SystemSetting::get('subscription_ai_tokens_monthly');

        return $value !== null && is_numeric($value) ? (int) $value : self::DEFAULT_CORE_AI_MONTHLY_TOKENS;
    }

    /**
     * Return active admin-managed billing packages for checkout.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, BillingPackage>
     */
    public function activePackages(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return BillingPackage::active()->where('type', $type)->orderBy('sort_order')->orderBy('price')->get();
    }

    /**
     * Get or initialize subscription for business.
     */
    public function getSubscription(Business $business): BusinessSubscription
    {
        /** @var BusinessSubscription $sub */
        $sub = BusinessSubscription::firstOrCreate(
            ['business_id' => $business->id],
            [
                'plan_code' => BusinessSubscription::PLAN_FREE,
                'price' => 0,
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => Carbon::now(),
                'ai_tokens_monthly_allowance' => 0,
                'ai_tokens_remaining' => 0,
                'last_token_reset_at' => Carbon::today()->toDateString(),
            ]
        );

        return $sub;
    }

    public const POS_EMERGENCY_BUFFER = 5;

    /**
     * Get dynamic or fallback quota limit for a given tier and resource.
     * Checks SystemSetting first (tier_limit_{tier}_{resource}), falling back to code constants.
     * Null means unlimited.
     */
    public function getTierLimit(string $tier, string $resource): ?int
    {
        $settingKey = "tier_limit_{$tier}_{$resource}";
        $val = SystemSetting::get($settingKey);

        if ($val !== null && $val !== '') {
            $trimmed = trim((string) $val);
            if ($trimmed === '-1' || strtolower($trimmed) === 'unlimited' || strtolower($trimmed) === 'null') {
                return null;
            }
            if (is_numeric($trimmed)) {
                return max(0, (int) $trimmed);
            }
        }

        // Fallback to code constants
        return match ($resource) {
            'product' => array_key_exists($tier, self::TIER_PRODUCT_LIMITS) ? self::TIER_PRODUCT_LIMITS[$tier] : self::FREE_PRODUCT_LIMIT,
            'recipe' => array_key_exists($tier, self::TIER_RECIPE_LIMITS) ? self::TIER_RECIPE_LIMITS[$tier] : self::FREE_RECIPE_LIMIT,
            'material' => array_key_exists($tier, self::TIER_MATERIAL_LIMITS) ? self::TIER_MATERIAL_LIMITS[$tier] : self::FREE_MATERIAL_LIMIT,
            'customer' => array_key_exists($tier, self::TIER_CUSTOMER_LIMITS) ? self::TIER_CUSTOMER_LIMITS[$tier] : self::FREE_CUSTOMER_LIMIT,
            'supplier' => array_key_exists($tier, self::TIER_SUPPLIER_LIMITS) ? self::TIER_SUPPLIER_LIMITS[$tier] : self::FREE_SUPPLIER_LIMIT,
            'outlet' => array_key_exists($tier, self::TIER_OUTLET_LIMITS) ? self::TIER_OUTLET_LIMITS[$tier] : self::FREE_OUTLET_LIMIT,
            'warehouse' => array_key_exists($tier, self::TIER_WAREHOUSE_LIMITS) ? self::TIER_WAREHOUSE_LIMITS[$tier] : self::FREE_WAREHOUSE_LIMIT,
            'total_location', 'location' => array_key_exists($tier, self::TIER_TOTAL_LOCATION_LIMITS) ? self::TIER_TOTAL_LOCATION_LIMITS[$tier] : 2,
            'user' => array_key_exists($tier, self::TIER_USER_PER_OWNER_LIMITS) ? self::TIER_USER_PER_OWNER_LIMITS[$tier] : 1,
            'business' => array_key_exists($tier, self::TIER_BUSINESS_LIMITS) ? self::TIER_BUSINESS_LIMITS[$tier] : self::FREE_BUSINESS_LIMIT,
            'pos' => array_key_exists($tier, self::TIER_MONTHLY_POS_LIMITS) ? self::TIER_MONTHLY_POS_LIMITS[$tier] : self::FREE_POS_MONTHLY_LIMIT,
            'invoice' => array_key_exists($tier, self::TIER_MONTHLY_INVOICE_LIMITS) ? self::TIER_MONTHLY_INVOICE_LIMITS[$tier] : self::FREE_INVOICE_MONTHLY_LIMIT,
            'po' => array_key_exists($tier, self::TIER_MONTHLY_PO_LIMITS) ? self::TIER_MONTHLY_PO_LIMITS[$tier] : self::FREE_PO_MONTHLY_LIMIT,
            'whatsapp' => array_key_exists($tier, self::TIER_WHATSAPP_LIMITS) ? self::TIER_WHATSAPP_LIMITS[$tier] : self::FREE_WHATSAPP_MONTHLY_LIMIT,
            'social' => array_key_exists($tier, self::TIER_SOCIAL_LIMITS) ? self::TIER_SOCIAL_LIMITS[$tier] : self::FREE_SOCIAL_POST_MONTHLY_LIMIT,
            'table' => array_key_exists($tier, self::TIER_TABLE_LIMITS) ? self::TIER_TABLE_LIMITS[$tier] : 0,
            'storage_gb' => match ($tier) {
                BusinessSubscription::TIER_PRESTIGE => 30,
                BusinessSubscription::TIER_PREMIUM => 10,
                BusinessSubscription::TIER_STANDARD => 3,
                default => 1,
            },
            default => null,
        };
    }

    /**
     * Get complete tier limits matrix for UI rendering and comparison tables.
     *
     * @return array<string, array<string, ?int>>
     */
    public function getAllTierLimits(): array
    {
        $tiers = [
            BusinessSubscription::TIER_FREE,
            BusinessSubscription::TIER_STANDARD,
            BusinessSubscription::TIER_PREMIUM,
            BusinessSubscription::TIER_PRESTIGE,
        ];

        $resources = [
            'product', 'recipe', 'material', 'customer', 'supplier',
            'outlet', 'warehouse', 'total_location', 'user', 'business',
            'pos', 'invoice', 'po', 'whatsapp', 'social', 'table', 'storage_gb',
        ];

        $matrix = [];
        foreach ($tiers as $tier) {
            foreach ($resources as $resource) {
                $matrix[$tier][$resource] = $this->getTierLimit($tier, $resource);
            }
        }

        return $matrix;
    }

    /**
     * Check if a specific feature is enabled for a given subscription tier.
     * Checks SystemSetting override ('tier_feature_{tier}_{feature}') first, falling back to DEFAULT_TIER_FEATURES.
     */
    public function isFeatureEnabled(string $tier, string $feature): bool
    {
        $settingKey = "tier_feature_{$tier}_{$feature}";
        $val = SystemSetting::get($settingKey);

        if ($val !== null && $val !== '') {
            return in_array(strtolower(trim((string) $val)), ['1', 'true', 'yes', 'on', 'enabled'], true);
        }

        return self::DEFAULT_TIER_FEATURES[$tier][$feature] ?? false;
    }

    /**
     * Get 4-tier by 17-feature matrix for UI and admin toggling.
     *
     * @return array<string, array<string, bool>>
     */
    public function getAllTierFeatures(): array
    {
        $tiers = [
            BusinessSubscription::TIER_FREE,
            BusinessSubscription::TIER_STANDARD,
            BusinessSubscription::TIER_PREMIUM,
            BusinessSubscription::TIER_PRESTIGE,
        ];

        $matrix = [];
        foreach ($tiers as $tier) {
            foreach (array_keys(self::FEATURE_DEFINITIONS) as $feature) {
                $matrix[$tier][$feature] = $this->isFeatureEnabled($tier, $feature);
            }
        }

        return $matrix;
    }

    /**
     * Get Product limit for business. Null means unlimited.
     */
    public function getProductLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->getTierLimit($tier, 'product');
    }

    /**
     * Can business create a new product?
     */
    public function canCreateProduct(Business $business): bool
    {
        $limit = $this->getProductLimit($business);
        if ($limit === null) {
            return true;
        }

        $currentProducts = Product::where('business_id', $business->id)->count();

        return $currentProducts < $limit;
    }

    /**
     * Get Recipe limit for business. Null means unlimited.
     */
    public function getRecipeLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->getTierLimit($tier, 'recipe');
    }

    /**
     * Can business create a new recipe (BOM)?
     */
    public function canCreateRecipe(Business $business): bool
    {
        $limit = $this->getRecipeLimit($business);
        if ($limit === null) {
            return true;
        }

        $currentRecipes = BomHeader::whereHas('costModel', function ($q) use ($business) {
            $q->where('business_id', $business->id);
        })->count();

        return $currentRecipes < $limit;
    }

    /**
     * Can business issue a new sales invoice this month?
     */
    public function canCreateInvoiceThisMonth(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        $limit = $this->getTierLimit($tier, 'invoice');
        if ($limit === null) {
            return true;
        }

        $currentMonthInvoices = Invoice::where('business_id', $business->id)
            ->whereMonth('invoice_date', Carbon::today()->month)
            ->whereYear('invoice_date', Carbon::today()->year)
            ->count();

        return $currentMonthInvoices < $limit;
    }

    /**
     * Can business access AI capabilities?
     */
    public function canAccessAi(Business $business): bool
    {
        $sub = $this->getSubscription($business);
        $tier = $sub->getTier();

        if (! $this->isFeatureEnabled($tier, 'ai')) {
            return false;
        }

        // Core plans (Standard, Premium, Prestige) have full access to AI capabilities (BYOAI / unmetered)
        if ($sub->isCorePlan()) {
            return true;
        }

        return ($sub->ai_tokens_remaining > 0)
            || $business->aiTokenTopups()->where('remaining_tokens', '>', 0)->where('expires_at', '>', Carbon::now())->exists();
    }

    /**
     * Can business access Model Context Protocol (MCP) integrations & gateway?
     */
    public function canAccessMcp(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();

        return $this->isFeatureEnabled($tier, 'mcp');
    }

    /**
     * Can business add team members / employees?
     */
    public function canAddMember(Business $business): bool
    {
        $owner = $business->users()->wherePivot('role', 'owner')->first();
        if (! $owner) {
            return false;
        }

        $highestTier = app(OwnerStorageQuotaService::class)->getHighestTierForOwner($owner);
        $userLimit = $this->getTierLimit($highestTier, 'user');

        if ($userLimit === null) {
            return true;
        }

        $currentUserCount = $this->countOwnerUsers($owner);
        return $currentUserCount < $userLimit;
    }

    /**
     * Count total unique members (non-owner) across all businesses owned by this user.
     */
    public function countOwnerUsers(User $owner): int
    {
        return BusinessMembership::whereIn(
            'business_id',
            $owner->businesses()->pluck('businesses.id')
        )->where('role', '!=', 'owner')->count();
    }

    // ── Owner-Level Checks ────────────────────────────────────────────────────
    
    /**
     * Get Business count limit for an owner or business. Null means unlimited.
     */
    public function getBusinessLimit(Business|User $target): ?int
    {
        if ($target instanceof User) {
            $highestTier = app(OwnerStorageQuotaService::class)->getHighestTierForOwner($target);
            return $this->getTierLimit($highestTier, 'business');
        }

        $tier = $this->getSubscription($target)->getTier();
        return $this->getTierLimit($tier, 'business');
    }

    /**
     * Can this owner create another Business?
     * Tier-based business limits (Blueprint v2.3):
     * Free: 1, Standard: 1, Premium: 3, Prestige: Unlimited.
     */
    public function canCreateBusiness(User $owner): bool
    {
        $businessCount = $owner->businesses()->count();
        if ($businessCount === 0) {
            return true;
        }

        $limit = $this->getBusinessLimit($owner);

        if ($limit === null) {
            return true;
        }

        return $businessCount < $limit;
    }

    // ── Business-Level Master Data Checks ─────────────────────────────────────

    /**
     * Get Material limit for business. Null means unlimited.
     */
    public function getMaterialLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->getTierLimit($tier, 'material');
    }

    /**
     * Can business create a new Material (Bahan Baku)?
     */
    public function canCreateMaterial(Business $business): bool
    {
        $limit = $this->getMaterialLimit($business);
        if ($limit === null) {
            return true;
        }

        $count = Material::where('business_id', $business->id)->count();
        return $count < $limit;
    }

    /**
     * Get Customer limit for business. Null means unlimited.
     */
    public function getCustomerLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->getTierLimit($tier, 'customer');
    }

    /**
     * Can business create a new Customer?
     */
    public function canCreateCustomer(Business $business): bool
    {
        $limit = $this->getCustomerLimit($business);
        if ($limit === null) {
            return true;
        }

        $count = Customer::where('business_id', $business->id)->count();
        return $count < $limit;
    }

    /**
     * Get Supplier limit for business. Null means unlimited.
     */
    public function getSupplierLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->getTierLimit($tier, 'supplier');
    }

    /**
     * Can business create a new Supplier?
     */
    public function canCreateSupplier(Business $business): bool
    {
        $limit = $this->getSupplierLimit($business);
        if ($limit === null) {
            return true;
        }

        $count = Supplier::where('business_id', $business->id)->count();
        return $count < $limit;
    }

    /**
     * Get Location limit for business. Null means unlimited.
     */
    public function getLocationLimit(Business $business, string $type): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->getTierLimit($tier, $type === 'warehouse' ? 'warehouse' : 'outlet');
    }

    /**
     * Get total combined locations (outlets + warehouses) limit for business. Null means unlimited.
     */
    public function getTotalLocationLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->getTierLimit($tier, 'total_location');
    }

    /**
     * Can business create a new Location of given type?
     * type: 'outlet' | 'warehouse' | 'central_kitchen'
     */
    public function canCreateLocation(Business $business, string $type): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        $totalLimit = $this->getTotalLocationLimit($business);
        if ($totalLimit !== null) {
            $totalCount = Location::where('business_id', $business->id)->count();
            if ($totalCount >= $totalLimit) {
                return false;
            }
        }

        // For Free tier: max 1 outlet and max 1 warehouse
        if ($tier === BusinessSubscription::TIER_FREE) {
            $typeCount = Location::where('business_id', $business->id)
                ->where('type', $type)
                ->count();
            if ($typeCount >= 1) {
                return false;
            }
        }

        $limit = $this->getLocationLimit($business, $type);
        if ($limit !== null) {
            $count = Location::where('business_id', $business->id)
                ->where('type', $type)
                ->count();
            if ($count >= $limit) {
                return false;
            }
        }

        return true;
    }

    // ── Monthly Transaction Quota Checks ──────────────────────────────────────

    /**
     * Can business create a new Purchase Order this calendar month?
     */
    public function canCreatePurchaseOrderThisMonth(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        $limit = $this->getTierLimit($tier, 'po');
        if ($limit === null) {
            return true;
        }

        $used = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_PO);
        return $used < $limit;
    }

    /**
     * Get monthly POS transaction limit for business. Null means unlimited.
     */
    public function getMonthlyPosLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->getTierLimit($tier, 'pos');
    }

    /**
     * Can business process a new POS transaction this calendar month?
     * Includes a 5-transaction emergency buffer to prevent in-store cashiers locking out during customer rushes.
     */
    public function canCreatePosTransactionThisMonth(Business $business): bool
    {
        $limit = $this->getMonthlyPosLimit($business);
        if ($limit === null) {
            return true;
        }

        $used = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_POS);
        $effectiveLimit = $limit + self::POS_EMERGENCY_BUFFER;

        return $used < $effectiveLimit;
    }

    /**
     * Check if the business is currently running on the emergency POS buffer (within limit to limit + 5).
     */
    public function isPosInEmergencyBuffer(Business $business): bool
    {
        $limit = $this->getMonthlyPosLimit($business);
        if ($limit === null) {
            return false;
        }

        $used = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_POS);
        return $used >= $limit && $used < ($limit + self::POS_EMERGENCY_BUFFER);
    }

    /**
     * Get remaining emergency buffer transactions (0 if not in buffer or exhausted).
     */
    public function getPosRemainingEmergencyBuffer(Business $business): int
    {
        $limit = $this->getMonthlyPosLimit($business);
        if ($limit === null) {
            return 0;
        }

        $used = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_POS);
        $bufferLimit = $limit + self::POS_EMERGENCY_BUFFER;

        if ($used >= $limit && $used < $bufferLimit) {
            return $bufferLimit - $used;
        }

        return 0;
    }

    /**
     * Check if the business has an active paid Social Media Management Add-on.
     */
    public function hasActiveSocialMediaSubscription(Business $business): bool
    {
        return SubscriptionPayment::where('business_id', $business->id)
            ->where('status', SubscriptionPayment::STATUS_APPROVED)
            ->where(function ($q) {
                $q->where('payment_type', 'social_media')
                    ->orWhere('plan_code', 'addon_social_media')
                    ->orWhere('package_name', 'like', '%Social Media%');
            })
            ->where('created_at', '>=', Carbon::now()->subDays(31))
            ->exists();
    }

    /**
     * Can business schedule a new social media post this calendar month?
     * Free: 3, Standard: 10, Premium: 30, Prestige/Add-on: unlimited.
     */
    public function canScheduleSocialPostThisMonth(Business $business): bool
    {
        if ($this->hasActiveSocialMediaSubscription($business)) {
            return true;
        }

        $tier = $this->getSubscription($business)->getTier();
        $limit = $this->getTierLimit($tier, 'social');
        if ($limit === null) {
            return true;
        }

        $used = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_SOCIAL_POST);
        return $used < $limit;
    }

    /**
     * Can business send a WhatsApp notification/blast this calendar month?
     * Tier limits (Blueprint v2.3): Free: 10, Standard: 50, Premium: 300, Prestige: 1000.
     */
    public function canSendWhatsAppThisMonth(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        $limit = $this->getTierLimit($tier, 'whatsapp');
        if ($limit === null) {
            return true;
        }

        $used = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_WHATSAPP);
        return $used < $limit;
    }

    /**
     * Get Table limit for dine-in POS. Null means unlimited.
     */
    public function getTableLimit(Business $business): ?int
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->getTierLimit($tier, 'table');
    }

    /**
     * Can business create a new POS Table?
     */
    public function canCreateTable(Business $business): bool
    {
        $limit = $this->getTableLimit($business);
        if ($limit === null) {
            return true;
        }

        $count = \App\Models\PosTable::where('business_id', $business->id)->count();
        return $count < $limit;
    }

    /**
     * Atomically increment monthly usage counter and return new count.
     * Returns false if quota would be exceeded after increment.
     */
    public function incrementMonthlyUsage(Business $business, string $type, int $limit): bool
    {
        return DB::transaction(function () use ($business, $type, $limit): bool {
            $now = Carbon::now();
            $record = QuotaMonthlyUsage::withoutGlobalScopes()->lockForUpdate()->firstOrCreate(
                [
                    'business_id'   => $business->id,
                    'resource_type' => $type,
                    'year'          => $now->year,
                    'month'         => $now->month,
                ],
                ['usage_count' => 0]
            );

            if ($record->usage_count >= $limit) {
                return false;
            }

            $record->increment('usage_count');
            return true;
        });
    }

    /**
     * Get current monthly usage count for a given resource type.
     */
    public function getMonthlyUsage(Business $business, string $type): int
    {
        $now = Carbon::now();
        return (int) (QuotaMonthlyUsage::withoutGlobalScopes()
            ->where('business_id', $business->id)
            ->where('resource_type', $type)
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->value('usage_count') ?? 0);
    }

    // ── Feature Access Checks ─────────────────────────────────────────────────

    /**
     * Can business use Import Data feature? Free = disabled by default, configurable.
     */
    public function canImportData(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'import');
    }

    /**
     * Can business use Export Data feature? Free = disabled by default, configurable.
     */
    public function canExportData(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'export');
    }

    // ── Multi-Branch & Central Kitchen Feature Gates (§Blueprint v2.3) ────────

    /**
     * Can business transfer stock between branches/warehouses?
     */
    public function canTransferStock(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'transfer_stock');
    }

    /**
     * Can business set specific product prices per branch?
     */
    public function canSetBranchPrices(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'branch_pricing');
    }

    /**
     * Can business use Kitchen Display System (KDS Layar Dapur)?
     */
    public function canUseKds(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'kds');
    }

    /**
     * Can business offer Self-Order QR Meja Pelanggan?
     */
    public function canUseSelfOrder(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'self_order');
    }

    /**
     * Can business use Cashier POS PIN & Cash Drawer controls?
     */
    public function canUsePosPin(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'pos_pin');
    }

    // ── HRM & Payroll Feature Gates (§Blueprint v2.3) ─────────────────────────

    /**
     * Can business calculate and track employee commissions per transaction/SPK?
     */
    public function canCalculateCommissions(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'commission');
    }

    /**
     * Can business manage employee loans and automated salary deductions?
     */
    public function canManageEmployeeLoans(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'loan');
    }

    /**
     * Can business manage Daily Workers / Pekerja Harian Lepas?
     */
    public function canManageDailyWorkers(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'daily_worker');
    }

    /**
     * Can business calculate BPJS Ketenagakerjaan & Kesehatan?
     */
    public function canCalculateBPJS(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'bpjs');
    }

    /**
     * Can business calculate prorated THR based on join date?
     */
    public function canCalculateTHR(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'thr');
    }

    // ── Tax Compliance Engine & Branch Accounting Feature Gates (§Blueprint v2.3) ─

    /**
     * Can business calculate PPh 21 TER (PP 58/2023) and December Pasal 17 reconciliation?
     */
    public function canCalculatePPh21(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'pph21');
    }

    /**
     * Can business track PPh Final UMKM 0.5% with Rp 500M annual threshold? (All active tiers)
     */
    public function canTrackPPhFinalUMKM(Business $business): bool
    {
        return true;
    }

    /**
     * Can business send automated salary slips via WhatsApp?
     */
    public function canAutomatePayrollWhatsApp(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'payroll_wa');
    }

    /**
     * Can business use per-branch / consolidated branch accounting?
     */
    public function canUseBranchAccounting(Business $business): bool
    {
        $tier = $this->getSubscription($business)->getTier();
        return $this->isFeatureEnabled($tier, 'branch_accounting');
    }

    /**
     * Deduct AI token usage.
     */
    public function deductAiTokens(Business $business, int $tokens, ?string $intent = null, ?User $user = null): bool
    {
        if ($tokens <= 0) {
            return false;
        }

        return DB::transaction(function () use ($business, $tokens, $intent, $user): bool {
            $sub = $this->getSubscription($business);
            $remaining = $tokens;
            $batches = $business->aiTokenTopups()
                ->where('remaining_tokens', '>', 0)
                ->where('expires_at', '>', Carbon::now())
                ->orderBy('purchased_at')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();
            foreach ($batches as $batch) {
                $deduct = min($remaining, (int) $batch->remaining_tokens);
                $batch->decrement('remaining_tokens', $deduct);
                $remaining -= $deduct;
                if ($remaining === 0) break;
            }
            if ($remaining > 0) {
                if (!$sub->isCorePlan() || $sub->ai_tokens_remaining < $remaining) return false;
                $sub->decrement('ai_tokens_remaining', $remaining);
            }
            AiTokenUsage::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'model_name' => 'gemini-2.5-flash',
                'input_tokens' => (int) ($tokens * 0.7),
                'output_tokens' => (int) ($tokens * 0.3),
                'total_tokens' => $tokens,
                'intent' => $intent ?? 'general_query',
            ]);
            return true;
        });
    }

    public function getTokenTopupAmount(): int
    {
        return max(0, (int) SystemSetting::get('ai_token_topup_amount', '1000000'));
    }
    public function getTokenTopupPrice(): float
    {
        return max(0, (float) SystemSetting::get('ai_token_topup_price', '50000'));
    }
    public function getStorageTopupBytes(): int
    {
        return max(0, (int) SystemSetting::get('storage_topup_gb', '1')) * 1024 * 1024 * 1024;
    }
    public function getStorageTopupPrice(): float
    {
        return max(0, (float) SystemSetting::get('storage_topup_price', '50000'));
    }

    public function createTokenTopupOrder(Business $business, User $user, string $paymentMethod = SubscriptionPayment::METHOD_QRIS): SubscriptionPayment
    {
        return $this->createTopupOrder($business, $user, 'ai_token', $this->getTokenTopupAmount(), 0, $this->getTokenTopupPrice(), $paymentMethod);
    }

    public function createStorageTopupOrder(Business $business, User $user, string $paymentMethod = SubscriptionPayment::METHOD_QRIS): SubscriptionPayment
    {
        return $this->createTopupOrder($business, $user, 'storage', 0, $this->getStorageTopupBytes(), $this->getStorageTopupPrice(), $paymentMethod);
    }

    private function createTopupOrder(Business $business, User $user, string $type, int $quantity, int $storageBytes, float $amount, string $paymentMethod): SubscriptionPayment
    {
        $orderNumber = $this->nextOrderNumber('TOP-' . date('Ym') . '-');
        return SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'payment_type' => $type,
            'order_number' => $orderNumber,
            'plan_code' => 'topup',
            'cycle' => 'one_time',
            'amount' => $amount,
            'unique_code' => 0,
            'total_payable' => $amount,
            'topup_quantity' => $quantity ?: null,
            'topup_storage_bytes' => $storageBytes ?: null,
            'payment_method' => $paymentMethod,
            'payment_gateway' => SubscriptionPayment::GATEWAY_TRIPAY,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);
    }

    /**
     * Susun nomor order unik global untuk subscription_payments.
     * Constraint `order_number` bersifat global (bukan per-bisnis), jadi pencacah
     * dihitung dari seluruh baris tanpa global scope per-bisnis agar tidak bentrok.
     */
    private function nextOrderNumber(string $prefix): string
    {
        $latest = DB::table('subscription_payments')
            ->where('order_number', 'LIKE', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Upgrade or set plan for business.
     */
    public function upgradeToCore(Business $business, string $cycle = 'monthly'): BusinessSubscription
    {
        $sub = $this->getSubscription($business);
        $isAnnual = $cycle === 'annual';
        $price = $isAnnual ? $this->getAnnualPrice() : $this->getMonthlyPrice();

        // Tambah durasi (skema add-on/FIFO(: perpanjang dari ends_at bila langganan masih aktif;
        // bila status non-aktif / sudah lewat waktunya, mulai dari sekarang.
        $currentEnd = ($sub->isActive() && $sub->ends_at && $sub->ends_at->isFuture())
            ? $sub->ends_at->copy()
            : Carbon::now();
        $newEndsAt = $isAnnual ? $currentEnd->addYear() : $currentEnd->addMonth();

        $sub->update([
            'plan_code' => $isAnnual ? BusinessSubscription::PLAN_CORE_ANNUAL : BusinessSubscription::PLAN_CORE_MONTHLY,
            'price' => $price,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now(),
            'ends_at' => $newEndsAt,
            'ai_tokens_monthly_allowance' => 0,
            'last_token_reset_at' => Carbon::today()->toDateString(),
        ]);

        return $sub;
    }

    /**
     * Determine if a business is currently on the Core plan.
     */
    public function isCorePlan(Business $business): bool
    {
        return $this->getSubscription($business)->isCorePlan();
    }

    /**
     * Usage summary for dashboard and limits meter - all 12+ resources.
     */
    public function getUsageSummary(Business $business, bool $forceFresh = false): array
    {
        $cacheKey = "entitlement_usage_summary_{$business->id}";
        if (! $forceFresh && ! app()->environment('testing')) {
            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $sub = $this->getSubscription($business);
        $isCore = $sub->isCorePlan();
        $monthlyPriceFormatted = number_format($this->getMonthlyPrice(), 0, ',', '.');
        $annualPriceFormatted  = number_format($this->getAnnualPrice(), 0, ',', '.');
        $now = Carbon::now();

        // ── Master data counts ──────────────────────────────────────────────
        $productCount  = Product::where('business_id', $business->id)->count();
        $materialCount = Material::where('business_id', $business->id)->count();
        $customerCount = Customer::where('business_id', $business->id)->count();
        $supplierCount = Supplier::where('business_id', $business->id)->count();
        $outletCount   = Location::where('business_id', $business->id)->where('type', 'outlet')->count();
        $warehouseCount = Location::where('business_id', $business->id)->where('type', 'warehouse')->count();

        $recipeCount = BomHeader::whereHas('costModel', function ($q) use ($business) {
            $q->where('business_id', $business->id);
        })->count();

        $tableCount = \App\Models\PosTable::where('business_id', $business->id)->count();

        // ── Monthly transaction counts ──────────────────────────────────────
        $invoiceMonthCount = Invoice::where('business_id', $business->id)
            ->whereMonth('invoice_date', $now->month)
            ->whereYear('invoice_date',  $now->year)
            ->count();

        $poMonthCount  = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_PO);
        $posMonthCount = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_POS);
        $socialPostsMonthCount = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_SOCIAL_POST);
        $waMonthCount = $this->getMonthlyUsage($business, QuotaMonthlyUsage::TYPE_WHATSAPP);
        $hasSocialAddon = $this->hasActiveSocialMediaSubscription($business);

        // ── Owner-level: Business count & User count ────────────────────────
        $owner = $business->users()->wherePivot('role', 'owner')->first();
        $businessCount = $owner ? $owner->businesses()->count() : 1;
        $userCount     = $owner ? $this->countOwnerUsers($owner) : 0;

        // ── AI tokens ──────────────────────────────────────────────────────
        $aiAllowance = $isCore ? (int) $sub->ai_tokens_monthly_allowance : 0;
        $aiRemaining = $isCore ? (int) $sub->ai_tokens_remaining : 0;
        $activeTopups = $business->aiTokenTopups()->where('expires_at', '>', $now)->get();
        $aiAllowance += (int) $activeTopups->sum('purchased_tokens');
        $aiRemaining += (int) $activeTopups->sum('remaining_tokens');
        $aiUsed = max(0, $aiAllowance - $aiRemaining);

        // ── Storage ─────────────────────────────────────────────────────────
        $storage = $owner ? app(OwnerStorageQuotaService::class)->getSummary($owner) : [
            'used_bytes' => 0,
            'limit_bytes' => 0,
            'used_mb' => 0,
            'limit_gb' => 0,
            'percentage' => 0,
            'is_over_limit' => false,
        ];

        $buildStat = function (int $used, ?int $limit): array {
            return [
                'used'       => $used,
                'limit'      => $limit,
                'percent'    => ($limit && $limit > 0) ? min(100, round(($used / $limit) * 100)) : 0,
                'is_reached' => $limit !== null && $used >= $limit,
                'remaining'  => $limit !== null ? max(0, $limit - $used) : null,
            ];
        };

        $tier = $sub->getTier();
        $tierUserLimit = $this->getTierLimit($tier, 'user');
        $tierBusinessLimit = $this->getTierLimit($tier, 'business');
        $tierTableLimit = $this->getTierLimit($tier, 'table');

        $tierLabel = match ($tier) {
            BusinessSubscription::TIER_PRESTIGE => 'Prestige Plan (Rp199.000/bln)',
            BusinessSubscription::TIER_PREMIUM => 'Premium Plan (Rp89.000/bln)',
            BusinessSubscription::TIER_STANDARD => 'Standard Plan (Rp39.000/bln)',
            default => 'Free Plan (Rp0)',
        };

        $result = [
            'tier'       => $tier,
            'tier_level' => $sub->getTierLevel(),
            'plan_code'  => $sub->plan_code,
            'plan_label' => $tierLabel,
            'is_core' => $isCore,
            'is_past_due' => $sub->isPastDue(),
            'status'  => $sub->status,
            'ends_at' => $sub->ends_at?->format('d M Y'),

            // Owner-level
            'businesses' => $buildStat($businessCount, $tierBusinessLimit),
            'users'      => $buildStat($userCount,     $tierUserLimit),

            // Business master data
            'products'   => $buildStat($productCount,   $this->getTierLimit($tier, 'product')),
            'materials'  => $buildStat($materialCount,  $this->getTierLimit($tier, 'material')),
            'customers'  => $buildStat($customerCount,  $this->getTierLimit($tier, 'customer')),
            'suppliers'  => $buildStat($supplierCount,  $this->getTierLimit($tier, 'supplier')),
            'outlets'    => $buildStat($outletCount,    $this->getTierLimit($tier, 'outlet')),
            'warehouses' => $buildStat($warehouseCount, $this->getTierLimit($tier, 'warehouse')),
            'locations'  => $buildStat($outletCount + $warehouseCount, $this->getTotalLocationLimit($business)),
            'recipes'    => $buildStat($recipeCount,    $this->getTierLimit($tier, 'recipe')),
            'tables'     => $buildStat($tableCount,     $tierTableLimit),

            // Monthly quotas
            'invoices_this_month' => $buildStat($invoiceMonthCount, $this->getTierLimit($tier, 'invoice')),
            'po_this_month'       => $buildStat($poMonthCount,      $this->getTierLimit($tier, 'po')),
            'pos_this_month'      => $buildStat($posMonthCount,     $this->getTierLimit($tier, 'pos')),
            'pos_in_emergency_buffer' => $this->isPosInEmergencyBuffer($business),
            'pos_remaining_buffer' => $this->getPosRemainingEmergencyBuffer($business),
            'pos_emergency_buffer_total' => self::POS_EMERGENCY_BUFFER,
            'social_posts_this_month' => $buildStat($socialPostsMonthCount, $hasSocialAddon ? null : $this->getTierLimit($tier, 'social')),
            'whatsapp_this_month'     => $buildStat($waMonthCount,          $this->getTierLimit($tier, 'whatsapp')),
            'has_social_addon'        => $hasSocialAddon,

            // AI & Storage
            'ai_tokens' => [
                'allowance' => $aiAllowance,
                'used'      => $aiUsed,
                'remaining' => $aiRemaining,
                'percent'   => $aiAllowance > 0 ? min(100, round(($aiUsed / $aiAllowance) * 100)) : 0,
                'is_free'   => !$isCore && $aiAllowance === 0,
                'is_unlimited' => true,
                'is_byoai' => true,
            ],
            'byoai' => [
                'is_byoai' => true,
                'configured' => $business->aiProviderConfigs()->where('is_active', true)->whereNotNull('api_key')->exists(),
            ],
            'storage' => $storage,

            // Feature flags (Strictly dynamic from SystemSetting via EntitlementService)
            'can_import' => $this->canImportData($business),
            'can_export' => $this->canExportData($business),
            'can_create_table' => $this->canCreateTable($business),
            'can_use_kds' => $this->canUseKds($business),
            'can_use_self_order' => $this->canUseSelfOrder($business),
            'can_use_pos_pin' => $this->canUsePosPin($business),
            'can_transfer_stock' => $this->canTransferStock($business),
            'can_set_branch_prices' => $this->canSetBranchPrices($business),
            'can_calculate_commissions' => $this->canCalculateCommissions($business),
            'can_manage_employee_loans' => $this->canManageEmployeeLoans($business),
            'can_manage_daily_workers' => $this->canManageDailyWorkers($business),
            'can_calculate_bpjs' => $this->canCalculateBPJS($business),
            'can_calculate_thr' => $this->canCalculateTHR($business),
            'can_calculate_pph21' => $this->canCalculatePPh21($business),
            'can_track_pph_final_umkm' => $this->canTrackPPhFinalUMKM($business),
            'can_automate_payroll_wa' => $this->canAutomatePayrollWhatsApp($business),
            'can_use_branch_accounting' => $this->canUseBranchAccounting($business),
        ];

        if (! app()->environment('testing')) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, $result, 60);
        }

        return $result;
    }

    /**
     * Clear usage cache for a business.
     */
    public function clearUsageCache(Business $business): void
    {
        \Illuminate\Support\Facades\Cache::forget("entitlement_usage_summary_{$business->id}");
    }

    /**
     * Create a pending subscription payment order with a unique verification code.
     */
    public function createPaymentOrder(
        Business $business,
        User $user,
        string $cycle = 'monthly',
        string $paymentMethod = SubscriptionPayment::METHOD_QRIS,
        string $tier = BusinessSubscription::TIER_STANDARD,
        ?SubscriptionPromo $promo = null
    ): SubscriptionPayment {
        $isAnnual = $cycle === 'annual';

        $tier = match (strtolower($tier)) {
            BusinessSubscription::TIER_PRESTIGE => BusinessSubscription::TIER_PRESTIGE,
            BusinessSubscription::TIER_PREMIUM => BusinessSubscription::TIER_PREMIUM,
            default => BusinessSubscription::TIER_STANDARD,
        };

        $planCode = match ($tier) {
            BusinessSubscription::TIER_PRESTIGE => $isAnnual ? BusinessSubscription::PLAN_PRESTIGE_ANNUAL : BusinessSubscription::PLAN_PRESTIGE_MONTHLY,
            BusinessSubscription::TIER_PREMIUM => $isAnnual ? BusinessSubscription::PLAN_PREMIUM_ANNUAL : BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            default => $isAnnual ? BusinessSubscription::PLAN_STANDARD_ANNUAL : BusinessSubscription::PLAN_STANDARD_MONTHLY,
        };

        $pricingService = app(EarlyAdopterPricingService::class);
        $pricingDetails = $pricingService->calculatePrice($tier, $cycle, $business);
        $baseAmount = (float) $pricingDetails['price'];
        $isEarlyAdopter = (bool) $pricingDetails['is_early_adopter'];
        $pricingBatchId = $pricingDetails['batch']?->id;
        $pricingProgramId = $pricingDetails['program']?->id;
        $originalBasePrice = (float) $pricingDetails['original_base_price'];

        // Atomic reservation if new slot in active early adopter batch
        if ($isEarlyAdopter && ! $pricingDetails['is_grandfathered'] && $pricingDetails['batch']) {
            $reserved = $pricingService->reserveQuotaAtomically($pricingDetails['batch']);
            if (! $reserved) {
                // Re-evaluate if batch filled up right at reservation
                $pricingDetails = $pricingService->calculatePrice($tier, $cycle, $business);
                $baseAmount = (float) $pricingDetails['price'];
                $isEarlyAdopter = (bool) $pricingDetails['is_early_adopter'];
                $pricingBatchId = $pricingDetails['batch']?->id;
                $pricingProgramId = $pricingDetails['program']?->id;
                $originalBasePrice = (float) $pricingDetails['original_base_price'];
            }
        }

        $discountAmount = 0.0;
        $promoId = null;
        $promoCode = null;

        if ($promo !== null) {
            $validation = $promo->validateFor($baseAmount, $tier, $cycle, $business->id);
            if ($validation['valid']) {
                $discountAmount = $promo->calculateDiscount($baseAmount);
                $promoId = $promo->id;
                $promoCode = $promo->code;
            } else {
                $promo = null;
            }
        }

        $totalPayable = max(0.0, round($baseAmount - $discountAmount, 2));

        // Generate unique order number SUB-YYYYMM-XXXX
        $orderNumber = $this->nextOrderNumber('SUB-' . date('Ym') . '-');

        $packageName = match ($tier) {
            BusinessSubscription::TIER_PRESTIGE => 'Paket Prestige ' . ($isAnnual ? 'Tahunan' : 'Bulanan'),
            BusinessSubscription::TIER_PREMIUM => 'Paket Premium ' . ($isAnnual ? 'Tahunan' : 'Bulanan'),
            default => 'Paket Standard ' . ($isAnnual ? 'Tahunan' : 'Bulanan'),
        };

        $payment = SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_number' => $orderNumber,
            'plan_code' => $planCode,
            'package_name' => $packageName,
            'cycle' => $isAnnual ? 'annual' : 'monthly',
            'amount' => $baseAmount,
            'promo_id' => $promoId,
            'promo_code' => $promoCode,
            'pricing_program_id' => $pricingProgramId,
            'pricing_batch_id' => $pricingBatchId,
            'is_early_adopter' => $isEarlyAdopter,
            'original_base_price' => $originalBasePrice,
            'discount_amount' => $discountAmount,
            'unique_code' => 0,
            'total_payable' => $totalPayable,
            'payment_method' => $totalPayable <= 0.0 ? SubscriptionPayment::METHOD_FREE_PROMO : $paymentMethod,
            'payment_gateway' => $totalPayable <= 0.0 ? SubscriptionPayment::GATEWAY_MANUAL : SubscriptionPayment::GATEWAY_TRIPAY,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        if ($promo !== null && $discountAmount > 0) {
            SubscriptionPromoUsage::create([
                'promo_id' => $promo->id,
                'business_id' => $business->id,
                'user_id' => $user->id,
                'subscription_payment_id' => $payment->id,
                'order_number' => $payment->order_number,
                'discount_amount' => $discountAmount,
                'final_paid_amount' => $totalPayable,
            ]);
            $promo->increment('used_count');
        }

        if ($totalPayable <= 0.0) {
            return $this->approvePayment(
                payment: $payment,
                admin: null,
                adminNotes: 'Aktivasi otomatis via Kode Promo Diskon 100% (' . ($promoCode ?? 'FREE') . ')'
            );
        }

        return $payment;
    }

    public function createPackageOrder(
        Business $business,
        User $user,
        BillingPackage $package,
        string $paymentMethod = SubscriptionPayment::METHOD_QRIS,
        ?SubscriptionPromo $promo = null
    ): SubscriptionPayment {
        // If package is free / promo trial (price <= 0), activate immediately without payment
        if ((float) $package->price <= 0.0) {
            return $this->activateFreePackage($business, $user, $package);
        }

        $orderNumber = $this->nextOrderNumber('PKG-' . date('Ym') . '-');
        $paymentType = $package->type;
        $isSubscription = $paymentType === BillingPackage::TYPE_SUBSCRIPTION;
        $baseAmount = (float) $package->price;
        $discountAmount = 0.0;
        $promoId = null;
        $promoCode = null;

        if ($promo !== null) {
            $validation = $promo->validateFor($baseAmount, null, null, $business->id);
            if ($validation['valid']) {
                $discountAmount = $promo->calculateDiscount($baseAmount);
                $promoId = $promo->id;
                $promoCode = $promo->code;
            } else {
                $promo = null;
            }
        }

        $totalPayable = max(0.0, round($baseAmount - $discountAmount, 2));

        $payment = SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'payment_type' => $paymentType,
            'billing_package_id' => $package->id,
            'promo_id' => $promoId,
            'promo_code' => $promoCode,
            'discount_amount' => $discountAmount,
            'package_name' => $package->name,
            'package_duration_days' => $package->duration_days,
            'order_number' => $orderNumber,
            'plan_code' => $isSubscription ? BusinessSubscription::PLAN_CORE_MONTHLY : 'topup',
            'cycle' => $isSubscription ? 'package' : 'one_time',
            'amount' => $baseAmount,
            'unique_code' => 0,
            'total_payable' => $totalPayable,
            'topup_quantity' => $package->token_quantity,
            'topup_storage_bytes' => $package->storage_bytes,
            'payment_method' => $totalPayable <= 0.0 ? SubscriptionPayment::METHOD_FREE_PROMO : $paymentMethod,
            'payment_gateway' => $totalPayable <= 0.0 ? SubscriptionPayment::GATEWAY_MANUAL : SubscriptionPayment::GATEWAY_TRIPAY,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        if ($promo !== null && $discountAmount > 0) {
            SubscriptionPromoUsage::create([
                'promo_id' => $promo->id,
                'business_id' => $business->id,
                'user_id' => $user->id,
                'subscription_payment_id' => $payment->id,
                'order_number' => $payment->order_number,
                'discount_amount' => $discountAmount,
                'final_paid_amount' => $totalPayable,
            ]);
            $promo->increment('used_count');
        }

        if ($totalPayable <= 0.0) {
            return $this->approvePayment(
                payment: $payment,
                admin: null,
                adminNotes: 'Aktivasi otomatis via Kode Promo Diskon 100% (' . ($promoCode ?? 'FREE') . ')'
            );
        }

        return $payment;
    }

    /**
     * Activate a free promo package (e.g. 15-day Trial Pro) immediately without payment or confirmation.
     */
    public function activateFreePackage(Business $business, User $user, BillingPackage $package): SubscriptionPayment
    {
        return DB::transaction(function () use ($business, $user, $package) {
            $orderNumber = $this->nextOrderNumber('FREE-' . date('Ym') . '-');
            $paymentType = $package->type;
            $isSubscription = $paymentType === BillingPackage::TYPE_SUBSCRIPTION;
            $durationDays = max(1, (int) ($package->duration_days ?? 15));

            $payment = SubscriptionPayment::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'payment_type' => $paymentType,
                'billing_package_id' => $package->id,
                'package_name' => $package->name,
                'package_duration_days' => $durationDays,
                'order_number' => $orderNumber,
                'plan_code' => $isSubscription ? BusinessSubscription::PLAN_CORE_MONTHLY : 'topup',
                'cycle' => $isSubscription ? 'package' : 'one_time',
                'amount' => 0,
                'unique_code' => 0,
                'total_payable' => 0,
                'topup_quantity' => $package->token_quantity,
                'topup_storage_bytes' => $package->storage_bytes,
                'payment_method' => SubscriptionPayment::METHOD_FREE_PROMO,
                'status' => SubscriptionPayment::STATUS_APPROVED,
                'admin_notes' => 'Aktivasi Otomatis Promo Bebas Biaya / Trial Pro ' . $durationDays . ' Hari',
                'approved_at' => Carbon::now(),
            ]);

            if ($isSubscription) {
                $subscription = $this->getSubscription($business);
                $currentEnd = ($subscription->isActive() && $subscription->ends_at && $subscription->ends_at->isFuture())
                    ? $subscription->ends_at
                    : Carbon::now();

                $subscription->update([
                    'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
                    'price' => 0,
                    'status' => BusinessSubscription::STATUS_ACTIVE,
                    'starts_at' => Carbon::now(),
                    'ends_at' => $currentEnd->copy()->addDays($durationDays),
                    'ai_tokens_monthly_allowance' => 0,
                    'last_token_reset_at' => Carbon::today()->toDateString(),
                ]);

                if ($package->token_quantity > 0) {
                    $purchasedAt = Carbon::now();
                    AiTokenTopup::create([
                        'business_id' => $business->id,
                        'payment_id' => $payment->id,
                        'purchased_tokens' => $package->token_quantity,
                        'remaining_tokens' => $package->token_quantity,
                        'purchased_at' => $purchasedAt,
                        'expires_at' => $purchasedAt->copy()->addDays($durationDays),
                    ]);
                }
            } elseif ($paymentType === 'ai_token') {
                $purchasedAt = Carbon::now();
                $expiryDays = $package->token_expiry_days ?? 30;
                AiTokenTopup::create([
                    'business_id' => $business->id,
                    'payment_id' => $payment->id,
                    'purchased_tokens' => $package->token_quantity,
                    'remaining_tokens' => $package->token_quantity,
                    'purchased_at' => $purchasedAt,
                    'expires_at' => $purchasedAt->copy()->addDays($expiryDays),
                ]);
            } elseif ($paymentType === 'storage') {
                $owner = $business->users()->wherePivot('role', 'owner')->firstOrFail();
                OwnerStorageTopup::create([
                    'owner_id' => $owner->id,
                    'payment_id' => $payment->id,
                    'storage_bytes' => $package->storage_bytes,
                    'approved_at' => Carbon::now(),
                ]);
            }

            return $payment;
        });
    }

    /**
     * Submit payment proof file and metadata.
     *
     * @param array<string, mixed> $senderData
     */
    public function submitPaymentProof(
        SubscriptionPayment $payment,
        UploadedFile $file,
        array $senderData = []
    ): SubscriptionPayment {
        $filename = Str::random(40) . '.' . $file->extension();
        $path = 'billing-proofs/' . $filename;
        Storage::disk('local')->putFileAs('billing-proofs', $file, $filename);

        $payment->update([
            'payment_proof_path' => $path,
            'sender_bank' => $senderData['sender_bank'] ?? null,
            'sender_account_name' => $senderData['sender_account_name'] ?? null,
            'sender_account_number' => $senderData['sender_account_number'] ?? null,
            'notes' => $senderData['notes'] ?? null,
            'proof_uploaded_at' => Carbon::now(),
            'status' => SubscriptionPayment::STATUS_AWAITING_APPROVAL,
        ]);

        $freshPayment = $payment->fresh();

        // 1. Send notification email to Administrator (dynamically resolved)
        try {
            \App\Domain\Mail\DynamicMailConfig::bootstrap();
            $adminEmails = Admin::where('is_active', true)
                ->where('role', 'super_admin')
                ->pluck('email')
                ->filter()
                ->values()
                ->all();

            if (empty($adminEmails)) {
                $fallback = SystemSetting::get('admin_notification_email', config('mail.from.address', 'admin@cooca.id'));
                if ($fallback) {
                    $adminEmails = [$fallback];
                }
            }

            if (! empty($adminEmails)) {
                \Illuminate\Support\Facades\Mail::to($adminEmails)
                    ->send(new \App\Mail\PaymentUploadedAdminMail($freshPayment));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send payment uploaded email to admin: ' . $e->getMessage());
        }

        return $freshPayment;
    }

    /**
     * Admin approves subscription payment and automatically activates Core subscription.
     */
    public function approvePayment(
        SubscriptionPayment $payment,
        ?Admin $admin = null,
        ?string $adminNotes = null
    ): SubscriptionPayment {
        $approvedPayment = DB::transaction(function () use ($payment, $admin, $adminNotes) {
            if (! $payment->isAwaitingApproval() && ! $payment->isPending()) return $payment->fresh();

            $payment->update([
                'status' => SubscriptionPayment::STATUS_APPROVED,
                'approved_by' => $admin?->id,
                'approved_at' => Carbon::now(),
                'admin_notes' => $adminNotes,
            ]);

            if ($payment->payment_type === 'ai_token') {
                $purchasedAt = Carbon::now();
                $expiryDays = $payment->billingPackage?->token_expiry_days ?? 30;
                AiTokenTopup::create([
                    'business_id' => $payment->business_id,
                    'payment_id' => $payment->id,
                    'purchased_tokens' => $payment->topup_quantity,
                    'remaining_tokens' => $payment->topup_quantity,
                    'purchased_at' => $purchasedAt,
                    'expires_at' => $purchasedAt->copy()->addDays($expiryDays),
                ]);

                // Sinkronkan kredit AI Studio ke saldo bisnis secara atomik
                try {
                    $creditLedgerService = app(\App\Domain\Ai\Studio\Billing\CreditLedgerService::class);
                    $creditsToAdd = (int) ($payment->topup_quantity >= 100000 ? max(10, (int) round($payment->topup_quantity / 10000)) : max(1, (int) $payment->topup_quantity));
                    if ($creditsToAdd > 0) {
                        $creditLedgerService->topup(
                            business: $payment->business_id,
                            credits: $creditsToAdd,
                            idempotencyKey: 'tripay_topup_' . $payment->id,
                            userId: $payment->user_id,
                            description: "Top up kredit AI Studio via Tripay (#{$payment->order_number})",
                            metadata: ['payment_id' => $payment->id, 'order_number' => $payment->order_number]
                        );
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('[EntitlementService] AI Studio Credit topup sync error: ' . $e->getMessage());
                }
            } elseif ($payment->payment_type === 'storage') {
                $owner = $payment->business->users()->wherePivot('role', 'owner')->firstOrFail();
                OwnerStorageTopup::create([
                    'owner_id' => $owner->id,
                    'payment_id' => $payment->id,
                    'storage_bytes' => $payment->topup_storage_bytes,
                    'approved_at' => Carbon::now(),
                ]);
            } elseif ($payment->billing_package_id && $payment->payment_type === BillingPackage::TYPE_SUBSCRIPTION) {
                $subscription = $this->getSubscription($payment->business);
                $durationDays = max(1, (int) ($payment->package_duration_days ?? 30));
                $currentEnd = ($subscription->isActive() && $subscription->ends_at && $subscription->ends_at->isFuture())
                    ? $subscription->ends_at
                    : Carbon::now();
                $subscription->update([
                    'plan_code' => $payment->plan_code,
                    'price' => $payment->amount,
                    'status' => BusinessSubscription::STATUS_ACTIVE,
                    'starts_at' => Carbon::now(),
                    'ends_at' => $currentEnd->copy()->addDays($durationDays),
                    'ai_tokens_monthly_allowance' => 0,
                    'last_token_reset_at' => Carbon::today()->toDateString(),
                ]);
                $this->clearUsageCache($payment->business);
            } elseif ($payment->plan_code && $payment->plan_code !== BusinessSubscription::PLAN_FREE) {
                $subscription = $this->getSubscription($payment->business);
                $isAnnual = $payment->cycle === 'annual';
                $durationDays = $isAnnual ? 365 : 30;
                $currentEnd = ($subscription->isActive() && $subscription->ends_at && $subscription->ends_at->isFuture())
                    ? $subscription->ends_at
                    : Carbon::now();
                $subscription->update([
                    'plan_code' => $payment->plan_code,
                    'price' => $payment->amount,
                    'status' => BusinessSubscription::STATUS_ACTIVE,
                    'starts_at' => Carbon::now(),
                    'ends_at' => $currentEnd->copy()->addDays($durationDays),
                    'ai_tokens_monthly_allowance' => 0,
                    'last_token_reset_at' => Carbon::today()->toDateString(),
                ]);
                $this->clearUsageCache($payment->business);
            } else {
                $this->upgradeToCore($payment->business, $payment->cycle);
                $this->clearUsageCache($payment->business);
            }

            // Apply Early Adopter Price Lock and record in Price History
            if ($payment->is_early_adopter && $payment->pricing_batch_id) {
                $subscription = $this->getSubscription($payment->business);
                app(EarlyAdopterPricingService::class)->applyPriceLockToSubscription($subscription, $payment);
            }

            // Auto-Journal: Catat Beban Langganan SaaS pada Buku Kas Operasional Tenant
            try {
                if ((float) $payment->total_payable > 0) {
                    $defaultCashAccount = \App\Models\CashAccount::where('business_id', $payment->business_id)
                        ->where('is_active', true)
                        ->first();

                    if ($defaultCashAccount) {
                        $amount = (float) $payment->total_payable;
                        $balanceAfter = (float) $defaultCashAccount->current_balance - $amount;
                        \App\Models\CashTransaction::create([
                            'business_id'      => $payment->business_id,
                            'cash_account_id'  => $defaultCashAccount->id,
                            'type'             => \App\Models\CashTransaction::TYPE_OUT,
                            'amount'           => $amount,
                            'balance_after'    => $balanceAfter,
                            'reference_type'   => SubscriptionPayment::class,
                            'reference_id'     => $payment->id,
                            'description'      => "Biaya Langganan Cooca SaaS (#{$payment->order_number} - {$payment->plan_code})",
                            'transaction_date' => Carbon::today()->toDateString(),
                            'created_by'       => $payment->user_id,
                        ]);

                        $defaultCashAccount->decrement('current_balance', $amount);
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[EntitlementService] Gagal mencatat auto-journal beban langganan: " . $e->getMessage());
            }

            // Catat Immutable Audit Log
            try {
                \App\Models\AuditLog::create([
                    'business_id'    => $payment->business_id,
                    'user_id'        => $payment->user_id,
                    'auditable_type' => SubscriptionPayment::class,
                    'auditable_id'   => $payment->id,
                    'action'         => 'subscription.activated',
                    'risk_level'     => \App\Models\AuditLog::RISK_LOW,
                    'risk_reason'    => 'Valid payment approved',
                    'notes'          => "Langganan #{$payment->order_number} ({$payment->plan_code}) berhasil diaktifkan.",
                    'created_at'     => Carbon::now(),
                ]);
            } catch (\Throwable) {
                // Ignore audit logging errors during activation
            }

            return $payment->fresh();
        });

        // 2. Send invoice & receipt email to Business Owner
        try {
            \App\Domain\Mail\DynamicMailConfig::bootstrap();
            $owner = $approvedPayment->business?->users()->wherePivot('role', 'owner')->first()
                ?? $approvedPayment->business?->users()->first();
            $ownerEmail = $owner?->email ?? $approvedPayment->business?->email;

            if ($ownerEmail) {
                \Illuminate\Support\Facades\Mail::to($ownerEmail)
                    ->send(new \App\Mail\PaymentApprovedInvoiceMail($approvedPayment));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send payment approved invoice to owner: ' . $e->getMessage());
        }

        // 3. Send WhatsApp receipt notification to Owner
        try {
            $ownerPhone = $owner?->phone ?? $approvedPayment->business?->phone;
            if ($ownerPhone && class_exists(\App\Domain\WhatsApp\AdminWhatsAppService::class)) {
                $totalFormatted = number_format((float) $approvedPayment->total_payable, 0, ',', '.');
                $planName = $approvedPayment->package_name ?? $approvedPayment->plan_code;
                $ownerName = $owner?->name ?? 'Pemilik Bisnis';
                $msg = "Halo {$ownerName}, pembayaran langganan Cooca Anda (#{$approvedPayment->order_number}) sebesar Rp {$totalFormatted} untuk paket {$planName} telah berhasil diverifikasi. Seluruh fitur dan kuota bisnis Anda telah aktif seketika. Terima kasih!";
                app(\App\Domain\WhatsApp\AdminWhatsAppService::class)->sendMessage($ownerPhone, $msg);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send payment approved WA to owner: ' . $e->getMessage());
        }

        return $approvedPayment;
    }

    /**
     * Admin rejects subscription payment with reason.
     */
    public function rejectPayment(
        SubscriptionPayment $payment,
        Admin $admin,
        string $reason
    ): SubscriptionPayment {
        $payment->update([
            'status' => SubscriptionPayment::STATUS_REJECTED,
            'admin_notes' => $reason,
            'rejected_at' => Carbon::now(),
        ]);

        return $payment->fresh();
    }

    /**
     * Can business / user participate in community (post, comment, repost)?
     * Wajib memiliki paket langganan aktif (Standard, Premium, atau Prestige).
     */
    public function canParticipateInCommunity(Business $business, ?User $user = null): bool
    {
        $sub = $this->getSubscription($business);
        if ($sub->isCorePlan()) {
            return true;
        }

        if ($user instanceof User) {
            $highestTier = app(OwnerStorageQuotaService::class)->getHighestTierForOwner($user);
            return $highestTier !== BusinessSubscription::TIER_FREE;
        }

        return false;
    }

    /**
     * Get official annual promo badge label (e.g. Hemat 2 Bulan).
     */
    public function getAnnualDiscountBadge(): string
    {
        return SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan');
    }
}

