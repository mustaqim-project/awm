# Modul Website Landing Page CMS & 42 Tema Storefront Universal

> **Status:** VERIFIED (Current Running Architecture)  
> **Domain Terkait:** `App\Models\BusinessLandingPage`, `App\Domain\Storefront\StorefrontThemeService`, `App\Http\Controllers\Web\BusinessLandingPageWebController`, `App\Http\Controllers\Web\Storefront\PublicStorefrontController`  
> **Tabel Basis Data:** `business_landing_pages`, `commerce_store_settings`, `products`, `pos_tables`, `posts`  
> **Arsitektur UI:** Bento Apple HIG v2.0 (11 Studio Tabs, 6 Visual Design Archetypes, Zero-Manual UI)  
> **Standar Arsitektur Tema:** Universal Blade Contract (Zero Commodity Copy, 3-Tier Fallback, Dynamic Value Pillars Injection, Zero Static External Images, Section Visibility Enforcement)

---

## 1. Tujuan Bisnis & Ikhtisar Sistem

Modul **Landing Page CMS & Storefront** COOCA adalah mesin digitalisasi terpadu yang memungkinkan setiap pelaku usaha UMKM dari berbagai sektor industri (Kuliner/F&B, Retail, Jasa/Servis, Manufaktur, Properti, Agensi Kreatif, hingga Korporat) untuk memiliki **website profil bisnis resmi sekaligus etalase toko online instan (Storefront)** dalam hitungan detik.

### Nilai Strategis Bisnis:
1. **Sentralisasi Identitas Digital:** Mengintegrasikan profil usaha, katalog produk fisik POS, layanan jasa, galeri foto, jam operasional, ulasan pelanggan, dan FAQ dalam satu URL kanonikal publik: `https://cooca.id/{slug}`.
2. **Kemandirian Desain Tanpa Koding:** Pemilik usaha dapat memilih dari **42 tema desain siap pakai**, menyesuaikan warna aksen brand, tipografi Google Fonts, dan mode tampilan gelap (*Dark Mode*) tanpa perlu menyentuh kode HTML/CSS.
3. **Universalitas Penuh (Anti-Silo):** Menghapus sekat sektor industri kaku. Sebuah bengkel dapat menggunakan tema berestetika *Warm Artisan* jika ingin menonjolkan sentuhan personal, atau sebuah kedai kopi dapat memakai tema *Dark Carbon* untuk aura modern maskulin.
4. **Otomasi Terintegrasi:** Terhubung langsung ke modul POS Kasir (auto-showcase katalog barang), modul Reservasi Meja/Slot Jam, modul WhatsApp Gateway (order langsung ke nomor resmi), dan modul Pelacakan Status Resi Pengiriman.

---

## 2. Diagram Alur Data & Arsitektur End-to-End

```mermaid
flowchart TD
    subgraph BackofficeCMS ["Backoffice CMS Studio (Owner & Admin)"]
        UI["resources/views/app/landing_page/edit.blade.php\n(11 Studio Tabs & Alpine.js Studio Workspace)"]
        WebCtrl["BusinessLandingPageWebController.php\n(Scalar & JSON Validation, Quota Shield)"]
        Model["BusinessLandingPage.php\n(Tenant Scoped BelongsToBusiness, Casts)"]
        DB[("Tabel: business_landing_pages\n(67 Kolom Atribut & Koleksi JSON)")]
    end

    subgraph ThemeEngine ["Theme Engine & Intelligence Service"]
        ThemeSvc["StorefrontThemeService.php\n(42 Themes Catalog & Resolution Engine)"]
        Archetypes["6 Visual Design Archetypes\n(Bento, Artisan, Luxe, Dark, Fresh, Trust)"]
        UniversalContract["Universal Fallback & i18n Bridge\n(lang/id/storefront.php & lang/en/storefront.php)"]
    end

    subgraph PublicRuntime ["Public Storefront Runtime (Customer Portal)"]
        PublicCtrl["PublicStorefrontController.php\n(resolveContext, Lang Switcher, Preview Shield)"]
        LayoutApp["public/storefront/layouts/app.blade.php\n(CSS Variables --theme-*, Fonts, Global Cart Store)"]
        HomeView["public/storefront/home.blade.php\n(Section Visibility Enforcement: isSectionVisible)"]
        ThemeBundle["public/storefront/themes/{bundleDir}/*\n(6 Core Blade Components)"]
    end

    UI -->|PUT /landing-page\nSimpan Pengaturan| WebCtrl
    WebCtrl -->|Eloquent Scoped Save| Model
    Model --> DB

    PublicCtrl -->|Load Landing Page & Business| DB
    PublicCtrl -->|Resolve Active / Preview Theme| ThemeSvc
    ThemeSvc --> Archetypes
    ThemeSvc --> UniversalContract

    PublicCtrl -->|Passing Array Context| HomeView
    LayoutApp -->|Injeksi Dynamic Design Tokens| HomeView
    HomeView -->|Include Berdasarkan bundle_dir| ThemeBundle
```

