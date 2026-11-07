# LAPORAN SURVEI & AUDIT PRODUKSI: MODUL COMMUNITY, CRM, DAN CUSTOMERS COOCA

**Tanggal Investigasi**: 7 November 2026  
**Auditor**: Teamwork Production Audit & Docs Survey Explorer  
**Workspace**: `c:\laragon\www\cooca_core`  
**Status**: Investigasi Read-Only Selesai  

---

## 1. RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY)

Investigasi komprehensif telah dilaksanakan terhadap 3 modul inti dalam ekosistem COOCA: **Community (Komunitas & Event Bisnis)**, **CRM (Loyalitas, Poin, Voucher & Pelunasan Piutang)**, dan **Customers (Master Data Pelanggan, B2B Terms, & Integrasi Bengkel/Jasa)**.

### Ringkasan Temuan Kunci:
1. **Automated Test Coverage**:
   - Suite pengujian untuk ketiga modul sangat ekstensif dengan total **>90 test method**.
   - Verifikasi langsung via `php artisan test`:
     - `tests/Feature/Community*.php`: **48 tests passed, 224 assertions** (100% lulus).
     - `tests/Feature/CustomerModuleAuditTest.php`: **7 tests passed, 28 assertions** (100% lulus).
     - `tests/Feature/CustomerWebFeatureTest.php`: **5 tests passed, 22 assertions** (100% lulus).
     - `tests/Feature/CrmModuleAuditTest.php`: **8 tests passed, 44 assertions** (100% lulus).
     - `tests/Feature/CrmWebFeatureTest.php`: **6 tests passed, 22 assertions** (100% lulus).
     - `tests/Feature/Crm/CustomerDebtAndRepayTest.php`: **10 tests passed, 49 assertions** (100% lulus).
   - **1 Kegagalan Assertion Terisolasi** terdeteksi pada:  
     `Tests\Feature\CrmAndCustomerComprehensiveSecurityTest::test_customer_ui_auto_hides_b2b_fields_for_fnb_or_non_b2b_business` (line 498).
     - *Akar Masalah*: HTML body antarmuka `resources/views/app/customers/index.blade.php` **telah benar** membungkus field input B2B dengan `@if ($canB2b)`. Namun, `layouts.app` menginjeksi seluruh kamus bahasa `window.COOCA_I18N = { customers: @json(__('customers')) }` ke dalam tag `<head>`, sehingga string `"NPWP Perusahaan"` tetap ada di DOM header script, memicu kegagalan `assertDontSee('NPWP Perusahaan')`.
2. **Production Readiness & RBAC Security**:
   - **CRM & Customers**: Telah menerapkan **Controller-Level RBAC** via `HasMiddleware` (`require.permission:customers.view`, `customers.create`, `customers.edit`, `customers.delete`, `customers.export`, `crm.view`, `crm.manage`) dan terdaftar di `database/seeders/RbacSeeder.php`. Query multi-tenant strictly scoped (`where('business_id', $business->id)`), IDOR protection aktif, dan deletion guard mencegah penghapusan pelanggan dengan saldo piutang aktif.
   - **Community**: `CommunityWebController::middleware()` saat ini **masih mengembalikan array kosong `return []`** dan belum ada permission `community.*` yang terdaftar di `RbacSeeder.php`. Otorisasi saat ini hanya mengandalkan gating entitlement subscription (`$isSubscribed`) dan author check (`post->owner_id === user->id`).
   - **Flash Feedback & Microcopy**: Layout utama (`layouts.app`) memiliki toast handler otomatis untuk `session('success')`, `session('error')`, `session('warning')`, dan `$errors->any()`. Namun, pada `crm/members.blade.php`, data `session('wa_receipt_url')` dari controller pelunasan piutang belum dirender sebagai tombol interaktif untuk submit non-AJAX. Di `community/index.blade.php`, masih terdapat penggunaan `alert()` JavaScript bawaan browser pada beberapa event error.
