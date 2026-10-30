# RENCANA IMPLEMENTASI LENGKAP: PERBAIKAN COPYWRITING FITUR & STANDARDISASI MULTI-BAHASA (i18n ID & EN) SELURUH HALAMAN PUBLIK COOCA

> **Status Dokumen:** READY FOR EXECUTION  
> **Dasar Analisis:** Hasil Deep Audit Dokumen `AUDIT_COPYWRITING_LANDING_PAGE_DAN_KONTEN_PUBLIK_COOCA.md` dan Feature-to-Content Gap Analysis terhadap SSOT Aplikasi.  
> **Pedoman Keahlian:** `landing-page-copywriting-analyst` & `multi-language-and-i18n`.  
> **Mandat Utama:**  
> 1. Sinkronisasi 100% informasi publik dengan kemampuan aktual sistem aplikasi (SSOT).  
> 2. Penegakan *Zero Hardcoded Text Mandate* dan dukungan dwibahasa penuh (**Bahasa Indonesia `id` default & English `en`**) pada seluruh 53 halaman publik dan komponen terkait.

---

## 1. Executive Summary & Sasaran Strategis

Rencana implementasi ini dirancang untuk mengubah website publik COOCA menjadi etalase produk yang **jujur, berwibawa, berorientasi nilai bisnis riil, dan berkelas internasional** melalui dua mesin transformasi:

1. **Mesin Validasi Fitur (Product Truth Remediation):**
   * Mengintegrasikan tagline resmi brand: **"Move Faster, Decide Better."**
   * Menonjolkan keunggulan pembeda utama: **Digital AI Office, Deteksi Fraud Kasir, Presensi Biometrik Wajah, Tarif Mesin & Tenaga Kerja, Kitchen Prep Sheet, dan Migrasi Cepat via Unified Data Exchange Hub**.
   * Menghapus klaim tanpa konteks (*hemat 75%* tanpa pembanding, janji *performa toko selalu sempurna*) dan frasa emosional hiperbolis (*biaya perantara yang mencekik*).
   * Membersihkan seluruh residu AI slop (*bukan sekadar X*, *tanpa ribet*) dan menstandarkan sapaan profesional **"Anda"**.

2. **Mesin Multi-Bahasa Menyeluruh (Full-Stack i18n & l10n Engine):**
   * Menyediakan arsitektur kamus terjemahan modular 12 domain (`lang/id/public_*.php` & `lang/en/public_*.php`).
   * Menambahkan komponen antarmuka **Language Switcher (ID $\leftrightarrow$ EN)** gaya Apple HIG Bento pada Desktop Header, Mobile Drawer, dan Global Footer.
   * Mendukung pergantian bahasa instan via URL query (`?lang=en`), cookie 1 tahun, dan sesi aktif.
   * Menjamin metadata SEO multilingual dinamis (Title, Description, H1, dan tag `<link rel="alternate" hreflang="..." />`).

---

