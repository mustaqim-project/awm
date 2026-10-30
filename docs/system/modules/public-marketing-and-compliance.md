# 18. Arsitektur Situs Publik, Sinkronisasi Paket/Limit & Kepatuhan Zero-Overclaim

- **Status Modul:** Production Ready (Diperbarui 30 Oktober 2026)
- **Komponen Utama:** `AppServiceProvider` (Global View Composer), `PublicPricingController`, `PublicCommercialLandingController`, `SystemSetting`, `EntitlementService`
- **Tampilan Terkait:** `resources/views/public/pricing.blade.php`, `resources/views/public/landing_commercial/kasir_gratis.blade.php`, `resources/views/landing.blade.php`, `resources/views/layouts/public_marketing.blade.php`, `resources/views/public/security.blade.php`

---

## 1. Latar Belakang & Prinsip Inti
Situs publik `https://cooca.id` berfungsi sebagai etalase komersial dan representasi resmi kredibilitas COOCA. Seluruh informasi yang disajikan kepada calon pengguna dan publik WAJIB memenuhi standar:
1. **Single Source of Truth Dinamis:** Tidak boleh ada hardcoding harga paket, limit kuota (produk, bahan baku, struk transaksi, akun kasir), atau diskon tahunan di dalam berkas Blade. Seluruhnya harus bersumber secara terpusat dari konfigurasi database admin (`SystemSetting`) dan `EntitlementService::getAllTierLimits()`.
2. **Klaim Nyata Tanpa Overclaim (Zero-Overclaim):** Dilarang mencantumkan SLA fiktif (seperti "VIP 24 Jam" atau "Account Manager Pribadi"), klaim keamanan yang belum diimplementasikan di kode (seperti MFA TOTP jika belum aktif), atau menampilkan produk placeholder palsu (Unsplash) pada marketplace tanpa filter merchant riil.
3. **Kedaulatan & Kejujuran Konten:** Studi kasus fiktif pada artikel blog wajib diberi label `[Simulasi Model Bisnis]` atau `[Studi Kasus Edukasi]`, dilengkapi disclaimer editorial, serta menampilkan metadata E-E-A-T (Penulis dan Peninjau/Reviewer Ahli).
4. **Navigasi Kanonikal Berbahasa UMKM:** Mega-menu publik tidak menggunakan jargon fintech korporat ("Dynamic Budgeting", "Vendor Spend", "Revenue Sync", "Siap Global Invoice"), melainkan istilah lugas operasional UMKM ("Arus Kas & Pembukuan", "Belanja Bahan & Pemasok", "Rekap Omzet & Laba", "Cetak Struk & Nota Kasir").

---

## 2. Arsitektur Data Flow & View Composer
Penyuntikan data ke seluruh tampilan publik ditangani secara deklaratif melalui Global View Composer di `app/Providers/AppServiceProvider.php`:

```php
View::composer(['public.*', 'layouts.public_marketing', 'landing'], function ($view): void {
    $entitlementService = app(EntitlementService::class);
    $tierLimits = $entitlementService->getAllTierLimits();

    $pricingData = [
        'standard' => [
            'monthly' => (int) (SystemSetting::get('subscription_price_standard_monthly', config('subscriptions.tiers.standard.price_monthly', 39000))),
            'annual'  => (int) (SystemSetting::get('subscription_price_standard_annual', config('subscriptions.tiers.standard.price_annual', 390000))),
        ],
        'premium' => [
            'monthly' => (int) (SystemSetting::get('subscription_price_premium_monthly', config('subscriptions.tiers.premium.price_monthly', 89000))),
            'annual'  => (int) (SystemSetting::get('subscription_price_premium_annual', config('subscriptions.tiers.premium.price_annual', 890000))),
        ],
        'prestige' => [
            'monthly' => (int) (SystemSetting::get('subscription_price_prestige_monthly', config('subscriptions.tiers.prestige.price_monthly', 199000))),
            'annual'  => (int) (SystemSetting::get('subscription_price_prestige_annual', config('subscriptions.tiers.prestige.price_annual', 1990000))),
        ],
    ];

    $view->with([
        'tierLimits'           => $tierLimits,
        'pricingData'          => $pricingData,
        'annualDiscountBadge'  => 'Hemat 2 Bulan',
        'supportSla'           => [
            'working_hours' => SystemSetting::get('support_working_hours', 'Senin - Jumat, 08:30 - 17:30 WIB'),
            'response_time' => SystemSetting::get('support_response_time', '< 15 Menit pada Jam Kerja'),
        ],
    ]);
});
```