3. **Arsitektur Dokumentasi (`resources/views/app/docs/`)**:
   - Saat ini dokumentasi untuk Community (Seksi 9) dan CRM (Seksi 8) hanya berupa **kartu stub kecil inline** (panjang < 30 baris), tanpa alur kerja langkah demi langkah, tanpa rute interaktif berizin, dan tanpa matriks 20 industri.
   - Modul Customers **sama sekali belum memiliki seksi tersendiri** di dokumentasi maupun Left Sidebar TOC.
   - Dibandingkan modul acuan standar tinggi COOCA (`sec_ai.blade.php`, `sec_ai_studio.blade.php`, `sec_approvals.blade.php`, `sec_billing.blade.php`, `sec_storefront.blade.php`), modul Community, CRM, dan Customers perlu diekstrak menjadi **file partials mandiri Bento Apple HIG** lengkap dengan matriks penerapan nyata 20 industri UMKM dari sudut pandang (POV) Business Owner.

---

## 2. AUDIT AUTOMATED TEST COVERAGE

### 2.1. Inventaris Test Suite yang Ada

| Modul | File Test Path | Status Eksekusi | Tests / Assertions | Cakupan Fitur Utama |
|---|---|:---:|:---:|---|
| **Community** | `tests/Feature/CommunityFeatureTest.php` | PASS | 12 / 60 | Feed postingan teks & gambar, kuota storage, like, komentar, repost, notifikasi interaksi, tab navigasi, blokir un-subscribed. |
| **Community** | `tests/Feature/CommunityEventFeatureTest.php` | PASS | 9 / 48 | Filter event mendatang, registrasi tiket gratis/berbayar, pembatalan, check-in GPS lokasi, check-in scan barcode tiket, tiket digital SVG. |
| **Community** | `tests/Feature/CommunityEventPaymentTest.php` | PASS | 8 / 42 | Registrasi berbayar QRIS TriPay, proteksi HTTP 402 pada tiket unpaid, polling status pembayaran real-time, simulasi sandbox testing. |
| **Community** | `tests/Feature/CommunityModuleAuditTest.php` | PASS | 8 / 38 | Redirect guest ke login, pencatatan jejak AuditLog (daftar, batal, hapus post), proteksi IDOR hapus post & bayar tiket, moderasi konten toxic/judol. |
| **Community** | `tests/Feature/CommunityWordFilterTest.php` | PASS | 9 / 36 | Filter kata kasar, kata judol, penipuan, pornografi pada post dan komentar, panel admin kata terlarang. |
| **CRM** | `tests/Feature/CrmModuleAuditTest.php` | PASS | 8 / 44 | Gating izin `crm.view` dan `crm.manage`, proteksi IDOR histori poin dan bayar piutang, jejak AuditLog pelunasan dan voucher, validasi saldo piutang, isolasi kode voucher tenant. |
| **CRM** | `tests/Feature/CrmWebFeatureTest.php` | PASS | 6 / 22 | Tampilan halaman member, filter pencarian & tier loyalitas, endpoint API riwayat poin, formulir pelunasan kasbon, pembuatan & toggle aktif voucher promo. |
| **CRM** | `tests/Feature/Crm/CustomerDebtAndRepayTest.php` | PASS | 10 / 49 | Overview saldo piutang, isolasi tenant piutang bisnis lain, pembayaran parsial & pembaruan saldo, integrasi kas laci shift POS kasir, penolakan bayar lebih dari saldo. |
| **Customers** | `tests/Feature/CustomerModuleAuditTest.php` | PASS | 7 / 28 | Gating izin `customers.view/create/edit/delete`, proteksi IDOR update/destroy, deletion guard blokir hapus pelanggan bersaldo piutang aktif, keunikan kode per tenant, netralisasi formula injection Excel (`=`, `+`, `-`, `@`). |
| **Customers** | `tests/Feature/CustomerWebFeatureTest.php` | PASS | 5 / 22 | Halaman index pelanggan, pencarian real-time, simpan pelanggan baru, update pelanggan, hapus pelanggan tanpa piutang. |
| **Customers** | `tests/Feature/CustomerMultiAddressAndBiteshipGeoTest.php` | PASS | 4 / 18 | Manajemen multi-alamat pengiriman dan integrasi koordinat ekspedisi Biteship. |
| **Customers** | `tests/Feature/CustomerPaymentTermReminderTest.php` | PASS | 3 / 14 | Pengingat termin jatuh tempo TOP (*Terms of Payment*) dan integrasi notifikasi tagihan WhatsApp. |
| **Customers** | `tests/Feature/CustomerPortalFeatureTest.php` | PASS | 14 / 64 | Autentikasi customer storefront, OTP WhatsApp/Email, dashboard riwayat pesanan, upload bukti bayar, integrasi checkout. |
| **Lintas Modul** | `tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php` | 51 PASS / 1 FAIL | 52 / 304 | Uji regresi keamanan SQL model binding, audit log pelunasan piutang, kartu responsif mobile thumb-zone, penyembunyian dinamis field B2B & bengkel. |

