# Analisis Mendalam: Antarmuka CMS Cockpit & Test Suite Storefront Universal COOCA

**Tanggal Investigasi**: 5 November 2026  
**Peneliti**: Teamwork Explorer (Survey 3 - CMS Cockpit & Test Suite)  
**Tujuan**: Menginvestigasi antarmuka CMS Cockpit (`BusinessLandingPageWebController` & `edit.blade.php`), mengevaluasi test suite yang ada, menganalisis perintah eksekusi pengujian, dan merancang kasus uji baru untuk universal fallback, 6 arketipe desain, dan penegakan visibilitas seksi.

---

## 1. Executive Summary

Platform SaaS ERP COOCA saat ini memiliki 42 tema storefront yang terdaftar di `App\Domain\Storefront\StorefrontThemeService`. Namun, saat ini terdapat beberapa keterbatasan struktural dan celah integrasi antara **CMS Cockpit** (`resources/views/app/landing_page/edit.blade.php`) dan **Storefront Publik** (`resources/views/public/storefront/home.blade.php`):

1. **CMS Cockpit Controller & Routing**: Antarmuka CMS dikendalikan oleh `App\Http\Controllers\Web\BusinessLandingPageWebController` (bukan di namespace `App\Http\Controllers\App`), yang dipetakan pada `routes/owner.php` dengan prefix `/owner/landing-page`.
2. **Kategori Tema Masih Berbasis Silo Industri**: Sub-Card 5 pada `edit.blade.php` saat ini mengelompokkan 42 tema ke dalam 6 kategori industri kaku (`fnb`, `retail`, `service`, `industry`, `lifestyle`, `corporate`), bukan berdasarkan **6 Arketipe Desain Visual** (*Clean Bento & Modern Tech, Earthy & Warm Artisan, High-End Luxe & Editorial, Dark Carbon & High Impact, Playful Fresh & Appetite, Institutional & Trust*).
3. **Hilangnya Kontrol Saklar (Toggle) untuk Dua Seksi Kritis**: CMS Cockpit memiliki toggle visibilitas untuk hero, about, products, services, gallery, testimonials, faq, contact, dan footer. Namun, **Features Banner (4 Pilar)** dan **Category Chips** TIDAK memiliki saklar toggle di form CMS, dan model `BusinessLandingPage` belum memiliki helper `isSectionVisible(string $section)`.
4. **Halaman Publik Belum Menegakkan Visibilitas Penuh**: File `resources/views/public/storefront/home.blade.php` merender seksi Hero, Features Banner, Category Chips, Products, Services, dan About tanpa memeriksa `isSectionVisible()`.
5. **Kesiapan Test Suite**: Suite pengujian berbasis PHPUnit 11 dengan SQLite in-memory (`RefreshDatabase`). Telah ada tes yang menguji 42 tema (`StorefrontThemeCustomizationTest.php`), namun **belum ada tes** yang memvalidasi *smart universal fallback*, eliminasi kebocoran teks sektor (*zero hardcoded sector text*), klasifikasi 6 arketipe, maupun penegakan visibilitas seksi features banner & category chips.

---

## 2. Investigasi Mendalam CMS Cockpit Interface

### 2.1 Arsitektur Controller & Data Flow

- **File Controller**: `app/Http/Controllers/Web/BusinessLandingPageWebController.php`
- **Routing**: Dideklarasikan di `routes/owner.php` (baris 1012–1015):
  ```php
  Route::get('/', [BusinessLandingPageWebController::class, 'edit'])->name('edit');
  Route::put('/', [BusinessLandingPageWebController::class, 'update'])->name('update');
  Route::post('/preset', [BusinessLandingPageWebController::class, 'applyPreset'])->name('preset');
  Route::post('/toggle-publish', [BusinessLandingPageWebController::class, 'togglePublish'])->name('toggle-publish');
  ```
- **Method `edit()`**:
  - Mengambil atau membuat record `BusinessLandingPage::firstOrCreate(['business_id' => $business->id])`.
  - Jika baru dibuat atau kosong, secara otomatis mengisi default preset teks (tanpa foto stok eksternal).
  - Menyuntikkan `$themePresets = $themeService->getAllThemes()` dan `$curatedFonts = array_keys($themeService->getCuratedFonts())`.