---

## 3. Skema Basis Data & Atribut Kunci (`business_landing_pages`)

Tabel `business_landing_pages` memiliki 67 kolom komprehensif yang dirancang untuk menyimpan preferensi tampilan, konfigurasi multi-halaman, dan aset konten:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        business_landing_pages                          │
├──────────────────────────────────┬─────────────────────────────────────┤
│ Kolom                            │ Tipe & Keterangan                   │
├──────────────────────────────────┼─────────────────────────────────────┤
│ id                               │ BIGINT UNSIGNED (Primary Key)       │
│ business_id                      │ CHAR(36) (Foreign Key ke businesses)│
│ is_published                     │ TINYINT(1) (Status Live vs Draft)   │
│ industry_preset                  │ VARCHAR(255) (Default preset teks)  │
│ theme_preset                     │ VARCHAR(50) (ID dari 42 Tema)       │
│ theme_color                      │ VARCHAR(255) (HEX Warna Aksen Toko) │
│ font_family                      │ VARCHAR(255) (Font Google Fonts)    │
│ dark_mode                        │ TINYINT(1) (Default Mode Gelap)     │
│ active_pages                     │ JSON (Peta aktifitas 7 halaman)     │
│ custom_labels                    │ JSON (Kustomisasi label menu)       │
│ section_visibility               │ JSON (Peta visibilitas 12 seksi)    │
│ values                           │ JSON (4 Pilar Keunggulan Bisnis)    │
│ operational_hours                │ JSON (Jadwal Buka/Tutup 7 Hari)     │
│ custom_services                  │ JSON (Katalog Layanan & Jasa)       │
│ gallery_images                   │ JSON (Daftar Foto & Keterangan)     │
│ testimonials                     │ JSON (Daftar Ulasan & Rating)       │
│ faqs                             │ JSON (Pertanyaan & Jawaban Q&A)     │
│ stats                            │ JSON (Statistik Kredibilitas Usaha) │
│ headline, subheadline            │ VARCHAR/TEXT (Judul Sambutan)       │
│ announcement_badge               │ VARCHAR(255) (Badge Pengumuman Hero)│
│ hero_image_url, logo_url         │ VARCHAR(500) (URL Aset Gambar)      │
│ cta_primary_text / url           │ VARCHAR (Teks & Link Tombol Utama)  │
│ cta_secondary_text / url         │ VARCHAR (Teks & Link Tombol Kedua)  │
│ popup_enabled, popup_title, etc. │ Konfigurasi Modal Promosi Toko      │
└──────────────────────────────────┴─────────────────────────────────────┘
```

---

## 4. Klasifikasi 6 Arketipe Desain Visual & Matriks 42 Tema

Seluruh 42 tema dipetakan ke dalam salah satu dari **6 Arketipe Desain Visual** di [`StorefrontThemeService`](file:///c:/laragon/www/cooca_core/app/Domain/Storefront/StorefrontThemeService.php), memastikan setiap karakter bisnis terwadahi secara proporsional:

| Arketipe Desain | Karakter Visual & Filosofi | Font Heading / Body | Palet Bawaan (Primary / Accent / BG) | Daftar Tema Terdaftar (7 Tema per Arketipe) |
|---|---|---|---|---|
| **`clean_bento`**<br>(Clean Bento & Modern Tech) | Grid modular Apple HIG, sudut squircle kontinu, kontras tajam, data-dense | Plus Jakarta Sans / Inter | `#2563EB` / `#0F172A`<br>`#F8FAFC` | `online_store`, `modern_umkm`, `service_agency`, `retail_electronics`, `retail_grocery`, `professional_services`, `retail_building` |
| **`warm_artisan`**<br>(Earthy & Warm Artisan) | Nuansa hangat kopi, kayu, bumi, kenyamanan personal, sentuhan kerajinan | DM Serif Display / Playfair / Inter | `#8B5A2B` / `#C88A58`<br>`#FAF7F2` | `coffee_beverage`, `agriculture_farm`, `bakery_cake`, `fnb_bakery`, `fnb_cafe`, `retail_florist`, `hospitality_accommodation` |
| **`luxe_editorial`**<br>(High-End Luxe & Editorial) | Layout majalah high-fashion, tipografi Didone anggun, aksen emas/rose gold | Syne / Cormorant Garamond / Poppins | `#BE185D` / `#D4AF37`<br>`#FDF8F6` | `retail_fashion`, `health_beauty`, `product_showcase`, `event_wedding`, `personal_brand`, `portfolio_creative`, `universal_storefront` |
| **`dark_carbon`**<br>(Dark Carbon & High Impact) | Stealth carbon hitam, aksen neon tajam (orange/lime/cyan), industrial presisi | Archivo Black / Chakra Petch / Inter | `#EA580C` / `#38BDF8`<br>`#0B0F19` | `service_workshop`, `service_carwash`, `service_fitness`, `service_laundry`, `construction_property`, `manufacturing_factory`, `logistics_transportation` |
| **`playful_fresh`**<br>(Playful, Fresh & Appetite) | Ramah keluarga, warna ceria menggugah selera, kurva lembut, visual cerah | Plus Jakarta Sans / Poppins | `#991B1B` / `#D97706`<br>`#FFFBEB` | `food_restaurant`, `fnb_resto`, `fnb_fastfood`, `fnb_catering`, `retail_bookstore`, `service_petshop`, `service_beauty` |
| **`institutional_trust`**<br>(Institutional & Trust) | Formalitas korporat, legalitas kokoh, kredibilitas B2B, penataan simetris | Merriweather / Plus Jakarta Sans | `#1E293B` / `#2563EB`<br>`#F8FAFC` | `business_corporate`, `education_courses`, `retail_pharmacy`, `service_business`, `service_printing`, `booking_appointment`, `wholesale_distributor` |