### 2.2. Analisis Kasus Kegagalan Pengujian (Regression Bug Finding)
- **Nama Test**: `test_customer_ui_auto_hides_b2b_fields_for_fnb_or_non_b2b_business`
- **File**: `tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php:498`
- **Pesan Error**: `Failed asserting that '<!DOCTYPE html> ...' does not contain "NPWP Perusahaan".`
- **Fakta Kode**:
  1. Pada `resources/views/app/customers/index.blade.php`:
     ```blade
     @if ($canB2b)
         <div class="space-y-1">
             <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('customers.tax_id') }}</label>
             <input type="text" name="tax_identification_number" placeholder="01.234.567.8-901.000" ...>
         </div>
     @endif
     ```
     Tag form HTML di body **sudah tersembunyi dengan benar** saat `$canB2b` bernilai false.
  2. Pada `resources/views/layouts/app.blade.php`:
     ```blade
     window.COOCA_I18N = {
         ...
         customers: @json(__('customers')),
     };
     ```
     Layout global menginjeksi seluruh kamus bahasa `customers` ke dalam `<head>`, yang di dalamnya terdapat `'tax_id' => 'NPWP Perusahaan'`.
  3. Assertion `$response->assertDontSee('NPWP Perusahaan')` memeriksa keseluruhan string HTML termasuk tag script.
- **Rekomendasi Perbaikan**:
  - Pada test assertion: Ubah menjadi `$response->assertDontSee('<label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">NPWP Perusahaan</label>', false)` atau pastikan pengecekan spesifik pada elemen form body.
  - Alternatif arsitektur i18n: Filter kamus `window.COOCA_I18N` berdasarkan modul aktif bisnis jika ingin penegakan Zero Feature Leakage hingga level kamus JS.

### 2.3. Skenario Pengujian yang Belum Ada (Missing Test Scenarios)

1. **Modul Community**:
   - *Upload Berkas Ilegal*: Pengujian upload lampiran gambar dengan ekstensi palsu (misal `.php.jpg` atau berkas executable) serta berkas berukuran > 5 MB (`MAX_IMAGE_BYTES`).
   - *Kuota Event Penuh*: Pengujian registrasi event berbayar/gratis ketika kapasitas kuota peserta (`max_participants`) sudah habis.
   - *Anti-Spam Flooding Rate Limiting*: Pengujian pengiriman komentar atau postingan berulang-ulang dalam hitungan detik.
   - *Check-in GPS Boundary Threshold*: Uji kasus batas akurat radius geofence (misal radius 100m, posisi peserta tepat 99.9m vs 100.1m).
   - *Enforcement RBAC Permission*: Pengujian akses rute komunitas jika kelak diterapkan middleware izin `community.view` / `community.post`.

2. **Modul CRM**:
   - *Validasi Nilai Pembayaran Negatif / Nol*: Pengujian input nilai `amount <= 0` pada endpoint `recordCreditPayment` (controller mewajibkan `min:1`).
   - *Voucher Limit Exceeded*: Pengujian penggunaan voucher yang sudah mencapai `usage_limit` saat diterapkan pada transaksi kasir POS atau checkout online.
   - *Voucher Tanggal Kedaluwarsa*: Pengujian validasi `valid_until` yang berada di masa lampau (*past date*).
   - *Penerbitan Struk WhatsApp saat Nomor Kosong*: Uji kasus ketika pelanggan belum memiliki nomor telepon WhatsApp saat piutang dilunasi (apakah menghasilkan fallback aman tanpa crash).