- **Method `update(Request $request)`**:
  - Memvalidasi dan menyimpan data skalar secara defensif (`$request->has($scalarField)`).
  - Menyimpan koleksi JSON untuk:
    - `values_json` &rarr; kolom `values` (array 4 pilar keunggulan).
    - `section_visibility_json` &rarr; kolom `section_visibility` (array boolean visibilitas seksi).
    - `custom_services_json`, `faqs_json`, `testimonials_json`, `operational_hours_json`.
  - Mengelola upload gambar hero, logo, about, og_image, dan galeri dengan kuota tenant storage.

### 2.2 Analisis Tampilan CMS: `resources/views/app/landing_page/edit.blade.php`

Halaman editor CMS adalah view Blade monolitik berdesain Apple HIG Bento (~3.336 baris kode) yang digerakkan oleh Alpine.js (`landingPageEditor()`).

#### A. Sub-Card 5: Koleksi Tema (Baris 372–620)
- **Kondisi Eksisting**:
  - Baris 381–435: Menggunakan array PHP `$themeCategoryMap` yang membagi 42 tema ke:
    - `fnb` (Kuliner & Minuman - 8 tema)
    - `retail` (Retail & Toko - 11 tema)
    - `service` (Jasa & Reservasi - 10 tema)
    - `industry` (Industri, Logistik, Properti & Agro - 4 tema)
    - `lifestyle` (Kesehatan, Kebugaran, Edukasi - 4 tema)
    - `corporate` (Korporat & Universal - 5 tema)
  - Baris 517–553: Tombol segmented tab menyaring tema berdasarkan kategori industri tersebut.
- **Masalah / Gap**:
  - Menjebak pemilik bisnis dalam ilusi bahwa tema terikat pada sektor bisnis tertentu (misal: pengusaha butik atau farmasi mengira tidak boleh memakai tema dark carbon atau warm artisan).
  - Bertentangan dengan cetak biru standarisasi Universal Storefront.
- **Rencana Perbaikan**:
  - Restrukturisasi `$themeCategoryMap` dan tab filter Alpine.js menjadi **6 Arketipe Desain**:
    1. `clean_bento` (Clean Bento & Modern Tech - 8 tema)
    2. `warm_artisan` (Earthy & Warm Artisan - 7 tema)
    3. `luxe_editorial` (High-End Luxe & Editorial - 6 tema)
    4. `dark_carbon` (Dark Carbon & High Impact - 7 tema)
    5. `playful_fresh` (Playful, Fresh & Appetite - 7 tema)
    6. `institutional` (Institutional & Trust - 7 tema)
  - Tambahkan badge di Sub-Card 5: *"Universal & Fleksibel untuk Segala Jenis Usaha"*.
  - Tambahkan copy edukatif yang menjelaskan bahwa tema adalah preferensi rasa estetika (*aesthetic taste*), bukan batasan jenis barang yang dijual.

#### B. Tab 3: Profil & Keunggulan / Values (Baris 1013–1055)
- **Kondisi Eksisting**:
  - Mengelola array `values` (4 pilar: `title`, `description`, `icon`).
  - Belum ada edukasi bahwa 4 nilai keunggulan ini adalah **Single Source of Truth** yang langsung menjadi Badges di Hero Section dan Kartu Fitur di `features_banner.blade.php`.
- **Rencana Perbaikan**:
  - Tambahkan callout/petunjuk informasi:
    *"💡 Catatan: 3–4 Nilai Keunggulan di sini akan otomatis disinkronkan menjadi Badges Kepercayaan di Banner Hero dan Kartu 4 Pilar Fitur pada tema toko Anda."*