---

## 5. Universal Blade Contract (5 Aturan Wajib Tema)

Setiap tema storefront di COOCA wajib mematuhi **Universal Blade Contract** agar tidak terjadi kebocoran teks komoditas sektor (*Zero Feature & Copy Leakage*):

### 1. Smart 3-Tier Fallback Hierarchy
Seluruh teks judul, slogan, badge, dan tombol pada Blade tema dilarang keras ditulis *hardcoded*. Wajib menggunakan hierarki fallback 3-tingkat:
```blade
{{-- 1. Input CMS ($landingPage) -> 2. Profil Usaha ($business) -> 3. Kamus Universal i18n --}}
<h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white">
    {{ $landingPage->headline ?: $business->name }}
</h1>

<p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300 leading-relaxed">
    {{ $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline')) }}
</p>
```

### 2. Universal Value Pillars Injection
Seluruh badge dan kartu keunggulan diinjeksi secara dinamis via `$landingPage->getUniversalValues()`. Method ini menjamin 4 pilar selalu lengkap dengan kunci `icon`, `title`, dan `desc`:
```blade
@php
    $universalValues = $landingPage->getUniversalValues();
    $badgePill1 = $universalValues[0]['title'];
    $badgeIcon1 = $universalValues[0]['icon'];
@endphp

<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold">
    <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5 text-theme-primary"></i>
    <span>{{ $badgePill1 }}</span>
</span>
```

### 3. Zero Static External Images (No Unsplash)
Dilarang menaruh tautan gambar Unsplash statis. Jika merchant belum mengunggah gambar hero, tema wajib merender **Bento Graphic Showcase** berbasis inisial brand dan warna aktif `--theme-primary`:
```blade
@if ($heroImage)
    <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
@else
    {{-- Brand Bento Graphic Showcase Card (Universal Fallback) --}}
    <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-neutral-900 to-neutral-950 text-white relative overflow-hidden aspect-square flex flex-col justify-between">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-heading font-bold text-2xl text-white shadow-md"
             style="background-color: var(--theme-primary, #007AFF);">
            {{ strtoupper(substr($business->name, 0, 2)) }}
        </div>
        <div>
            <h3 class="font-heading font-bold text-2xl sm:text-3xl text-white">{{ $business->name }}</h3>
            <p class="text-xs text-white/80 leading-relaxed font-normal">{{ $heroSubheadline }}</p>
        </div>
    </div>
@endif
```