3. **Modul Customers**:
   - *Import Excel Berkas Rusak / Kosong*: Pengujian endpoint `previewImport` dan `executeImport` ketika berkas Excel tidak memiliki baris data atau header kolom acak.
   - *Input Karakter Ekstrem*: Pengujian input string panjang (> 255 karakter) pada nama, email, dan nomor plat kendaraan.
   - *Deep-Link Tab Preservation*: Pengujian bahwa redirect `customers?tab=members&search=budi` meneruskan parameter pencarian ke rute `crm.members.index?search=budi`.

---

## 3. AUDIT KESIAPAN PRODUKSI (PRODUCTION READINESS)

### 3.1. Penanganan Error & Validasi Input

| Modul & Controller | Mekanisme Validasi | Aturan Kunci | Penanganan Error & Feedback |
|---|---|---|---|
| **Community**<br>`CommunityWebController` | `$request->validate()` inline & `ContentModerationService::validateOrFail()` | Post content max 5000 chars, mimes jpeg/png/webp/gif max 5MB. Geolocation latitude (-90 s/d 90), longitude (-180 s/d 180). Barcode max 128 chars. | Menangkap `ValidationException` dari moderasi konten dan mengembalikan `back()->withErrors()->with('error', $firstError)`. Menggunakan `abort_unless(..., 403)`. |
| **CRM**<br>`CrmWebController` | `$request->validate()` inline & `LoyaltyService` | Voucher: code max 50 scoped unique per tenant, discount_type in percentage/fixed, valid_until after_or_equal:valid_from. Credit payment: `min:1`, `max:{$currentBalance}`. | Jika saldo piutang nol/negatif, langsung redirect back dengan `$errors->add('amount', 'Pelanggan tidak memiliki saldo piutang aktif.')`. Response JSON mengembalikan HTTP 422. |
| **Customers**<br>`CustomerWebController` | `$request->validate()` inline & `DataExchangeService` | Customer name max 255 (required), code max 50 scoped unique per tenant dengan ignore ID saat update. Email format valid. Odometer integer min 0. Payment terms max 365 hari. | Excel import memvalidasi file max 5MB mimes xlsx/xls/csv. Deletion guard memblokir HTTP 422 dengan flash error jika saldo piutang berjalan > 0. |

### 3.2. Konsistensi Kueri Data & Isolasi Multi-Tenant

1. **Scoping Tenant (`business_id`)**:
   - Pada `CustomerWebController`: Seluruh pemanggilan model (`Customer::where('business_id', $business->id)`) dieksekusi dengan kueri berlingkup tenant. Metode `update` dan `destroy` memverifikasi kepemilikan melalui `abort_unless($customer->business_id === $business->id, 403)`.
   - Pada `CrmWebController`: Seluruh data voucher dan member difilter berdasarkan `$business->id`. Metode `pointHistories` dan `recordCreditPayment` memiliki proteksi guardrail `abort_unless($customer->business_id === $business->id, 403)`.
   - Pada `CommunityWebController`: Postingan bersifat jejaring antarpemilik usaha, tetapi `business_id` dan `owner_id` dicatat secara eksplisit pada setiap pembuatan post, komentar, repost, dan pendaftaran event. Penghapusan post dilindungi oleh validasi kepemilikan `abort_unless((string)$post->owner_id === (string)$user->id, 403)`.
2. **Paginasi & Optimasi Kueri (N+1 Prevention)**:
   - `CustomerWebController::index()` menggunakan `PaginationHelper::perPage($request, 25)` dan `withCount(['invoices', 'purchaseOrders', 'posOrders'])` untuk mencegah masalah N+1 pada metrik aktivitas transaksi pelanggan.
   - `CrmWebController::members()` menggunakan `paginate($perPage)` dan `withCount('posOrders')`.
   - `CommunityWebController::index()` memuat eager-loading relasi `with(['owner', 'business', 'comments.user', 'reposts.user'])` dan `withCount('likes', 'comments', 'reposts')`. Status interaksi `liked_by_me` dan `reposted_by_me` dibundel menggunakan `whereIn` dan `keyBy` tanpa kueri berulang per post.