#### C. Saklar Visibilitas Seksi (Section Visibility)
- **Kondisi Eksisting**:
  - Diatur melalui Alpine.js `sectionVisibility` dan diinput ke hidden input `section_visibility_json`.
  - Default yang terdefinisi pada baris 2575–2592 hanya mencakup:
    `hero`, `about`, `products`, `services`, `gallery`, `testimonials`, `faq`, `contact`, `footer` (+ `footer_brand`, `footer_navigation`, `footer_services`, `footer_contact`).
  - **Dua seksi kritis HILANG**:
    1. `features_banner` (Seksi 4 Pilar Fitur)
    2. `category_chips` (Seksi Shortcut Kategori Produk)
- **Rencana Perbaikan**:
  - Tambahkan `features_banner => true` dan `category_chips => true` ke `$sectionVisibilityDefaults`.
  - Tambahkan kontrol switch/checkbox di UI:
    - Di Tab 3 (atau Tab 2): Switch *"Tampilkan Banner 4 Pilar Keunggulan di Storefront"* (`x-model="sectionVisibility.features_banner"`).
    - Di Tab 4/Tab 5: Switch *"Tampilkan Shortcut Kategori Produk/Layanan (Category Chips)"* (`x-model="sectionVisibility.category_chips"`).

### 2.3 Model Gap: `App\Models\BusinessLandingPage`

Pada file `app/Models/BusinessLandingPage.php`:
- Kolom `section_visibility` sudah tercatat di `$fillable` (baris 49) dan di-cast ke `'array'` (baris 97).
- Sudah terdapat helper `isPageActive(string $page): bool` (baris 126–130) dan `getActivePages(): array`.
- **GAP**: Helper `isSectionVisible(string $section, bool $default = true): bool` **BELUM ADA**.
- **Solusi yang Dibutuhkan**:
  ```php
  /**
   * Determine whether a storefront homepage section is visible.
   */
  public function isSectionVisible(string $section, bool $default = true): bool
  {
      $visibility = (array) ($this->section_visibility ?? []);
      if (! array_key_exists($section, $visibility)) {
          return $default;
      }
      return (bool) $visibility[$section];
  }
  ```

### 2.4 Storefront Gap: `resources/views/public/storefront/home.blade.php`

Pada file `resources/views/public/storefront/home.blade.php`:
- Baris 50–56: Hero dirender tanpa cek `isSectionVisible('hero')`.
- Baris 61–66: Features Banner dirender tanpa cek `isSectionVisible('features_banner')`.
- Baris 70–76: Category Chips dirender tanpa cek `isSectionVisible('category_chips')`.
- Baris 81: Featured Products dirender hanya memeriksa `$featuredProducts->isNotEmpty()`, tanpa memeriksa `isSectionVisible('products')`.
- Baris 121: Services highlight hanya memeriksa `$services->isNotEmpty()`, tanpa memeriksa `isSectionVisible('services')`.
- Baris 237: About teaser hanya memeriksa status halaman about, tanpa memeriksa `isSectionVisible('about')`.
- **Solusi yang Dibutuhkan**:
  Setiap seksi di `home.blade.php` wajib dibungkus dengan klausul `@if ($landingPage->isSectionVisible('...'))`.

---

## 3. Inventarisasi & Evaluasi Test Suite Eksisting

### 3.1 Daftar Test Terkait Storefront & Landing Page