---

## 3. Matriks Limitasi & Gating Fitur Paket

| Parameter Sumber Data | Free (Gratis) | Standard (Rp 39.000) | Premium (Rp 89.000) | Prestige (Rp 199.000) |
| :--- | :--- | :--- | :--- | :--- |
| **Harga Bulanan** | Rp 0 | Rp 39.000 / bln | Rp 89.000 / bln | Rp 199.000 / bln |
| **Harga Tahunan** | Rp 0 | Rp 390.000 / thn | Rp 890.000 / thn | Rp 1.990.000 / thn |
| **Kapasitas Produk** | Sesuai limit DB | 100 Item | Unlimited | Unlimited |
| **Transaksi Kasir POS**| Sesuai limit DB | 1.000 Struk / bln | Unlimited | Unlimited |
| **Resep / BOM Kuliner**| 3 Resep Dasar | 20 Resep Menu | Unlimited Resep | Multi-Level BOM Manufaktur |
| **Pengguna / Kasir** | 1 Akun Pemilik | 3 Akun Kasir | Hingga 10 Staf | Hak Akses Role Kustom |
| **Cabang & Gudang** | 1 Toko Tunggal | Hingga 2 Lokasi | Hingga 5 Cabang | Multi-Cabang Skala Penuh |
| **Cloud Storage** | 3 GB | 10 GB | 25 GB | 100 GB |

---

## 4. Halaman Keamanan & Kepatuhan Regulasi (`/security`)
Halaman `/security` (dan redirect 301 dari `/keamanan`) mempublikasikan 5 pilar keamanan terverifikasi yang didukung langsung oleh kode sumber repositori:
1. **Isolasi Data Multi-Tenant yang Ketat:** Kueri database secara otomatis di-scope pada `business_id` pemilik usaha.
2. **Enkripsi Standar Perbankan:** Kata sandi di-hash dengan Bcrypt (`BCRYPT_ROUNDS=12`), kredensial sensitif dienkripsi AES-256-CBC, dan transmisi via TLS 1.3 / HTTPS.
3. **Rekam Jejak Audit Log:** Pencatatan peristiwa krusial pada tabel `audit_logs` (void transaksi kasir, perubahan stok, penyesuaian hak akses).
4. **Proteksi Siber OWASP:** CSRF protection, sanitasi input XSS, parameterized queries anti-SQLi, dan pembatasan frekuensi (*rate limiting*).
5. **Kepatuhan UU PDP No. 27/2022:** Kedaulatan data pemilik usaha dan hak subjek data.
6. **Transparansi Infrastruktur:** Pencadangan data berkala dilakukan melalui *cloud infrastructure volume snapshots*, bukan cron script tidak terpelihara. Fitur MFA TOTP dicantumkan secara jujur dalam *roadmap* mendatang.

---

## 5. Pengujian Otomatis
Pengujian kepatuhan dan integritas pemasaran publik dikawal oleh suite pengujian otomatis:
- `tests/Feature/PublicMarketingConsistencyAndRoutesTest.php` (7 skenario: verifikasi sinkronisasi harga/limit dari DB, pembuktian anti-hardcoding dengan update nilai DB, verifikasi pilar halaman `/security`, redirect 301 `/resources/blog`, bebas 404 pada seluruh tautan mega-menu, metadata E-E-A-T artikel, dan SLA dinamis).
- `tests/Feature/PublicPagesStructureTest.php` (7 skenario struktur halaman publik dan kepatuhan Bento UI).