### 3.3. Penegakan Otorisasi (RBAC) & Middleware

| Komponen | Status Implementasi | Catatan Temuan & Rekomendasi |
|---|:---:|---|
| **Customers Controller** | LENGKAP | Mengimplementasikan `HasMiddleware`: `customers.view`, `customers.export`, `customers.create`, `customers.edit`, `customers.delete`, dan `entitlement:customer`. |
| **Customers Views** | LENGKAP | Tombol aksi Tambah, Edit, Hapus, Ekspor, dan Impor dilindungi `Context::hasPermission(...)`. Input B2B dilindungi `$canB2b`, input bengkel dilindungi `$isWorkshop`. |
| **CRM Controller** | LENGKAP | Mengimplementasikan `HasMiddleware`: `crm.view` pada member & voucher, `crm.manage` pada pelunasan piutang & kelola voucher. |
| **CRM Views** | LENGKAP | Tombol `Catat Pelunasan Piutang`, `Buat Voucher`, dan toggle status dilindungi `Context::hasPermission('crm.manage')`. |
| **Community Controller** | BELUM ADA | `CommunityWebController::middleware()` mengembalikan array kosong `return []`. Rute di `routes/owner.php` belum memiliki middleware `require.permission`. Otorisasi saat ini murni berbasis subscription entitlement. |
| **Community Views** | BELUM ADA | Aksi posting, like, komen, dan repost tidak memiliki pembatasan role permission, hanya memblokir jika belum subscribe Cooca. |

---

## 4. AUDIT ARSITEKTUR DOKUMENTASI (`resources/views/app/docs/`)

### 4.1. Kondisi Saat Ini di `index.blade.php`

1. **Left Sidebar TOC (Navigasi Daftar Isi)**:
   - Terbagi ke dalam 5 Klaster:
     - *Klaster 1: Core Business*: Onboarding, POS, Penjualan B2B, Analitik (`sec-analytics`), Virtual Office AI (`sec-ai`), AI Studio (`sec-ai-studio`), Komunitas & Event (`sec-community`).
     - *Klaster 2: Inventory & Supply Chain*: Inventori & Resep HPP, Pengadaan (PO).
     - *Klaster 3: Finance & Accounting*: Kas/Bank & Akuntansi, Otorisasi MAR (`sec-approvals`).
     - *Klaster 4: Human Resources & CRM*: Karyawan & Penggajian, Loyalitas & WhatsApp Gateway (`sec-crm`), Toko Online & Storefront (`sec-storefront`).
     - *Klaster 5: System & Panduan*: Billing (`sec-billing`), Shortcuts, FAQ.
   - **Kesenjangan TOC**:
     - `#sec-community` ada di TOC tetapi hanya menunjuk ke seksi ringkas 3 kartu di baris 782 `index.blade.php`.
     - `#sec-crm` ada di TOC tetapi hanya menunjuk ke seksi ringkas 2 kartu di baris 742 `index.blade.php`.
     - **Modul Customers tidak terdaftar di Left Sidebar TOC** dan tidak memiliki jangkar URL tersendiri.

2. **Evaluasi Partial Eksisting (Benchmark Kualitas Tinggi)**:
   Modul-modul yang sudah diekstrak menjadi partial mandiri di `resources/views/app/docs/partials/`:
   - `sec_approvals.blade.php` (878 baris): Standar baku Bento Apple HIG, Maker-Checker anti-fraud, 4 langkah alur kerja, matriks 20 industri (6 klaster), rute interaktif berizin `@can`, dan FAQ interaktif.
   - `sec_storefront.blade.php` (850 baris): Panduan toko online, 42 tema, katalog 6 arketipe desain, matriks 20 industri.
   - `sec_billing.blade.php` (850 baris): Paket langganan, kuota, simulasi QRIS TriPay.
   - `sec_ai.blade.php` & `sec_ai_studio.blade.php` (~800 baris): Panduan asisten AI, studio inspirasi visual.

---