| No | File Path | Deskripsi & Cakupan |
|---|---|---|
| 1 | `tests/Feature/StorefrontThemeCustomizationTest.php` | Menguji rendering editor CMS, update tema/warna/font, rendering tema di storefront publik, live preview tanpa simpan, dan iterasi verifikasi HTTP 200 pada seluruh 42 tema. |
| 2 | `tests/Feature/PublicStorefrontMultiPageThemeTest.php` | Menguji navigasi multi-halaman (`home`, `catalog`, `about`, `contact`, `blog`, `tracking`), injeksi CSS variable token (`--theme-primary`), filter katalog, PDP meta tags, dan auto-hide routing. |
| 3 | `tests/Feature/PublicStorefrontFieldScenariosTest.php` | Menguji skenario nyata multi-sektor: Reservasi meja F&B, pemesanan takeaway, checkout dengan metode pembayaran QRIS & Bank Transfer. |
| 4 | `tests/Feature/StorefrontIndustryAdaptiveReservationTest.php` | Menguji penyesuaian terminologi dan alur reservasi berdasarkan tipe industri. |
| 5 | `tests/Feature/StorefrontPopupCmsTest.php` | Menguji modal promosi pop-up storefront yang dikonfigurasi via CMS beserta frekuensi penayangannya. |
| 6 | `tests/Feature/StorefrontSeoAndSocialSharingTest.php` | Menguji metadata OpenGraph, canonical URL, dan skema JSON-LD Schema.org pada storefront. |
| 7 | `tests/Feature/CanonicalStorefrontSlugRoutingTest.php` | Menguji resolusi slug toko dan routing kanonikal multi-tenant. |
| 8 | `tests/Feature/LandingPageAuthTest.php` | Menguji halaman landing platform SaaS COOCA untuk guest vs owner vs admin. |
| 9 | `tests/Feature/LandingPageResponsiveHeroTest.php` | Menguji responsive viewport unit (100svh), trust badge, dan 3D visual hub pada landing page platform. |

### 3.2 Analisis Konfigurasi & Performa Eksekusi Tes

- **Konfigurasi Lingkungan (`phpunit.xml`)**:
  - `DB_CONNECTION`: `sqlite`
  - `DB_DATABASE`: `:memory:`
  - `APP_ENV`: `testing`
  - `APP_LOCALE`: `id`
  - Test runner: PHPUnit 11 (dijalankan via `php artisan test` atau `vendor/bin/phpunit`).
- **Skrip di `composer.json`**:
  ```json
  "test": [
      "@php artisan config:clear --ansi @no_additional_args",
      "@php artisan test"
  ]
  ```
- **Karakteristik & Bottleneck Eksekusi**:
  - Repositori COOCA memiliki **241 file migrasi database**.
  - Trait `RefreshDatabase` mengeksekusi ke-241 migrasi pada setiap inisialisasi TestCase.
  - Tes `StorefrontThemeCustomizationTest::test_all_42_themes_resolve_and_render_without_errors` merender seluruh 42 tema secara berurutan dalam satu test method. Pada sistem Windows, proses ini membutuhkan waktu sekitar 40–60 detik CPU time.
  - **Rekomendasi Operasional**: Gunakan filter terarah saat pengembangan dan verifikasi:
    ```powershell
    php artisan test --filter=StorefrontThemeCustomizationTest
    ```

---

## 4. Kebutuhan Kasus Uji Baru (New Test Cases & Assertions)

Untuk menjamin keberhasilan transformasi Universal Storefront tanpa regresi, perlu ditambahkan file pengujian baru: `tests/Feature/StorefrontUniversalContractTest.php` atau perluasan langsung pada `StorefrontThemeCustomizationTest.php`.

Berikut rincian 5 kasus uji baru yang wajib diimplementasikan:

### Kasus Uji 1: Smart Universal Content Fallback Hierarchy
Memastikan jika input CMS kosong, storefront secara cerdas mengambil profil master bisnis, dan jika profil bisnis kosong, mengambil translasi universal i18n (`storefront.*`).

```php
public function test_smart_universal_fallback_hierarchy_when_cms_fields_are_null(): void
{
    // 1. Kosongkan field CMS pada BusinessLandingPage
    $this->landingPage->update([
        'headline' => null,
        'subheadline' => null,
        'announcement_badge' => null,
        'cta_primary_text' => null,
        'cta_primary_url' => null,
        'cta_secondary_text' => null,
        'cta_secondary_url' => null,
        'values' => null,
        'hero_image_url' => null,
    ]);

    $response = $this->get('/' . $this->business->slug);
    $response->assertOk();

    // 2. Assert Headline fallback ke nama bisnis
    $response->assertSee($this->business->name);

    // 3. Assert Subheadline fallback ke deskripsi bisnis atau i18n
    $expectedSub = $this->business->description ?: __('storefront.hero.tagline');
    $response->assertSee(e($expectedSub));

    // 4. Assert CTA primer fallback ke katalog
    $response->assertSee(url('/' . $this->business->slug . '/katalog'));

    // 5. Assert 4 pilar fallback ke default universal translation
    $response->assertSee(__('storefront.features.official_store'));
    $response->assertSee(__('storefront.features.easy_payment'));
    $response->assertSee(__('storefront.features.secure_delivery'));
    $response->assertSee(__('storefront.features.customer_care'));

    // 6. Assert Fallback Graphic Bento (Monogram nama bisnis) tampil menggantikan gambar Unsplash
    $initials = strtoupper(substr($this->business->name, 0, 2));
    $response->assertSee($initials);
}
```

