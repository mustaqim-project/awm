# Laporan Handoff: Investigasi CMS Cockpit & Test Suite Storefront Universal

**Handoff Type**: Hard (Investigation Complete)  
**Agent ID**: `teamwork_explorer_survey_3`  
**Parent Agent ID**: `3e6ffd97-6534-466f-a0f2-f2278f17c1fe`  
**Target Reference**: `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_explorer_survey_3\analysis.md`

---

## 1. Observation

1. **CMS Cockpit Controller Location & Routes**:
   - Controller berada di `app/Http/Controllers/Web/BusinessLandingPageWebController.php` (bukan di `App/Http/Controllers/App/LandingPageController.php`).
   - Rute didefinisikan pada `routes/owner.php` baris 1012–1015:
     ```php
     Route::get('/', [BusinessLandingPageWebController::class, 'edit'])->name('edit');
     Route::put('/', [BusinessLandingPageWebController::class, 'update'])->name('update');
     Route::post('/preset', [BusinessLandingPageWebController::class, 'applyPreset'])->name('preset');
     Route::post('/toggle-publish', [BusinessLandingPageWebController::class, 'togglePublish'])->name('toggle-publish');
     ```
   - Input `section_visibility_json` divalidasi dan disimpan ke kolom `section_visibility` (baris 156, 253–270).

2. **Kategori Tema Eksisting di Sub-Card 5**:
   - Pada `resources/views/app/landing_page/edit.blade.php` baris 380–452, terdapat array `$themeCategoryMap` yang mengelompokkan 42 tema ke dalam 6 kategori industri: `'fnb'`, `'retail'`, `'service'`, `'industry'`, `'lifestyle'`, `'corporate'`.
   - Tombol segmented tab di baris 517–553 menyaring tema berdasarkan kategori industri tersebut, bukan berdasarkan 6 arketipe desain visual.

3. **Celah Saklar Visibilitas Seksi di CMS & Model**:
   - Pada `resources/views/app/landing_page/edit.blade.php` baris 2575–2592, `$sectionVisibilityDefaults` memuat:
     `hero`, `about`, `products`, `services`, `gallery`, `testimonials`, `faq`, `contact`, `footer` (+ 4 sub-footers).
   - **Tidak ada** kunci atau saklar untuk `features_banner` dan `category_chips`.
   - Pada `app/Models/BusinessLandingPage.php`, kolom `section_visibility` di-cast ke `'array'` (baris 97), namun helper `isSectionVisible(string $section, bool $default = true): bool` **belum diimplementasikan**.

4. **Ketiadaan Guard Visibilitas di Halaman Publik**:
   - Pada `resources/views/public/storefront/home.blade.php`:
     - Baris 50–56: Seksi Hero di-include langsung tanpa cek visibilitas.
     - Baris 61–66: Seksi Features Banner di-include langsung tanpa cek visibilitas.
     - Baris 70–76: Seksi Category Chips di-include langsung tanpa cek visibilitas.
     - Baris 81, 121, 237: Seksi Products, Services, dan About hanya memeriksa ketersediaan koleksi data/status halaman, tanpa memeriksa `isSectionVisible()`.

5. **Test Runner & Eksekusi Pengujian Eksisting**:
   - Testing framework: PHPUnit 11 dengan runner `php artisan test` (`phpunit.xml` menggunakan SQLite `:memory:`, 241 file migrasi database).
   - Perintah pengujian `php artisan test tests/Feature/StorefrontThemeCustomizationTest.php` dieksekusi dengan hasil:
     ```json
     {"tool":"phpunit","result":"passed","tests":5,"passed":5,"assertions":440,"duration_ms":175760}
     ```
   - Tes ini membuktikan seluruh 42 tema dapat di-resolve dan dirender (HTTP 200), namun belum menguji *smart universal fallback*, eliminasi *hardcoded sector text*, klasifikasi 6 arketipe desain, maupun kontrol saklar visibilitas seksi.

---

## 2. Logic Chain