## 5. SPESIFIKASI BAKU DOKUMENTASI COMMUNITY, CRM, DAN CUSTOMERS (POV BUSINESS OWNER UMKM)

Sesuai arahan pada skill `business-owner-doc-generator`, seluruh buku panduan harus disusun dari **sudut pandang Pemilik Usaha (Business Owner)** dengan prinsip:
1. Menjawab masalah nyata bisnis UMKM (mencegah kasir curang, mengunci loyalitas pelanggan, mencegah piutang macet, jejaring omzet).
2. Bahasa yang bersahaja, hangat, dan bebas jargon teknologi.
3. Menyertakan **tombol tautan langsung** ke rute fitur yang valid dengan proteksi `@can` / `Context::hasPermission`.
4. Menguraikan **Matriks Nyata 20 Industri Berbeda** dalam 6 Klaster.
5. Menjelaskan aspek keamanan & otorisasi staf (RBAC) serta FAQ pemilik usaha.

### 5.1. Rencana Pemisahan / Integrasi Partials

Direkomendasikan membuat 2 file partial mandiri di `resources/views/app/docs/partials/`:
1. `sec_crm.blade.php`: Mengintegrasikan dokumentasi terpadu **Master Pelanggan & CRM Loyalitas** (Master Data, B2B Terms, Kendaraan Bengkel, Tier Member, Poin POS, Voucher Diskon, Pelunasan Piutang Anti-Lapping, dan Struk WhatsApp).
2. `sec_community.blade.php`: Mendokumentasikan **Jejaring Komunitas Bisnis & Agenda Pelatihan UMKM** (Diskusi tren bisnis, tiket QRIS seminar, check-in barcode/GPS, moderasi konten anti-fraud).

Kemudian di `resources/views/app/docs/index.blade.php`:
- Ganti baris 742-771 (`<section id="sec-crm">`) dengan `@include('app.docs.partials.sec_crm')`.
- Ganti baris 782-820 (`<section id="sec-community">`) dengan `@include('app.docs.partials.sec_community')`.
- Perbarui Left Sidebar TOC agar mencantumkan sub-fitur Master Pelanggan, Tier Member, dan Voucher Promo secara presisi.

---

### 5.2. Spesifikasi Matriks 20 Industri untuk Modul CRM & Customers