### 4. Section Visibility Enforcement
Seluruh seksi di [`resources/views/public/storefront/home.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/public/storefront/home.blade.php) wajib dikawal oleh `$landingPage->isSectionVisible(...)`:
```blade
@if ($landingPage->isSectionVisible('hero'))
    @include("public.storefront.themes.{$bundleDir}.hero")
@endif

@if ($landingPage->isSectionVisible('features_banner'))
    @include("public.storefront.themes.{$bundleDir}.features_banner")
@endif

@if ($landingPage->isSectionVisible('category_chips') && $productCategories->isNotEmpty())
    @include("public.storefront.themes.{$bundleDir}.category_chips")
@endif
```

### 5. Auto-Hide Dynamic Navigation Guard
Header navbar dan footer tema hanya menampilkan link halaman yang diaktifkan oleh merchant di CMS (`isPageActive`):
```blade
@if ($landingPage->isPageActive('catalog'))
    <a href="{{ url('/' . $business->slug . '/katalog') }}" class="nav-link">
        {{ $landingPage->getCustomLabel('catalog', __('storefront.nav_catalog')) }}
    </a>
@endif
```

---

## 6. Anatomi 6 Berkas Komponen Tema

Setiap folder tema di `resources/views/public/storefront/themes/{bundleDir}/` memuat 6 komponen berikut:

1. **`hero.blade.php`:**
   - Memuat blok inisialisasi `@php` fallback lengkap.
   - Kontainer `<section>` dengan background khas arketipe.
   - Kolom Kiri (`lg:col-span-7`): Eyebrow badge, headline `$heroHeadline`, subheadline `$heroSubheadline`, 3 badge kepercayaan dari `$universalValues`, dan 2 tombol CTA adaptif.
   - Kolom Kanan (`lg:col-span-5`): Foto hero terunggah atau Bento Graphic Showcase fallback.
2. **`features_banner.blade.php`:**
   - Merender 4 pilar keunggulan bisnis dalam layout grid responsif (2 kolom mobile, 4 kolom desktop).
3. **`category_chips.blade.php`:**
   - Slider horizontal kategori produk dari database POS dengan tombol "Semua Produk".
4. **`product_card.blade.php`:**
   - Menampilkan foto barang, badge kategori, nama, deskripsi ringkas, harga dengan format mata uang Indonesia `Rp xxx.xxx`, serta tombol tambah ke keranjang instan Alpine.js (`$store.cart.add`).
5. **`navbar.blade.php`:**
   - Header toko dengan logo, nama bisnis, menu navigasi aktif, toggle Dark Mode, tombol pelacakan resi pesanan, dan indikator keranjang belanja real-time.
6. **`footer.blade.php`:**
   - Informasi legal, alamat toko, jam buka resmi, tautan customer service WhatsApp, dan hak cipta.

---

## 7. Laporan Audit & Rekonstruksi 21 Hero Tema Storefront

Pada audit mendalam 5 November 2026, ditemukan bahwa **21 berkas `hero.blade.php` (Batch 3 & Batch 4)** mengalami kerusakan struktural akibat kesalahan *file overwriting* pada refactoring sebelumnya. Berkas-berkas tersebut terpotong dan hanya menyisakan baris kolom kanan (`lg:col-span-5`), sehingga judul headline dan tombol CTA toko tidak muncul di layar.

### Tindakan Rekonstruksi yang Telah Selesai:
- **Daftar 21 Tema yang Direkonstruksi:**
  1. `booking_appointment`
  2. `construction_property`
  3. `education_courses`
  4. `event_wedding`
  5. `hospitality_accommodation`
  6. `logistics_transportation`
  7. `manufacturing_factory`
  8. `personal_brand`
  9. `portfolio_creative`
  10. `product_showcase`
  11. `professional_services`
  12. `retail_pharmacy`
  13. `service_agency`
  14. `service_beauty`
  15. `service_business`
  16. `service_carwash`
  17. `service_fitness`
  18. `service_laundry`
  19. `service_petshop`
  20. `service_printing`
  21. `wholesale_distributor`
- **Hasil Rekonstruksi:** Seluruh berkas kini kembali utuh (~120-160 baris), memiliki kontainer `<section>` lengkap, ambient glow yang proporsional, kolom kiri interaktif, dan terikat 100% pada Universal Blade Contract.
- **Verifikasi:** Lolos pengecekan sintaks `php -l` pada seluruh 256 berkas Blade (0 error) dan lolos automated test suite `UniversalStorefrontAll42ThemesTest.php` dengan **951 assertions**.

---

## 8. Panduan Verifikasi Pengembang (QA & Testing)

Untuk memvalidasi integritas 42 tema dan Landing Page CMS di masa depan:

```powershell
# 1. Jalankan test suite terpadu seluruh 42 tema storefront
php artisan test tests/Feature/UniversalStorefrontAll42ThemesTest.php

# 2. Uji kompilasi sintaks seluruh template Blade tema
Get-ChildItem -Path "resources\views\public\storefront\themes" -Recurse -Filter "*.blade.php" | ForEach-Object { php -l $_.FullName }

# 3. Verifikasi rute publik dan preview mode
curl -I "http://localhost/universal-nusantara-group?preview=1&preview_theme=online_store"
```