1. Dari **Observasi 1 & 2**, antarmuka CMS Cockpit saat ini masih mengunci pemilik bisnis dalam perspektif industri kaku melalui `$themeCategoryMap`. Jika pemilik bisnis toko butik atau alat kesehatan memilih tema `service_workshop`, antarmuka saat ini mengategorikannya sebagai tema "Jasa & Bengkel", padahal menurut cetak biru arsitektur Universal Storefront, tema tersebut adalah Arketipe Desain *Dark Carbon & High Impact* yang valid untuk bisnis apa pun.
2. Dari **Observasi 3 & 4**, pemilik bisnis tidak dapat menyembunyikan atau menampilkan seksi Features Banner (4 Pilar Keunggulan) dan Category Chips karena ketiadaan input toggle di CMS dan ketiadaan method `isSectionVisible()` di model `BusinessLandingPage`. Akibatnya, `home.blade.php` merender seksi-seksi tersebut secara tanpa syarat (*unconditional*).
3. Dari **Observasi 5**, infrastruktur pengujian otomatis telah berjalan dengan baik (440 assertions passed), tetapi memiliki *testing coverage gap* terhadap kontrak data universal:
   - Tidak ada pengujian bahwa ketika field CMS `headline`/`subheadline`/`announcement_badge`/`values` bernilai null, template mengambil nama bisnis atau translasi universal i18n (`storefront.*`).
   - Tidak ada assertion yang mencegah kemunculan teks terlarang (*zero hardcoded sector text leakage*, seperti *"Tune-up Mesin"* atau *"Biji Kopi"*).
   - Diperlukan suite pengujian baru `tests/Feature/StorefrontUniversalContractTest.php` untuk mengunci dan memverifikasi kontrak ini secara otomatis.

---

## 3. Caveats

1. **Durasi Eksekusi Test**: Karena repositori memiliki 241 file migrasi yang dieksekusi pada setiap database reset di Windows SQLite, suite pengujian penuh 42 tema memerlukan waktu ~3 menit (175 detik). Saat implementasi dan pengujian berkala, disarankan menjalankan tes secara terarah menggunakan filter flag `--filter=...`.
2. **Kamus Translasi i18n**: Translasi universal `lang/id/storefront.php` dan `lang/en/storefront.php` (Fase 1 Task 1.1) belum diperiksa mendalam apakah seluruh key (`hero.explore_catalog`, `features.official_store`, dll.) sudah lengkap tersedia di kedua bahasa atau masih memerlukan penambahan string baru.

---

## 4. Conclusion

1. **Controller & File Identifikasi**: Pengendali CMS Cockpit adalah `App\Http\Controllers\Web\BusinessLandingPageWebController` dan tampilannya adalah `resources/views/app/landing_page/edit.blade.php`.
2. **Perubahan CMS Cockpit yang Diperlukan**:
   - Ubah pengelompokan Sub-Card 5 dari 6 kategori industri lama menjadi **6 Arketipe Desain** (*Clean Bento & Modern Tech, Earthy & Warm Artisan, High-End Luxe & Editorial, Dark Carbon & High Impact, Playful Fresh & Appetite, Institutional & Trust*).
   - Tambahkan badge *"Universal & Fleksibel untuk Segala Jenis Usaha"* dan teks edukatif.
   - Tambahkan saklar visibilitas untuk `features_banner` dan `category_chips` di form CMS dan `$sectionVisibilityDefaults`.
   - Tambahkan catatan edukasi pada Tab 3 (*About / Keunggulan*) bahwa nilai 4 pilar langsung menjadi Badges di Hero dan Kartu di Features Banner.
3. **Perubahan Backend & View Publik yang Diperlukan**:
   - Tambahkan method `isSectionVisible(string $section, bool $default = true): bool` pada `App\Models\BusinessLandingPage`.
   - Tambahkan method `getThemeArchetype(string $themeKey): string` pada `App\Domain\Storefront\StorefrontThemeService`.
   - Bungkus seksi Hero, Features Banner, Category Chips, Products, Services, dan About di `resources/views/public/storefront/home.blade.php` dengan `@if ($landingPage->isSectionVisible(...))`.
4. **Test Suite yang Diperlukan**:
   - Buat file pengujian baru `tests/Feature/StorefrontUniversalContractTest.php` yang mencakup 5 skenario utama: Smart Universal Fallback, Zero Sector Text Leakage, Section Visibility Enforcement, Klasifikasi 6 Arketipe Desain, dan Presistensi CMS Cockpit.

---

## 5. Verification Method

Pengujian dan verifikasi independen dapat dilakukan dengan langkah-langkah berikut:

1. **Verifikasi Sintaks Blade**:
   ```powershell
   php -l resources/views/app/landing_page/edit.blade.php
   php -l resources/views/public/storefront/home.blade.php
   ```
2. **Eksekusi Test Suite Tema Eksisting**:
   ```powershell
   php artisan test tests/Feature/StorefrontThemeCustomizationTest.php
   ```
   *Expected result*: 5 tests passed, 440 assertions passed.
3. **Eksekusi Test Suite Universal Baru (setelah implementasi)**:
   ```powershell
   php artisan test tests/Feature/StorefrontUniversalContractTest.php
   ```
   *Expected result*: Seluruh assertion fallback universal, zero leakage, dan section visibility passed tanpa error.
4. **Verifikasi Manual Melalui Browser / Preview URL**:
   Akses `/{slug}?preview=1&preview_theme={themeKey}` untuk mengecek apakah tema merender CSS token `--theme-primary` dan konten dinamis CMS.