| Klaster Industri | 20 Jenis Usaha | Kasus Nyata Penerapan Fitur CRM & Pelanggan di COOCA |
|---|---|---|
| **1. Kuliner & F&B** | 1. Restoran Dine-In | Simpan preferensi meja & alergi pelanggan reguler, reward poin diskon makan keluarga, pengiriman promo diskon via WhatsApp saat hari ulang tahun. |
| | 2. Coffee Shop & Cafe | Kartu stempel digital otomatis (Beli 9 cup gratis 1 kopi), tiering Gold member untuk akses wifi cepat atau gratis upsize minuman. |
| | 3. Bakery & Toko Roti | Catatan pesanan kue ulang tahun custom, batas DP termin pesanan tart, kupon voucher diskon roti sore jelang tutup toko (*clearance*). |
| | 4. Cloud Kitchen Online | Database kontak pemesan langganan mingguan, penukaran poin cashback ke menu paket makan siang kantoran. |
| | 5. Katering Prasmanan | Jadwal penagihan termin DP katering pernikahan, batas jatuh tempo pelunasan H-3 acara, catatan alamat pengiriman gedung/rumah. |
| **2. Manufaktur & HPP** | 6. Konveksi & Garment | Master pelanggan vendor distro, termin pembayaran piutang bahan kain (TOP 30 hari), batas pagu kredit pesanan seragam B2B. |
| | 7. Percetakan & Offset | Penagihan termin tempo faktur cetak brosur/buku kantor, histori pesanan ulang (*re-order*) plat cetak, diskon fixed pesanan di atas 10 rim. |
| | 8. Mebel & Furniture Kayu | Data klien perumahan & proyek kafe, riwayat termin pelunasan pengerjaan lemari custom, bukti tanda terima pembayaran kasbon via WhatsApp. |
| | 9. Bengkel Bubut & Logam | Data pabrik mitra pemesan sparepart gear, syarat pembayaran tempo faktur B2B, nomor NPWP perusahaan untuk faktur pajak resmi. |
| | 10. Kerajinan Tangan / Craft | Pelanggan reseller suvenir pernikahan borongan, kupon kode diskon belanja grosir, pelacakan riwayat alamat pengiriman kargo luar kota. |
| **3. Ritel & Apotek** | 11. Minimarket & Kelontong | Scan cepat barcode nomor member di kasir, pemotongan langsung poin belanja sebagai pengurang total bayar belanja bulanan sembako. |
| | 12. Apotek & Toko Obat | Riwayat nomor kontak pasien untuk obat rutin bulanan, informasi alergi obat, nota digital bukti resep via WhatsApp tanpa boros kertas. |
| **4. Jasa Harian** | 13. Bengkel Mobil & Motor | **Field Kendaraan Aktif**: Nomor polisi (nopol), tipe mobil/motor, dan kilometer odometer. Notifikasi otomatis servis berkala via WhatsApp saat 5.000 KM. |
| | 14. Barbershop & Salon | Catatan gaya rambut / warna cat favorit langganan, poin gratis potong rambut ke-10, voucher diskon paket perawatan creambath. |
| | 15. Laundry Kiloan & Satuan | Riwayat nomor rak loker pakaian, saldo deposit kasbon cuci kiloan, notifikasi otomatis WhatsApp saat cucian selesai setrika dan siap ambil. |
| | 16. Cuci Mobil & Detailing | Data kendaraan nopol pelanggan, stempel poin cuci mobil ke-6 gratis, penagihan invoice paket nano ceramic coating bertahap. |
| **5. Jasa Proyek** | 17. Kontraktor & Renovasi | Data pemilik rumah/kantor, tagihan termin progres renovasi (DP 30%, Progres 50%, Pelunasan 20%), batas jatuh tempo penagihan SPK. |
| | 18. Wedding Organizer | Database kontak calon pengantin & orang tua, pengingat jadwal DP vendor dekorasi, voucher diskon paket honeymoon. |
| | 19. Agensi Digital & Kreatif | Klien korporat langganan retainer bulanan, alamat pengiriman invoice resmi, termin TOP 14 hari kerja. |
| **6. Distribusi & Agro** | 20. Distributor Grosir B2B | Limit plafon kredit toko kelontong mitra, histori pelunasan kasbon dengan Anti-Lapping Shield, ekspor-impor ratusan kontak toko via Excel. |

---

### 5.3. Spesifikasi Matriks 20 Industri untuk Modul Community

| Klaster Industri | 20 Jenis Usaha | Manfaat Jejaring & Event Komunitas Cooca |
|---|---|---|
| **1. Kuliner & F&B** | 1-5. Restoran, Cafe, Bakery, Cloud Kitchen, Katering | Diskusi tips izin BPOM/Sertifikasi Halal di forum, tiket barcode workshop food plating, kolaborasi supplier bahan segar antarsesama owner. |
| **2. Manufaktur & HPP** | 6-10. Konveksi, Percetakan, Mebel, Bengkel Bubut, Kerajinan | Berbagi kontak pabrik bahan baku kain & logam murah, seminar efisiensi mesin, promosi kapasitas mesin nganggur (*idle capacity*). |
| **3. Ritel & Apotek** | 11-12. Minimarket, Apotek | Diskusi penataan rak (*planogram*) minimarket, seminar regulasi izin edar obat, tips mencegah barang hilang (*shrinkage*). |
| **4. Jasa Harian** | 13-16. Bengkel, Barbershop, Laundry, Cuci Mobil | Pelatihan SOP servis kendaraan dan standarisasi chemical laundry ramah lingkungan, presensi GPS kehadiran pelatihan mekanik. |
| **5. Jasa Proyek** | 17-19. Kontraktor, WO, Agensi | Saling referensi proyek tender, kolaborasi vendor fotografi & dekorasi event, seminar strategi pitching klien korporat. |
| **6. Distribusi & Agro** | 20. Distributor & Agro | Temu buyer hasil panen petani antarpulau, informasi regulasi armada logistik daerah, tiket seminar rantai pasok dingin (*cold chain*). |