### Kasus Uji 2: Zero Hardcoded Sector Text Leakage
Memastikan tidak ada teks statis industri spesifik yang bocor saat bisnis netral/lintas sektor memakai tema tertentu (misal: Toko Alat Kesehatan memakai tema `service_workshop` atau `coffee_beverage`).

```php
public function test_zero_hardcoded_sector_text_leakage_across_themes(): void
{
    $bannedSectorPhrases = [
        'Tune-up Mesin',
        'Bongkar Pasang',
        'Petik Segar Subuh',
        'Resep Warisan Nusantara',
        'Jahitan Rapi & Fitting',
        'Sewa Kamar',
        'Biji Sangrai Kopi',
        'Ganti Oli Mesin',
        'Pangkas Rambut',
    ];

    $sampleThemes = [
        'service_workshop',
        'coffee_beverage',
        'retail_fashion',
        'agriculture_farm',
        'food_restaurant',
        'bakery_cake',
    ];

    foreach ($sampleThemes as $themeKey) {
        $response = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get('/' . $this->business->slug . '?preview=1&preview_theme=' . $themeKey);

        $response->assertOk();

        foreach ($bannedSectorPhrases as $bannedPhrase) {
            $response->assertDontSee(
                $bannedPhrase,
                false,
                "Tema '{$themeKey}' membocorkan teks sektor hardcoded: '{$bannedPhrase}'"
            );
        }
    }
}
```

### Kasus Uji 3: Penegakan Section Visibility di Storefront Home
Memastikan saklar toggle `section_visibility` dihormati 100% oleh `home.blade.php`.

```php
public function test_storefront_respects_section_visibility_toggles(): void
{
    // A. Saat features_banner & category_chips di-hide
    $this->landingPage->update([
        'section_visibility' => [
            'hero' => true,
            'features_banner' => false,
            'category_chips' => false,
            'products' => false,
            'services' => false,
            'about' => false,
        ],
    ]);

    $response = $this->get('/' . $this->business->slug);
    $response->assertOk();

    // Features banner container tidak boleh dirender
    $response->assertDontSee(__('storefront.features.official_store'));
    // Category chips tidak boleh dirender
    $response->assertDontSee(__('storefront.kategori_pilihan'));

    // B. Saat features_banner & category_chips di-enable kembali
    $this->landingPage->update([
        'section_visibility' => [
            'hero' => true,
            'features_banner' => true,
            'category_chips' => true,
            'products' => true,
            'services' => true,
            'about' => true,
        ],
    ]);

    $responseActive = $this->get('/' . $this->business->slug);
    $responseActive->assertOk();
    $responseActive->assertSee(__('storefront.features.official_store'));
}
```

### Kasus Uji 4: Klasifikasi 6 Arketipe Desain pada Service
Memastikan `StorefrontThemeService` memiliki metode pengelompokan yang valid untuk ke-42 tema ke dalam 6 arketipe desain visual.