## 2. Arsitektur Multi-Bahasa (i18n) & Struktur Kamus Modular

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                 ARSITEKTUR MULTI-BAHASA (i18n) HALAMAN PUBLIK COOCA                         │
├──────────────────────────┬──────────────────────────┬───────────────────────────────────────┤
│ 1. RESOLUSI LOCALE       │ 2. STRUKTUR KAMUS MODULAR│ 3. INTEGRASI VIEW & BLADE             │
│ • URL Query (?lang=en)   │ • lang/id/public_*.php   │ • {{ __('public_home.hero_title') }}  │
│ • Sesi session('locale') │ • lang/en/public_*.php   │ • Dynamic SEO Title & Meta H1         │
│ • Cookie 'cooca_locale'  │ • 12 Kluster Domain      │ • Alternatif hreflang ID & EN         │
│ • Fallback: 'id'         │   Tergorganisasi Rapi    │ • Language Switcher Apple Bento UI    │
└──────────────────────────┴──────────────────────────┴───────────────────────────────────────┘
```

### Matriks 12 File Kamus Terjemahan Modular
Seluruh teks dipisahkan ke dalam file kamus terjemahan terisolasi agar terhindar dari file kamus raksasa yang lambat dan sulit dirawat:

| No | File Kamus (`lang/id/` & `lang/en/`) | Cakupan Halaman Publik | Domain yang Dimuat |
|---|---|---|---|
| **1** | `public_marketing.php` | Layout Master, Navigasi, Footer | Menu header, dropdown vertikal, global footer, tagline, cookie consent, modal global. |
| **2** | `public_home.php` | Beranda (`landing.blade.php`) | Hero section, overline kicker, pilar produk, bento showcase, social proof, CTA penutup. |
| **3** | `public_bos.php` | Business Operating System (3 Halaman) | Overview arsitektur BOS, How It Works, Why COOCA, perbandingan software terpisah. |
| **4** | `public_erp.php` | Omnichannel ERP (8 Modul) | POS Kasir, Stok Multi-Gudang, Keuangan, Akuntansi SAK EMKM, HRM & Presensi, CRM, Analitik. |
| **5** | `public_omnichannel.php` | Omnichannel Suite (5 Halaman) | WhatsApp Bisnis, Marketplace Hub (Shopee/Tokped/TikTok), Pesanan Terpusat, Media Sosial. |
| **6** | `public_content.php` | Content Automation (4 Halaman) | Studio Kreasi Konten AI, Kalender Promosi, Jadwal Posting, Analitik Kinerja Konten. |
| **7** | `public_solutions.php` | Solusi Sektor UMKM (7 Halaman) | F&B / Resto, Toko Ritel, Bengkel Kendaraan, Laundry, Manufaktur / Konveksi, Jasa Servis. |
| **8** | `public_calculators.php`| Kalkulator Finansial (9 Halaman) | HPP Makanan, Titik Impas BEP, Harga Jual, Laba Bersih, Gaji Karyawan, PPh Final, What-If. |
| **9** | `public_commercial.php` | Landing Komersial (3 Halaman) | Aplikasi Kasir Gratis (Tier limit), Kasir Android (APK Download), Kasir Offline (Lokal). |
| **10**| `public_marketplace.php` | Marketplace Publik (6 Halaman) | Direktori Toko, Produk Lokal, Kategori Usaha, Filter Kota, Pencarian, Transaksi Langsung. |
| **11**| `public_resources.php`  | Sumber Daya & Panduan (3 Halaman) | Contoh Skenario & Studi Kasus UMKM, FAQ Keamanan & Biaya, Panduan Cetak Printer Thermal. |
| **12**| `public_contact.php`    | Halaman Bantuan & Kontak (1 Halaman) | Form Tiket Konsultasi, Chat WhatsApp Resmi, SLA Respon Dukungan Pelanggan. |

---

## 3. Spesifikasi Komponen Language Switcher (Apple HIG Style)

Komponen Language Switcher akan dipasang pada 3 titik strategis antarmuka:

### A. Desktop Navigation Bar (Header)
* **Posisi:** Berdampingan dengan *Theme Switcher Button* (Dark/Light Mode) di pojok kanan atas header.
* **Tampilan UI:** Kapsul Bento Apple HIG transparan (`bg-white/10 dark:bg-white/5 border border-white/10 hover:border-white/20`) dengan bendera/label minimalis: `ID` (Indonesia) dan `EN` (English).
* **Interaksi:** Menampilkan badge aktif pada bahasa yang sedang dipilih (`bg-[#007AFF] text-white font-bold`). Sekali klik berpindah bahasa seketika tanpa refresh berulang.

### B. Mobile Navigation Drawer & Bottom Bar
* **Posisi:** Di bagian atas drawer navigasi mobile dan baris bawah menu responsive.
* **Tampilan UI:** Tombol toggle dua segmen (*segmented control*): `[ 🇮🇩 Bahasa Indonesia ] [ 🇬🇧 English ]`.

### C. Global Footer
* **Posisi:** Di samping tautan hak cipta dan copyright brand pada baris bawah footer.
* **Tampilan UI:** Dropdown pilihan bahasa dengan ikon bola dunia (*Globe Icon* lucide) dan label bahasa aktif.

---

## 4. Rincian Rencana Kerja Bertahap (Phase-by-Phase Task List)

### FASE 1: Fondasi Arsitektur i18n & Resolusi Locale (SELESAI)
*Tujuan: Memastikan infrastruktur middleware, routes, dan kamus dwibahasa siap beroperasi 100%.*

- [x] **Task 1.1   Peningkatan `SetLocaleMiddleware`:** (SELESAI)  
  Perbarui [app/Http/Middleware/SetLocaleMiddleware.php](file:///c:/laragon/www/cooca_core/app/Http/Middleware/SetLocaleMiddleware.php) agar mampu mendeteksi parameter URL `?lang=en` atau `?lang=id` secara instan, menyimpannya ke session dan cookie, lalu mengikatnya ke `app()->setLocale($locale)`.
- [x] **Task 1.2   Inisialisasi Kamus `public_marketing.php` (ID & EN):** (SELESAI)  
  Buat [lang/id/public_marketing.php](file:///c:/laragon/www/cooca_core/lang/id/public_marketing.php) dan [lang/en/public_marketing.php](file:///c:/laragon/www/cooca_core/lang/en/public_marketing.php) berisi navigasi header, footer, modal demo, cookie banner, dan tagline resmi:
  * ID: `"Move Faster, Decide Better. Sistem Operasi Bisnis Terpadu untuk UMKM Indonesia."`
  * EN: `"Move Faster, Decide Better. Integrated Business Operating System for Indonesian MSMEs."`
- [x] **Task 1.3   Inisialisasi Kamus `public_home.php` (ID & EN):** (SELESAI)  
  Buat file kamus beranda lengkap dengan seluruh teks hero, proposisi Asisten AI, bento showcase, dan CTA.
- [x] **Task 1.4   Inisialisasi Kamus `public_bos.php` & `public_erp.php` (ID & EN):** (SELESAI)  
  Buat kamus terjemahan untuk modul BOS dan 8 modul ERP.
- [x] **Task 1.5   Inisialisasi Kamus `public_solutions.php` & `public_calculators.php` (ID & EN):** (SELESAI)  
  Buat kamus terjemahan untuk 7 vertikal industri dan 9 alat hitung kalkulator finansial.
- [x] **Task 1.6   Inisialisasi Kamus `public_omnichannel.php`, `public_content.php`, `public_commercial.php`, `public_marketplace.php`, `public_resources.php`, `public_contact.php` (ID & EN):** (SELESAI)  
  Lengkapi seluruh 12 file kamus dwibahasa tanpa melewatkan satupun string.

---

### FASE 2: Layout Master, Navigasi Global & Penegakan Tagline (P0) (SELESAI)
*Tujuan: Mengintegrasikan Language Switcher dan tagline resmi brand pada layout utama.*

- [x] **Task 2.1   Update Tagline Fallback di Layout:** (SELESAI)  
  Buka [resources/views/layouts/public_marketing.blade.php](file:///c:/laragon/www/cooca_core/resources/views/layouts/public_marketing.blade.php). Ganti teks hardcoded tagline lama di baris 44 dan 2248 menggunakan `{{ __('public_marketing.brand_tagline') }}` dan deskripsi `{{ __('public_marketing.brand_description') }}`.
- [x] **Task 2.2   Pemasangan Language Switcher Desktop:** (SELESAI)  
  Tambahkan komponen *Segmented Language Toggle* di baris 576 berdampingan dengan *Theme Switcher* yang memanggil route `route('locale.switch', 'id')` dan `route('locale.switch', 'en')`.
- [x] **Task 2.3   Pemasangan Language Switcher Mobile Drawer:** (SELESAI)  
  Pasang *Language Toggle Segmented Control* di dalam drawer mobile menu agar pengguna smartphone dapat berganti bahasa dengan nyaman.
- [x] **Task 2.4   Ekstraksi Navigasi Header & Footer ke Kamus:** (SELESAI)  
  Ganti label menu navigasi (*Platform, Solusi, Kalkulator, Harga, Panduan, Masuk, Coba Gratis*) dan kolom footer menggunakan helper `{{ __('public_marketing.nav_...') }}`.
- [x] **Task 2.5   Verifikasi Dynamic hreflang Tags:** (SELESAI)  
  Tambahkan tag SEO alternatif di dalam `<head>`:
  ```html
  <link rel="alternate" hreflang="id" href="{{ request()->fullUrlWithQuery(['lang' => 'id']) }}" />
  <link rel="alternate" hreflang="en" href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" />
  <link rel="alternate" hreflang="x-default" href="{{ request()->fullUrlWithQuery(['lang' => 'id']) }}" />
  ```

---

### FASE 3: Refactoring Beranda Utama (`landing.blade.php`) & BOS Suite (P0 & P1) (SELESAI)
*Tujuan: Menonjolkan fitur Asisten AI, mengeliminasi pola AI slop, dan menerapkan i18n dwibahasa penuh.*

- [x] **Task 3.1   Refactoring Hero Section Beranda:** (SELESAI)  
  Buka [resources/views/landing.blade.php](file:///c:/laragon/www/cooca_core/resources/views/landing.blade.php).  
  * Ganti overline hardcoded baris 185 menjadi `{{ __('public_home.hero_kicker') }}`.
  * Tambahkan Asisten AI pada subheadline baris 198-201 menggunakan `{{ __('public_home.hero_subtitle') }}`.
  * Ganti tombol aksi baris 220 menjadi `{{ __('public_home.hero_cta_primary') }}` (*"Mulai Coba Gratis"* / *"Start Free Trial"*).
- [x] **Task 3.2   Refactoring Bento Features & Social Proof Beranda:** (SELESAI)  
  Ganti seluruh string judul kartu bento, angka metrik, dan testimoni menjadi referensi kamus `{{ __('public_home....') }}`.
- [x] **Task 3.3   Pembersihan AI Slop & Refactoring `overview.blade.php`:** (SELESAI)  
  Buka [resources/views/public/business-operating-system/overview.blade.php](file:///c:/laragon/www/cooca_core/resources/views/public/business-operating-system/overview.blade.php).  
  * Hapus frasa *"Bukan sekadar kasir..."* di baris 143.
  * Ganti jargon korporat baris 495 *"Executive Control"* menjadi *"Kendali Pemilik Usaha & Wawasan AI"* (`{{ __('public_bos.overview_control_title') }}`).
  * Terapkan kamus dwibahasa pada 100% elemen teks.
- [x] **Task 3.4   Perbaikan Alur CTA `how-it-works.blade.php` & Konteks Klaim `why-cooca.blade.php`:** (SELESAI)  
  * Di `how-it-works.blade.php` baris 116, ubah CTA membingungkan *"Coba Alur Nyata"* menjadi *"Coba Akun Kasir Gratis"* (`{{ __('public_bos.how_cta_start') }}`).
  * Di `why-cooca.blade.php` baris 131, berikan konteks pembanding konkret pada klaim penghematan biaya (`{{ __('public_bos.why_savings_claim') }}`).

---

### FASE 4: Refactoring Omnichannel ERP Suite (8 Modul)
*Tujuan: Mengintegrasikan fitur Presensi Biometrik Wajah, menstandarkan istilah akuntansi, dan migrasi i18n.*

- [x] **Task 4.1   Modul HRM (`hrm.blade.php`):** (SELESAI)  
  Buka [resources/views/public/omnichannel-erp/hrm.blade.php](file:///c:/laragon/www/cooca_core/resources/views/public/omnichannel-erp/hrm.blade.php).  
  * Tambahkan fitur aktual **Presensi Biometrik Wajah (Face Verification)** dan geofencing GPS pada hero dan bento card baris 125 & 473.
  * Ekstrak seluruh teks ke kamus `public_erp.hrm_*`.
- [x] **Task 4.2   Modul POS Kasir (`pos.blade.php`):** (SELESAI)  
  Integrasikan Asisten AI Kasir dan lembar persiapan dapur (*prep sheet*) ke dalam fitur POS, lalu migrasi 100% teks ke kamus `public_erp.pos_*`.
- [x] **Task 4.3   Modul Inventori & Gudang (`inventory.blade.php`):** (SELESAI)  
  Bersihkan kata sifat absolut *"sempurna"* di baris 539 dan istilah *"End-to-End"* di baris 603, lalu migrasi ke kamus `public_erp.inv_*`.
- [x] **Task 4.4   Modul Keuangan (`finance.blade.php`):** (SELESAI)  
  Perjelas otomatisasi rekonsiliasi kas dan settlement QRIS, lalu migrasi ke kamus `public_erp.fin_*`.
- [x] **Task 4.5   Modul Akuntansi (`accounting.blade.php`):** (SELESAI)  
  Migrasi penjelasan jurnal standar SAK EMKM dan buku besar otomatis ke kamus `public_erp.acc_*`.
- [x] **Task 4.6   Modul Analitik & AI (`analytics.blade.php`):** (SELESAI)  
  Tonjolkan kapabilitas deteksi fraud kasir dan 25 MCP tools, lalu migrasi ke kamus `public_erp.analytics_*`.
- [x] **Task 4.7   Modul CRM (`crm.blade.php`):** (SELESAI)  
  Migrasi fitur loyalty points, histori transaksi, dan membership ke kamus `public_erp.crm_*`.
- [x] **Task 4.8   Modul ERP Overview (`erp.blade.php`):** (SELESAI)  
  Ganti buzzword *"Transformasi Operasional"* baris 626 menjadi *"Rapikan Operasional Bisnis Anda"*, lalu migrasi ke kamus `public_erp.erp_*`.

---

### FASE 5: Refactoring Omnichannel Suite (5 Halaman) (SELESAI)
*Tujuan: Mengoreksi klaim performa toko, melokalkan kicker asing, dan migrasi i18n.*

- [x] **Task 5.1   WhatsApp Omnichannel (`whatsapp.blade.php`):** (SELESAI)  
  Buka [resources/views/public/omnichannel/whatsapp.blade.php](file:///c:/laragon/www/cooca_core/resources/views/public/omnichannel/whatsapp.blade.php).  
  * Ganti kicker bahasa Inggris baris 100 menjadi `{{ __('public_omnichannel.wa_kicker') }}`.
  * Ganti istilah *"Enkripsi End-to-End"* baris 171 menjadi *"Protokol Resmi & Aman Meta Cloud API"*.
  * Hapus kata superlatif *"sempurna"* baris 375 dan janji *"pengalaman terbaik"* baris 671.
  * Migrasi 100% teks ke kamus `public_omnichannel.wa_*`.
- [x] **Task 5.2   Marketplace Omnichannel (`marketplace.blade.php`):** (SELESAI)  
  Buka [resources/views/public/omnichannel/marketplace.blade.php](file:///c:/laragon/www/cooca_core/resources/views/public/omnichannel/marketplace.blade.php).  
  * Ganti janji absolut *"selalu sempurna"* baris 402 menjadi perlindungan dari penalti pesanan.
  * Migrasi seluruh teks ke kamus `public_omnichannel.marketplace_*`.
- [x] **Task 5.3   Social Media Integration (`social-media.blade.php`):** (SELESAI)  
  Migrasi fitur integrasi feed, DM Instagram/TikTok, dan katalog WhatsApp ke kamus `public_omnichannel.social_*`.
- [x] **Task 5.4   Orders Management (`orders.blade.php`):** (SELESAI)  
  Migrasi fitur agregasi pesanan multi-saluran ke kamus `public_omnichannel.orders_*`.
- [x] **Task 5.5   Customer Database (`customer.blade.php`):** (SELESAI)  
  Migrasi database pelanggan terpusat ke kamus `public_omnichannel.customer_*`.

---

### FASE 6: Refactoring Solusi Sektor UMKM (7 Vertikal Sektor) (SELESAI)
*Tujuan: Menambahkan fitur aktual tersembunyi (prep sheet, jam mesin, reservasi DP) dan migrasi i18n.*

- [x] **Task 6.1 — Solusi F&B & Kafe (`fnb.blade.php`):** (SELESAI)  
  Buka [resources/views/public/solutions/fnb.blade.php](file:///c:/laragon/www/cooca_core/resources/views/public/solutions/fnb.blade.php).  
  * Tambahkan fitur aktual **Kitchen Prep Sheet** (perhitungan bahan persiapan harian).
  * Migrasi resep gram/ml, tiket KOT dapur, dan split bill ke kamus `public_solutions.fnb_*`.
- [x] **Task 6.2 — Solusi Manufaktur & Konveksi (`manufacturing.blade.php`):** (SELESAI)  
  Buka [resources/views/public/solutions/manufacturing.blade.php](file:///c:/laragon/www/cooca_core/resources/views/public/solutions/manufacturing.blade.php).  
  * Tambahkan fitur aktual **Tarif Upah Jahit/Montir & Beban Jam Mesin per Jam**.
  * Bersihkan klise *"tanpa ribet spreadsheet"* baris 611.
  * Migrasi ke kamus `public_solutions.mfg_*`.
- [x] **Task 6.3 — Solusi Laundry (`laundry.blade.php`):** (SELESAI)  
  Bersihkan klise *"tanpa ribet"* baris 619 dan migrasi pelacakan status cucian kiloan/satuan ke kamus `public_solutions.laundry_*`.
- [x] **Task 6.4 — Solusi Bengkel (`workshop.blade.php`):** (SELESAI)  
  Migrasi fitur SPK nomor polisi, komisi montir per jasa, dan stok oli/sparepart ke kamus `public_solutions.workshop_*`.
- [x] **Task 6.5 — Solusi Toko Ritel (`retail.blade.php`):** (SELESAI)  
  Migrasi fitur scan barcode kamera HP, harga bertingkat grosir/renceng/dus, dan pencatatan kasbon ke kamus `public_solutions.retail_*`.
- [x] **Task 6.6 — Solusi Bisnis Jasa (`services.blade.php`):** (SELESAI)  
  Migrasi fitur kalender janji temu, alokasi terapis, dan uang muka (DP) ke kamus `public_solutions.services_*`.
- [x] **Task 6.7 — Dynamic Solution Show (`show.blade.php`):** (SELESAI)  
  Migrasi template solusi dinamis ke kamus `public_solutions.show_*`.

---

### FASE 7: Refactoring Kalkulator Finansial (9 Halaman)
*Tujuan: Mempertahankan akurasi engine akuntansi dan migrasi dwibahasa penuh.*

- [ ] **Task 7.1   Hub Kalkulator (`index.blade.php`):**  
  Migrasi katalog 9 alat hitung finansial ke kamus `public_calculators.hub_*`.
- [ ] **Task 7.2   Kalkulator HPP (`hpp.blade.php`):**  
  Migrasi simulasi 3 pilar SAK EMKM dan microcopy konversi ke POS ke kamus `public_calculators.hpp_*`.
- [ ] **Task 7.3   Kalkulator BEP (`bep.blade.php`):**  
  Migrasi kalkulator titik impas ke kamus `public_calculators.bep_*`.
- [ ] **Task 7.4   Kalkulator Harga Jual (`harga_jual.blade.php`):**  
  Migrasi simulasi margin dan markup ke kamus `public_calculators.pricing_*`.
- [ ] **Task 7.5   Kalkulator Laba Bersih (`laba_bersih.blade.php`):**  
  Migrasi simulasi laba kotor vs laba operasional ke kamus `public_calculators.net_profit_*`.
- [ ] **Task 7.6   Kalkulator Gaji Karyawan (`gaji_karyawan.blade.php`):**  
  Migrasi pembagian gaji pokok, tunjangan, dan bonus ke kamus `public_calculators.payroll_*`.
- [ ] **Task 7.7   Kalkulator PPh Final (`pph_final.blade.php`):**  
  Migrasi simulasi tarif PPh Final UMKM 0.5% (PP 55/2022) ke kamus `public_calculators.tax_*`.
- [ ] **Task 7.8   Kalkulator Omzet Harian (`omzet_harian.blade.php`):**  
  Migrasi rekap omzet harian ke kamus `public_calculators.revenue_*`.
- [ ] **Task 7.9   Simulasi What-If (`simulasi_what_if.blade.php`):**  
  Bersihkan frasa AI *"bukan sekadar intuisi"* baris 321 dan migrasi ke kamus `public_calculators.what_if_*`.

---

### FASE 8: Refactoring Landing Komersial & Marketplace Publik
*Tujuan: Memperbaiki bahasa emosional dan migrasi dwibahasa penuh.*

- [ ] **Task 8.1   Kasir Gratis (`kasir_gratis.blade.php`):**  
  Migrasi transparansi kuota 1 toko & 10 produk gratis selamanya ke kamus `public_commercial.free_*`.
- [ ] **Task 8.2   Kasir Android (`kasir_android.blade.php`):**  
  Migrasi panduan unduh APK dan spesifikasi minimum HP ke kamus `public_commercial.android_*`.
- [ ] **Task 8.3   Kasir Offline (`kasir_offline.blade.php`):**  
  Migrasi fitur transaksi saat sinyal internet mati ke kamus `public_commercial.offline_*`.
- [ ] **Task 8.4   Koreksi Kalimat Emosional `products.blade.php`:**  
  Buka [resources/views/public/marketplace/products.blade.php](file:///c:/laragon/www/cooca_core/resources/views/public/marketplace/products.blade.php).  
  * Ganti *"Tanpa biaya perantara yang mencekik"* di baris 281 menjadi `{{ __('public_marketplace.producer_price_desc') }}` (*"Tanpa potongan komisi perantara yang tinggi..."*).
- [ ] **Task 8.5   Migrasi Seluruh Direktori Marketplace (`businesses`, `categories`, `locations`, `search`, `index`):**  
  Ekstrak seluruh teks ke kamus `public_marketplace.*`.
- [ ] **Task 8.6   Direktori Discovery (`discovery/index.blade.php`):**  
  Bersihkan duplikasi komentar blade di baris 41-45 dan migrasi teks ke kamus `public_discovery.*`.

---

### FASE 9: Refactoring Content Automation, Resources & Contact
*Tujuan: Memasarkan fitur migrasi Excel cepat, melokalkan kicker asing, dan migrasi i18n.*

- [ ] **Task 9.1   Lokalisasi Kicker Content Automation (`creation`, `calendar`, `publishing`, `analytics`):**  
  Terjemahkan overline bahasa Inggris asing (*"Product-Driven Content Creation Studio"*) menjadi istilah bahasa Indonesia yang ramah UMKM di `lang/id` dan bahasa Inggris formal di `lang/en`.
- [ ] **Task 9.2   Variasi Kosakata "Terbaik" pada FAQ Analitik:**  
  Variasikan pengulangan kata *"terbaik"* 5 kali di [content-automation/analytics.blade.php](file:///c:/laragon/www/cooca_core/resources/views/public/content-automation/analytics.blade.php).
- [ ] **Task 9.3   Tambahkan Panduan Migrasi Data Excel (`guides.blade.php`):**  
  Cantumkan panduan migrasi cepat dari software lama menggunakan **Unified Data Exchange Hub** COOCA.
- [ ] **Task 9.4   Halaman Studi Kasus & FAQ (`case-studies.blade.php`, `faq.blade.php`):**  
  Ganti pengulangan kata *"transformasi"* menjadi *"perubahan operasional"* dan migrasi teks ke kamus `public_resources.*`.
- [ ] **Task 9.5   Halaman Kontak Resmi (`contact/index.blade.php`):**  
  Migrasi SLA respon dukungan dan formulir pesan ke kamus `public_contact.*`.

---

### FASE 10: Standardisasi Sapaan Blog & Pembersihan AI Slop
*Tujuan: Menyatukan formalitas sapaan brand menjadi "Anda" dan membersihkan klise mesin.*

- [ ] **Task 10.1   Standardisasi Sapaan 13 Artikel Blog:**  
  Buka 13 file artikel markdown di [resources/views/public/blog/articles/](file:///c:/laragon/www/cooca_core/resources/views/public/blog/articles) yang menggunakan sapaan *"kamu"* (artikel nomor 004, 010, 012, 027, 030, 040, 052, 061, 073, 082, 088, 091, 095). Ubah judul dan body text secara rapi menjadi sapaan hormat **"Anda"**.
- [ ] **Task 10.2   Pembersihan Pola AI Slop "Bukan Sekadar":**  
  Bersihkan klise *"Bukan sekadar X..."* pada 21 artikel blog dan gantikan dengan kalimat pembuka bernilai langsung.
- [ ] **Task 10.3   Refactoring Indeks Blog & Show Template (`index.blade.php`, `show.blade.php`):**  
  Migrasi seluruh antarmuka pencarian blog, filter cluster, tombol pagination, dan CTA penutup artikel ke kamus dwibahasa `public_marketing.blog_*`.

---

### FASE 11: Validasi Kualitas, Uji Regresi & Pelaporan Akhir
*Tujuan: Memastikan nol teks mentah yang tertinggal dan nol regresi pada sistem aplikasi.*

- [ ] **Task 11.1   Pemindaian Mandat Zero Hardcoded Text:**  
  Jalankan skrip scanner regex untuk memastikan tidak ada lagi teks bahasa Indonesia mentah di seluruh 53 file Blade publik.
- [ ] **Task 11.2   Pengujian Fungsionalitas Switcher Bahasa:**  
  Uji perpindahan bahasa ID $\leftrightarrow$ EN di Desktop, Tablet, dan Mobile. Pastikan sesi dan cookie `cooca_locale` bertahan saat navigasi antarhalaman.
- [ ] **Task 11.3   Verifikasi Validitas Sintaks Blade & Evaluasi PHP:**  
  Jalankan `php artisan view:cache` (atau compile check) untuk memastikan seluruh directive Blade, helper `__()`, dan variable data terkompilasi 100% tanpa error sintaks.
- [ ] **Task 11.4   Verifikasi Keamanan Backend & Database:**  
  Pastikan tidak ada perubahan pada database schema, Eloquent model logic, middleware otentikasi, maupun API endpoints.
- [ ] **Task 11.5   Pembaruan Dokumentasi Implementasi:**  
  Catat seluruh berkas yang telah diperbarui dan sajikan laporan eksekusi final kepada pengguna.

---

## 5. Matriks Pemetaan Progres Eksekusi (Work Breakdown Structure)

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                    MATRIKS KONTROL STATUS IMPLEMENTASI & i18n COOCA                         │
├────────────────────┬─────────────────────┬───────────────┬─────────────────┬────────────────┤
│ Tahapan Kerja      │ Total Berkas Target │ Estimasi Task │ Status Mandat   │ Prioritas Aksi │
├────────────────────┼─────────────────────┼───────────────┼─────────────────┼────────────────┤
│ Fase 1: Kamus i18n │ 12 Kamus ID + 12 EN │ 6 Task Utama  │ SELESAI (100%)  │ FONDASI (P0)   │
│ Fase 2: Layout & SW│ 2 Layout Master     │ 5 Task Utama  │ SELESAI (100%)  │ KRITIS (P0)    │
│ Fase 3: Home & BOS │ 4 File Blade        │ 4 Task Utama  │ PENDING TRIGGER │ KRITIS (P0-P1) │
│ Fase 4: ERP Suite  │ 8 File Blade        │ 8 Task Utama  │ SELESAI (100%)  │ TINGGI (P1)    │
│ Fase 5: Omnichannel│ 5 File Blade        │ 5 Task Utama  │ PENDING TRIGGER │ TINGGI (P1-P2) │
│ Fase 6: Solusi UMKM│ 7 File Blade        │ 7 Task Utama  │ PENDING TRIGGER │ TINGGI (P1-P2) │
│ Fase 7: Kalkulator │ 9 File Blade        │ 9 Task Utama  │ PENDING TRIGGER │ SEDANG (P2)    │
│ Fase 8: Komersial  │ 9 File Blade        │ 6 Task Utama  │ PENDING TRIGGER │ TINGGI (P0-P2) │
│ Fase 9: Resources  │ 8 File Blade        │ 5 Task Utama  │ PENDING TRIGGER │ SEDANG (P2)    │
│ Fase 10: Blog Edit │ 102 File (MD+Blade) │ 3 Task Utama  │ PENDING TRIGGER │ TINGGI (P1-P2) │
│ Fase 11: QA & Uji  │ 53 File Blade       │ 5 Task Utama  │ PENDING TRIGGER │ VERIFIKASI (P0)│
├────────────────────┼─────────────────────┼───────────────┼─────────────────┼────────────────┤
│ TOTAL KESELURUHAN  │ 153 Berkas Publik   │ 63 Task Rinci │ SIAP EKSEKUSI   │ HIGH VALUE     │
└────────────────────┴─────────────────────┴───────────────┴─────────────────┴────────────────┘
```

---

## 6. Protokol Keamanan & Anti-Regresi

Selama mengeksekusi 63 task di atas, AI Agent wajib mematuhi **5 Aturan Keselamatan Kerja**:

1. **Zero Logic Mutation:** Tidak mengubah logika query database, relationship Eloquent, route name, controller parameters, maupun policy authorization.
2. **Strict Helper Translatability:** Menggunakan helper resmi Laravel `{{ __('kamus.key') }}` pada Blade dan `__('kamus.key')` pada PHP. Jika terdapat variabel dinamis, wajib dilewatkan via parameter array (misal: `{{ __('public_home.trial_days', ['days' => 14]) }}`).
3. **Preservasi Karakter & Tone Brand:** Terjemahan bahasa Inggris (`en`) tidak boleh menggunakan gaya kaku terjemahan mesin (*literal Google Translate*), melainkan menggunakan standar terminologi SaaS modern (contoh: *BOM Takaran Resep* diterjemahkan menjadi *Recipe & Bill of Materials Management*, bukan *Recipe Measure*).
4. **Validasi File per Batch:** Setiap batch modifikasi (per direktori) wajib segera diperiksa integritas sintaksnya sebelum melanjutkan ke batch berikutnya.
5. **No Data Loss Guarantee:** Seluruh link WhatsApp, URL rute tombol, nomor telepon dukungan, dan skema JSON-LD terstruktur wajib dipertahankan keutuhannya.