---

## 6. DIREKTORI PENAUTAN RUTE & OTORISASI (ACTIONABLE ROUTE LINKING)

Untuk memastikan panduan bersifat actionable dan interaktif, tombol aksi wajib disematkan dengan pemeriksaan otorisasi:

```blade
<!-- Tombol Akses Master Pelanggan -->
@if(\App\Support\Context::hasPermission('customers.view'))
    <a href="{{ route('customers.index') }}" class="h-9 px-3.5 rounded-xl bg-[#007AFF] text-white text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
        <i data-lucide="users" class="w-3.5 h-3.5"></i>
        <span>Kelola Data Pelanggan</span>
    </a>
@endif

<!-- Tombol Tambah Pelanggan Baru -->
@if(\App\Support\Context::hasPermission('customers.create'))
    <a href="{{ route('customers.index') }}" class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
        <i data-lucide="user-plus" class="w-3.5 h-3.5 text-blue-600"></i>
        <span>Tambah Pelanggan Baru</span>
    </a>
@endif

<!-- Tombol Member & Loyalitas CRM -->
@if(\App\Support\Context::hasPermission('crm.view'))
    <a href="{{ route('crm.members.index') }}" class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
        <i data-lucide="heart" class="w-3.5 h-3.5 text-pink-500"></i>
        <span>Member &amp; Loyalitas Poin</span>
    </a>
    <a href="{{ route('crm.vouchers.index') }}" class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
        <i data-lucide="ticket" class="w-3.5 h-3.5 text-purple-600"></i>
        <span>Voucher Promo Diskon</span>
    </a>
@endif

<!-- Tombol Komunitas & Event Bisnis -->
<a href="{{ route('community.index') }}" class="h-9 px-3.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
    <i data-lucide="messages-square" class="w-3.5 h-3.5"></i>
    <span>Buka Forum Komunitas</span>
</a>
<a href="{{ route('community.events.index') }}" class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
    <i data-lucide="calendar" class="w-3.5 h-3.5 text-teal-600"></i>
    <span>Jadwal Event &amp; Tiket</span>
</a>
```

---

## 7. REKOMENDASI TUGAS PERBAIKAN (TASK LIST UNTUK IMPLEMENTER)

1. **Task UI Simplification & Anti-Overlap**:
   - Ganti browser `alert()` mentah pada `community/index.blade.php` dan `community/events.blade.php` dengan komponen toast atau dialog native Apple HIG (`AppAlert.error()`).
   - Tambahkan banner tombol kirim WhatsApp untuk data `session('wa_receipt_url')` pada tampilan `crm/members.blade.php` saat pelunasan piutang dicatat melalui form non-AJAX.
2. **Task RBAC & Security Enforcement**:
   - Daftarkan izin `community.view` dan `community.participate` di `database/seeders/RbacSeeder.php` jika kebijakan bisnis menginginkan pembatasan staf pada fitur forum/event.
   - Perbarui `CommunityWebController::middleware()` untuk mendeklarasikan izin pengawasan jika diperlukan, atau dokumentasikan bahwa fitur Komunitas memang terbuka untuk semua tier subscription owner.
   - Perbaiki assertion uji regresi `test_customer_ui_auto_hides_b2b_fields_for_fnb_or_non_b2b_business` agar memeriksa ketiadaan elemen form spesifik daripada mencocokkan string mentah yang ada di dalam `<script>` kamus global.
3. **Task Dokumentasi Bisnis Owner (UMKM POV)**:
   - Buat file `resources/views/app/docs/partials/sec_crm.blade.php` (~700-800 baris) memuat panduan komprehensif Pelanggan & CRM Loyalitas dengan matriks 20 industri.
   - Buat file `resources/views/app/docs/partials/sec_community.blade.php` (~600-700 baris) memuat panduan Jejaring Komunitas, Tiket QRIS, dan Check-in Lokasi/Barcode dengan matriks 20 industri.
   - Integrasikan kedua partials ke dalam `resources/views/app/docs/index.blade.php` dan daftarkan tautan navigasi Left Sidebar TOC lengkap dengan badge semantik.