```php
public function test_storefront_theme_service_classifies_42_themes_into_6_archetypes(): void
{
    $service = app(\App\Domain\Storefront\StorefrontThemeService::class);
    $allThemes = $service->getAllThemes();
    $this->assertCount(42, $allThemes);

    $validArchetypes = [
        'clean_bento',
        'warm_artisan',
        'luxe_editorial',
        'dark_carbon',
        'playful_fresh',
        'institutional',
    ];

    $archetypeCounts = array_fill_keys($validArchetypes, 0);

    foreach (array_keys($allThemes) as $themeKey) {
        $archetype = $service->getThemeArchetype($themeKey);
        $this->assertContains(
            $archetype,
            $validArchetypes,
            "Tema '{$themeKey}' memiliki arketipe tidak dikenal: '{$archetype}'"
        );
        $archetypeCounts[$archetype]++;
    }

    // Pastikan distribusi 42 tema sesuai spesifikasi blueprint: 8 + 7 + 6 + 7 + 7 + 7 = 42
    $this->assertSame(8, $archetypeCounts['clean_bento']);
    $this->assertSame(7, $archetypeCounts['warm_artisan']);
    $this->assertSame(6, $archetypeCounts['luxe_editorial']);
    $this->assertSame(7, $archetypeCounts['dark_carbon']);
    $this->assertSame(7, $archetypeCounts['playful_fresh']);
    $this->assertSame(7, $archetypeCounts['institutional']);
}
```

### Kasus Uji 5: Integrasi CMS Cockpit Update & Section Visibility Persistence
Memastikan form CMS dapat mengirim dan menyimpan toggle `features_banner` dan `category_chips` ke database melalui controller.

```php
public function test_owner_can_persist_features_banner_and_category_chips_visibility(): void
{
    $payload = [
        'section_visibility_json' => json_encode([
            'hero' => true,
            'features_banner' => false,
            'category_chips' => false,
            'about' => true,
            'products' => true,
            'services' => true,
        ]),
    ];

    $response = $this->actingAs($this->owner, 'web')
        ->withSession(['active_business_id' => $this->business->id])
        ->put(route('landing-page.update'), $payload);

    $response->assertRedirect();

    $this->landingPage->refresh();
    $this->assertFalse($this->landingPage->isSectionVisible('features_banner'));
    $this->assertFalse($this->landingPage->isSectionVisible('category_chips'));
    $this->assertTrue($this->landingPage->isSectionVisible('hero'));
}
```

---

## 5. Ringkasan Matriks Gap & Rekomendasi Aksi

| Komponen | Status Saat Ini | Tindakan yang Dibutuhkan | Target File |
|---|---|---|---|
| **Model Helper** | Tidak ada `isSectionVisible()` | Tambahkan method `isSectionVisible(string $section, bool $default = true): bool` | `app/Models/BusinessLandingPage.php` |
| **Service Helper** | Tidak ada klasifikasi arketipe | Tambahkan method `getThemeArchetype(string $themeKey): string` dan konstanta matriks 6 arketipe | `app/Domain/Storefront/StorefrontThemeService.php` |
| **Storefront Home** | Merender seksi tanpa guard visibilitas | Pasang `@if ($landingPage->isSectionVisible(...))` pada seksi Hero, Features Banner, Category Chips, Products, Services, dan About | `resources/views/public/storefront/home.blade.php` |
| **CMS Sub-Card 5** | Tab filter tema berbasis 6 industri lama | Ganti dengan segmented tabs 6 Arketipe Desain, pasang badge "Universal & Fleksibel", serta copy edukasi | `resources/views/app/landing_page/edit.blade.php` |
| **CMS Section Toggles** | Hanya 9 seksi umum, tidak ada features_banner & category_chips | Tambahkan checkbox toggle untuk `features_banner` dan `category_chips` serta daftarkan di `$sectionVisibilityDefaults` | `resources/views/app/landing_page/edit.blade.php` |
| **CMS Tab 3 Copy** | Tidak ada petunjuk kaitan 4 pilar ke tema | Tambahkan callout petunjuk bahwa 4 pilar langsung menjadi Badges di Hero dan Kartu di Features Banner | `resources/views/app/landing_page/edit.blade.php` |
| **Test Suite** | Belum ada tes universal contract & zero leakage | Tambahkan file test `tests/Feature/StorefrontUniversalContractTest.php` dengan 5 skenario komprehensif | `tests/Feature/StorefrontUniversalContractTest.php` |

---
*Dokumen analisis ini telah disusun secara lengkap dan siap dijadikan rujukan operasional pada Fase Implementasi.*
