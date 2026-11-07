# Laporan Hasil Audit Kesiapan Produksi & Arsitektur Dokumentasi Pemilik Usaha
**Modul Target**: `analytics`, `approvals`, `billing`  
**Explorer**: Explorer 3 (Production Readiness & Docs Explorer)  
**Tanggal Audit**: 2026-11-07  
**Lokasi Laporan**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_audit_docs_survey\handoff.md`

---

## 1. OBSERVATION

Berdasarkan investigasi langsung (code-first inspection) terhadap seluruh source code controller, Blade views, translation keys, routing, dan automated test suites di workspace `c:\laragon\www\cooca_core`:

### A. Inventaris Berkas Kode & Rute Modul
1. **Modul Analitik (`analytics`)**:
   - Controller: `app/Http/Controllers/Web/AnalyticsWebController.php` (533 baris)
   - View: `resources/views/app/analytics/index.blade.php` (573 baris)
   - Route: `Route::get('/analytics', [AnalyticsWebController::class, 'index'])->name('analytics.index');` di `routes/owner.php:398`
   - Test File: `tests/Feature/AnalyticsWebTest.php` (338 baris, 9 test methods)
   - Translation: `lang/id/analytics.php` & `lang/en/analytics.php` (dwibahasa lengkap)

2. **Modul Otorisasi Dokumen (`approvals`)**:
   - Controller: `app/Http/Controllers/Web/Approval/ApprovalWebController.php` (312 baris)
   - Service: `app/Domain/Approval/ApprovalWorkflowService.php`
   - Views:
     - `resources/views/app/approvals/inbox.blade.php` (381 baris)
     - `resources/views/app/approvals/history.blade.php` (192 baris)
     - `resources/views/app/approvals/rules.blade.php` (522 baris)
   - Routes di `routes/owner.php:273-290`:
     - `GET /approvals` (`approvals.inbox`)
     - `GET /approvals/history` (`approvals.history`)
     - `POST /approvals/{approvalRequest}/approve` (`approvals.approve`)
     - `POST /approvals/{approvalRequest}/reject` (`approvals.reject`)
     - `GET /settings/approval-rules` (`approval-rules.index`)
     - `POST /settings/approval-rules` (`approval-rules.store`)
     - `PUT /settings/approval-rules/{approvalRule}` (`approval-rules.update`)
     - `DELETE /settings/approval-rules/{approvalRule}` (`approval-rules.destroy`)
   - Test Files:
     - `tests/Feature/ApprovalModuleAuditTest.php` (385 baris, 7 test methods)
     - `tests/Feature/DocumentApprovalWorkflowTest.php` (554 baris, 10 test methods)
   - Translation: `lang/id/approvals.php` & `lang/en/approvals.php`

3. **Modul Langganan, Kuota & Billing (`billing`)**:
   - Controllers:
     - `app/Http/Controllers/Web/Billing/BillingAndLimitWebController.php` (278 baris)
     - `app/Http/Controllers/Web/Billing/SubscriptionCheckoutWebController.php` (624 baris)
   - Views:
     - `resources/views/app/billing/limits.blade.php` (2694 baris)
     - `resources/views/app/billing/checkout.blade.php` (1090 baris)
     - `resources/views/app/billing/payment.blade.php` (905 baris)
     - `resources/views/app/billing/history.blade.php` (443 baris)
     - `resources/views/app/billing/invoice.blade.php` (1014 baris)
   - Routes di `routes/owner.php:567-583`:
     - `GET /billing` & `GET /billing/limits` (`billing.limits`)
     - `GET /billing/checkout` (`billing.checkout`)
     - `POST /billing/order` (`billing.order`)
     - `GET /billing/payments/{payment}` (`billing.payment.show`)
     - `GET /billing/payments/{payment}/status` (`billing.payment.status`)
     - `GET /billing/payments/{payment}/invoice` (`billing.payment.invoice`)
     - `GET /billing/history` (`billing.history`)
     - `POST /billing/storage/recalculate` (`billing.storage.recalculate`)
     - `GET /billing/storage/files` (`billing.storage.files`)
     - `DELETE /billing/storage/files/{storageFile}` (`billing.storage.files.destroy`)
   - Test Files:
     - `tests/Feature/BillingModuleAuditTest.php` (230 baris, 8 test methods)
     - `tests/Feature/BillingSubmoduleSecurityAndErgonomicsTest.php` (285 baris, 8 test methods)
     - `tests/Feature/BillingLimitsAndAdminFeatureGatesTest.php` (3 test methods)
     - `tests/Feature/BillingPackageCatalogTest.php` (4 test methods)
     - `tests/Feature/BillingSubscriptionCheckoutActiveChannelsTest.php` (3 test methods)
     - `tests/Feature/Billing/PaymentApprovedInvoicePdfMailTest.php` (3 test methods)
     - `tests/Feature/Billing/SubscriptionPaymentExpirationTimezoneTest.php` (3 test methods)
   - Translation: `lang/id/billing.php` & `lang/en/billing.php`

4. **Arsitektur Pusat Dokumentasi Sistem (`resources/views/app/docs/`)**:
   - Controller: `app/Http/Controllers/Web/DocumentationWebController.php` (23 baris)
   - View Utama: `resources/views/app/docs/index.blade.php` (1048 baris)
   - Partials Tersedia:
     - `resources/views/app/docs/partials/sec_ai.blade.php` (1569 baris) — Golden standard implementasi 20 sektor industri & route terverifikasi.
     - `resources/views/app/docs/partials/sec_ai_studio.blade.php` (582 baris)
     - `resources/views/app/docs/partials/sec_storefront.blade.php` (419 baris)
   - Route: `Route::get('/docs', [DocumentationWebController::class, 'index'])->name('docs.index');` di `routes/owner.php:544`

---

### B. Hasil Pengujian Automated Test Suites (Direct Execution)
Perintah uji dijalankan di lingkungan workspace:
1. `php artisan test tests/Feature/AnalyticsWebTest.php tests/Feature/ApprovalModuleAuditTest.php tests/Feature/BillingModuleAuditTest.php`
   - **Hasil**: `{"tool":"phpunit","result":"passed","tests":24,"passed":24,"assertions":85,"duration_ms":56623}` [VERIFIED: PASS 100%]
2. `php artisan test tests/Feature/BillingSubmoduleSecurityAndErgonomicsTest.php tests/Feature/DocumentApprovalWorkflowTest.php`
   - **Hasil**: `{"tool":"phpunit","result":"passed","tests":18,"passed":18,"assertions":99,"duration_ms":44220}` [VERIFIED: PASS 100%]
3. `php artisan test tests/Feature/Billing`
   - **Hasil**: `{"tool":"phpunit","result":"passed","tests":6,"passed":6,"assertions":36,"duration_ms":25879}` [VERIFIED: PASS 100%]
4. `php artisan test tests/Feature/BillingLimitsAndAdminFeatureGatesTest.php tests/Feature/BillingPackageCatalogTest.php tests/Feature/BillingSubscriptionCheckoutActiveChannelsTest.php`
   - **Hasil**: `{"tool":"phpunit","result":"passed","tests":10,"passed":10,"assertions":61,"duration_ms":25332}` [VERIFIED: PASS 100%]
- **Akumulasi**: **58 tests dijalankan, 58 lulus (100%), 281 assertions valid, 0 gagal.**

---

### C. Temuan Spesifik Audit Kesiapan Produksi (Production Readiness)

#### 1. Penanganan Error & Validasi Input
- **Analytics (`AnalyticsWebController.php`)**:
  - Validasi parameter `period` ketat dengan whitelist: `['today', 'week', 'month', 'year', 'custom']` (baris 53-56).
  - Validasi `location_id` mencocokkan koleksi cabang aktif tenant: `if ($selectedLocationId && ! $locations->contains('id', $selectedLocationId)) $selectedLocationId = null;` (baris 48-50).
  - Custom date range input dibungkus blok `try { Carbon::parse(...) } catch (Throwable)` dengan fallback otomatis ke bulan berjalan (baris 283-297) serta auto-swap bila `$from > $to`.
  - Agregasi jam per jam (`buildHourlyHeatmap` dan `buildTrendSeries`) adaptif terhadap 3 dialek database: SQLite (`strftime`), PostgreSQL (`to_char`), dan MySQL (`DATE_FORMAT`/`HOUR`) (baris 330-334, baris 390-394).
- **Approvals (`ApprovalWebController.php`)**:
  - Penolakan dokumen (`reject`) mewajibkan alasan minimal 3 karakter: `if (empty($reason) || strlen(trim((string) $reason)) < 3)` (baris 139-141).
  - Validasi penambahan aturan (`rulesStore`) dan pembaruan aturan (`rulesUpdate`) menggunakan `request->validate()` dengan aturan ketat: `min_amount` (`gte:0`), `max_amount` (`gte:min_amount`), `required_levels` (`between:1,3`), dan dokumen jenis yang valid (baris 192-205, baris 242-256).
  - Eksekusi persetujuan dan penolakan dibungkus blok `try/catch (\Throwable $e)` yang mengembalikan `back()->with('error', ...)` (baris 120-122, baris 162-164).
- **Billing (`BillingAndLimitWebController.php` & `SubscriptionCheckoutWebController.php`)**:
  - Validasi formulir pesanan langganan (`store`) memverifikasi saluran pembayaran terhadap gabungan saluran aktif TriPay (`tripayService->getActiveChannels()`) dan rekening bank manual (baris 248-266).
  - Validasi kode promo AJAX (`validatePromo`) memeriksa status aktif, kuota pemakaian, dan minimal transaksi (baris 165-229).
  - Proteksi double-submit di frontend: tombol pesanan dinonaktifkan (`:disabled="isSubmitting"`) (baris 1044 `checkout.blade.php`).

#### 2. Flash Alerts & Feedback Pengguna
- **Mekanisme Global**:
  - `resources/views/layouts/app.blade.php:2753-2776` memiliki listener JavaScript `AppAlert` terpadu yang otomatis menangkap session flash: `session('success')`, `session('error')`, `session('warning')`, dan `session('info')` serta menampilkannya sebagai floating toast Apple HIG yang elegan.
- **Implementasi Controller**:
  - `ApprovalWebController`: `back()->with('success', ...)` dan `back()->with('error', ...)` pada seluruh mutasi data.
  - `BillingAndLimitWebController`: `redirect()->route('billing.limits')->with('success', ...)` pada hitung ulang penyimpanan dan upgrade, serta `with('error', ...)` jika owner tidak ditemukan.
  - `SubscriptionCheckoutWebController`: `redirect()->with('success', ...)` saat pesanan terbit, paket gratis aktif, atau bukti transfer diverifikasi.

#### 3. Konsistensi Query Multi-Tenant & Pencegahan IDOR
- **Analytics**:
  - Seluruh query (`PosOrder`, `Invoice`, `CommerceOrder`, `Expense`) memuat klausa eksplisit `where('business_id', $business->id)`.
  - Tabel anak (`pos_order_items`, `pos_order_payments`) di-JOIN langsung ke `pos_orders` dengan filter `pos_orders.business_id = $business->id`.
  - Zero cross-tenant leakage [VERIFIED].
- **Approvals**:
  - `approve()`: `abort_unless($approvalRequest->business_id === $business->id, 404);` (baris 90).
  - `reject()`: `abort_unless($approvalRequest->business_id === $business->id, 404);` (baris 131).
  - `rulesUpdate()`: `abort_unless($approvalRule->business_id === $business->id, 404);` (baris 240).
  - `rulesDestroy()`: `abort_unless($approvalRule->business_id === $business->id, 404);` (baris 291).
- **Billing**:
  - `destroyStorageFile`: `abort_unless($storageFile->business_id === $business->id, 403, ...);` (baris 173).
  - `payment`: `abort_unless($payment->business_id === $business->id, 403);` (baris 408).
  - `checkStatus`: `abort_unless($payment->business_id === $business->id, 403);` (baris 464).
  - `invoice`: `abort_unless($payment->business_id === $business->id, 403);` (baris 532).
  - `viewProof`: Verifikasi hak akses admin platform atau kepemilikan tenant aktif (`$payment->business_id === $business->id`) (baris 601-605).

#### 4. Kesenjangan Otorisasi Tampilan (View-Level RBAC Gaps)
Meskipun Controller-Level RBAC telah diterapkan 100% dengan `HasMiddleware` dan `Middleware('require.permission:...')`, ditemukan kesenjangan pada **View-Level Guards**:
- **Temuan V-APP-001 (Approvals Inbox)**:
  - Pada `resources/views/app/approvals/inbox.blade.php:131-146`, tombol aksi `Tolak` (`openRejectModal`) dan `Setujui Level` (`openApproveModal`) **belum dibungkus** dengan pengaman izin `@if(\App\Support\Context::hasPermission('approvals.manage'))`.
  - Pengguna dengan izin `approvals.view` (staf pemeriksa/auditor) dapat melihat tombol ini aktif. Ketika diklik, modal konfirmasi tetap terbuka, namun ketika formulir dikirim, sistem merespons dengan HTTP 403. Sesuai kriteria penerimaan R2, tombol aksi harus disembunyikan bagi pengguna tanpa wewenang.
- **Temuan V-APP-002 (Approvals Rules)**:
  - Pada `resources/views/app/approvals/rules.blade.php:31-37` (tombol `Tambah Aturan` di header) dan baris 134-138 (tombol `Buat Aturan Pertama` pada empty state) **belum dibungkus** pengecekan `@if(\App\Support\Context::hasPermission('approvals.manage'))`. (Hanya tombol Edit & Hapus di baris 101 yang dibungkus).

---

### D. Temuan Audit Arsitektur Dokumentasi Pemilik Usaha (`resources/views/app/docs/`)

1. **Modul Analitik (`analytics`) — STATUS: TIDAK ADA (MISSING)**:
   - Pada `resources/views/app/docs/index.blade.php`, sama sekali tidak ditemukan seksi `id="sec-analytics"`, tidak ada item pada Left Sidebar TOC, dan tidak ada file partial `resources/views/app/docs/partials/sec_analytics.blade.php`.
   - Pemilik bisnis tidak memiliki panduan untuk membaca KPI omzet vs laba bersih, analisis jam sibuk kasir (peak hours), evaluasi metode bayar, maupun perbandingan margin produk.

2. **Modul Otorisasi MAR (`approvals`) — STATUS: SKELETAL / MINIMALIS**:
   - Di `resources/views/app/docs/index.blade.php:801-832`, topik otorisasi hanya menempati 2 paragraf singkat di bawah `id="sec-security"`.
   - Tidak ada tautan interaktif ke `route('approvals.inbox')`, `route('approvals.history')`, atau `route('approval-rules.index')`.
   - Tidak ada alur kerja langkah demi langkah (Maker-Checker workflow).
   - Tidak ada matriks implementasi untuk 20 sektor usaha.

3. **Modul Billing & Kuota (`billing`) — STATUS: SKELETAL / MINIMALIS**:
   - Di `resources/views/app/docs/index.blade.php:837-861`, topik billing hanya menempati 1 paragraf singkat.
   - Tidak mencakup penjelasan 4 tingkat langganan (Free Solo, Standard, Premium, Prestige), opsi saluran pembayaran TriPay (QRIS Dinamis, VA Bank), manajemen kuota penyimpanan awan, serta penjaminan Emergency Buffer saat transaksi ramai.
   - Tidak ada matriks kebutuhan langganan lintas 20 sektor industri.

---

## 2. LOGIC CHAIN

```
[Observasi 1: Controller & Service]
- AnalyticsWebController, ApprovalWebController, BillingAndLimitWebController, SubscriptionCheckoutWebController
- Semua menerapkan HasMiddleware dengan require.permission
- Semua query data di-filter strictly dengan where('business_id', $business->id) dan abort_unless 404/403
        ↓ (Mengimplikasikan)
[Evaluasi Keamanan Backend & Multi-Tenant]:
Backend aman 100%, bebas kebocoran IDOR, dan konsisten secara multi-tenant.

[Observasi 2: View Blade Modul]
- approvals/inbox.blade.php (baris 131-146): Tombol Tolak & Setujui tanpa @if(\App\Support\Context::hasPermission('approvals.manage'))
- approvals/rules.blade.php (baris 31-37 & 134-138): Tombol Tambah Aturan tanpa guard izin
        ↓ (Mengimplikasikan)
[Evaluasi View Guarding]:
Terjadi inkonsistensi antara Controller Guard (yang memblokir 403) dengan View Layer (yang masih menampilkan tombol aksi). Ini melanggar kriteria penerimaan R2.

[Observasi 3: Pusat Dokumentasi (resources/views/app/docs/)]
- Format baku di index.blade.php & partials/sec_ai.blade.php: Header Bento dengan route() interaktif, Executive Summary POV Bisnis Owner, 4 Langkah Praktis, Matriks 20 Industri (6 Klaster), Keamanan & Anti-Fraud, FAQ Bisnis.
- Dokumentasi modul:
  * analytics: 0% (tidak ada file atau seksi)
  * approvals: 15% (hanya 2 paragraf pengantar tanpa tautan/matriks)
  * billing: 15% (hanya 1 paragraf pengantar tanpa rincian TriPay/kuota)
        ↓ (Mengimplikasikan)
[Evaluasi Dokumentasi Pemilik Usaha]:
Dibutuhkan pembuatan berkas dokumentasi partial terstandarisasi untuk analytics, approvals, dan billing sesuai standar /business-owner-doc-generator dan integrasinya ke index.blade.php.
```

---

## 3. CAVEATS

1. **Lingkungan Server Produksi Eksternal**:
   - Audit dilakukan pada codebase lokal (`c:\laragon\www\cooca_core`). Callback webhook gateway pembayaran pihak ketiga (TriPay production endpoint) disimulasikan menggunakan mock dan callback controller internal (`TripayCallbackController`).
2. **Ketersediaan Model Transaksi Aktual**:
   - Perhitungan metrik analitik mengandalkan transaksi tabel `pos_orders`, `invoices`, `commerce_orders`, dan `expenses`. Jika suatu tenant baru berdiri dan belum pernah melakukan input data transaksi atau master produk, modul menampilkan empty state yang bersih tanpa error.
3. **Pemberian Hak Akses RBAC**:
   - Pengecekan view guard `@if(\App\Support\Context::hasPermission(...))` mengandalkan sistem permission Spatie/Context COOCA. Seeder database telah menyediakan permission `reports.view`, `approvals.view`, `approvals.manage`, `billing.view`, dan `billing.manage`.

---

## 4. CONCLUSION

1. **Status Kesiapan Produksi (Production Readiness)**:
   - **Backend Controller & Multi-Tenant Scoping**: **READY (100% Lulus)**. Seluruh query telah terisolasi aman per tenant `business_id`, penanganan exception rapi dengan feedback pengguna (`session('success')`, `session('error')`), dan validasi request form diterapkan secara komprehensif.
   - **Automated Test Coverage**: **READY (100% Lulus)**. Seluruh 58 unit/feature tests pada ketiga modul lulus tanpa kegagalan (281 assertions).
   - **View-Level RBAC**: **NEEDS SURGICAL FIX (Minor)**. Diperlukan penambahan wrapper `@if(\App\Support\Context::hasPermission('approvals.manage'))` pada tombol aksi setujui/tolak di `inbox.blade.php` serta tombol tambah aturan di `rules.blade.php`.
2. **Status Dokumentasi Pemilik Usaha (Business Owner User Documentation)**:
   - **Dokumentasi Analitik (`analytics`)**: **BELUM TERSEDIA**. Wajib dibuat berkas partial baru `resources/views/app/docs/partials/sec_analytics.blade.php` dan didaftarkan pada `index.blade.php`.
   - **Dokumentasi Otorisasi MAR (`approvals`)**: **PERLU PEMBARUAN PENUH**. Wajib dibuat berkas partial baru `resources/views/app/docs/partials/sec_approvals.blade.php` menggantikan seksi statis di `sec-security`.
   - **Dokumentasi Billing & Kuota (`billing`)**: **PERLU PEMBARUAN PENUH**. Wajib dibuat berkas partial baru `resources/views/app/docs/partials/sec_billing.blade.php` menggantikan seksi minimalis di `sec-billing`.
   - Seluruh dokumentasi wajib menyertakan **Matriks 20 Sektor Industri (6 Klaster)**, **3-5 Langkah Praktis**, dan **Tautan Route Interaktif yang Terverifikasi**.

---

## 5. VERIFICATION METHOD & INDEPENDENT REPRODUCTION

Perintah pengujian independen untuk memvalidasi kesiapan produksi dan integritas kode:

```powershell
# 1. Jalankan seluruh test suite terkait modul analitik, approval, dan billing
php artisan test tests/Feature/AnalyticsWebTest.php
php artisan test tests/Feature/ApprovalModuleAuditTest.php
php artisan test tests/Feature/DocumentApprovalWorkflowTest.php
php artisan test tests/Feature/BillingModuleAuditTest.php
php artisan test tests/Feature/BillingSubmoduleSecurityAndErgonomicsTest.php
php artisan test tests/Feature/Billing

# 2. Verifikasi sintaks PHP seluruh controller dan view blade yang terdampak
php -l app/Http/Controllers/Web/AnalyticsWebController.php
php -l app/Http/Controllers/Web/Approval/ApprovalWebController.php
php -l app/Http/Controllers/Web/Billing/BillingAndLimitWebController.php
php -l app/Http/Controllers/Web/Billing/SubscriptionCheckoutWebController.php
php -l app/Http/Controllers/Web/DocumentationWebController.php

# 3. Kompilasi dan validasi seluruh view Blade (memastikan tidak ada broken syntax)
php artisan view:cache
php artisan view:clear

# 4. Validasi daftar route modul target
php artisan route:list --name=analytics
php artisan route:list --name=approvals
php artisan route:list --name=approval-rules
php artisan route:list --name=billing
php artisan route:list --name=docs
```

---

## 6. RENCANA REMEDIASI & REKOMENDASI IMPLEMENTASI DETAIL

Untuk implementor selanjutnya, berikut adalah panduan tindakan presisi (actionable remediation blueprint):

### A. Perbaikan View Guard RBAC (Surgical Fix)
1. **Berkas**: `resources/views/app/approvals/inbox.blade.php`
   - **Lokasi**: Baris 131–146
   - **Tindakan**: Bungkus blok kontainer tombol aksi otorisasi (`openRejectModal` & `openApproveModal`) dengan:
     ```blade
     @if(\App\Support\Context::hasPermission('approvals.manage'))
         <!-- Tombol Tolak & Setujui -->
     @else
         <span class="text-xs text-black/40 dark:text-white/40 italic font-medium">Hanya dapat melihat (Read-only)</span>
     @endif
     ```
2. **Berkas**: `resources/views/app/approvals/rules.blade.php`
   - **Lokasi**: Baris 31–37 (Header button) & Baris 134–138 (Empty state button)
   - **Tindakan**: Bungkus tombol `openCreateModal()` dengan `@if(\App\Support\Context::hasPermission('approvals.manage')) ... @endif`.

### B. Spesifikasi Pembuatan Dokumentasi Pemilik Usaha (20 Industri)

#### 1. Modul Analitik (`resources/views/app/docs/partials/sec_analytics.blade.php`)
- **ID Seksi**: `id="sec-analytics"`
- **Tautan Cepat Header**:
  - `route('analytics.index')` (Buka Dashboard Analitik)
  - `route('analytics.index', ['period' => 'today'])` (Pantau Jam Sibuk Hari Ini)
  - `route('reports.index')` (Laporan Keuangan Formal)
- **Ringkasan Eksekutif POV Owner**:
  - Masalah: Menghilangkan kebingungan owner yang merasa tokonya ramai tapi saat tutup buku akhir bulan uangnya tidak ada (HPP bocor / salah hitung margin).
  - 4 Pilar Manfaat:
    1. Margin Laba Bersih Transparan (memisahkan omzet kotor, beban operasional, dan laba riil).
    2. Heatmap Jam Sibuk Kasir (mengatur shift staf secara efisien agar kasir tidak kewalahan).
    3. Distribusi Metode Bayar (evaluasi penerimaan QRIS vs Tunai vs Kasbon piutang).
    4. Top 10 Produk Terlaris & Margin (mengetahui produk unggulan yang paling untung).
- **Alur Kerja Praktis 4 Langkah**:
  - Langkah 1: Memilih Periode Pantauan (Hari Ini, Pekan Ini, Bulan Ini, Custom).
  - Langkah 2: Evaluasi 4 Kartu Bento KPI Utama.
  - Langkah 3: Menganalisis Grafik Waktu & Jam Sibuk Kasir.
  - Langkah 4: Tinjau Produk Terlaris & Performa Antar-Cabang.
- **Matriks Implementasi 20 Sektor Industri (6 Klaster)**:
  - *1. F&B (Resto, Cafe, Bakery, Cloud Kitchen, Katering)*: Pantau jam makan siang (12:00-13:30) vs makan malam (19:00-21:00); evaluasi rasio food cost HPP bahan basah.
  - *2. Manufaktur (Konveksi, Percetakan, Mebel, Bubut, Craft)*: Bandingkan laba pesanan borongan B2B vs pesanan eceran; lacak margin bersih setelah dipotong ongkos tukang.
  - *3. Ritel & Apotek (Minimarket, Apotek)*: Identifikasi obat/sembako fast-moving; amati jam antrean kasir sepulang kerja.
  - *4. Jasa Harian (Bengkel, Barbershop, Laundry, Cuci Mobil)*: Evaluasi hari teramai (Sabtu-Minggu); bandingkan omzet jasa servis mekanik vs penjualan sparepart/oli.
  - *5. Jasa Proyek (Kontraktor, WO, Digital Agency)*: Pantau pembayaran termin invoice; lacak persentase laba kotor per proyek selesai.
  - *6. Distribusi & Agro (Distributor B2B, Agrobisnis)*: Pantau rasio piutang toko mitra vs penerimaan tunai; evaluasi cabang distributor dengan volume tertinggi.

#### 2. Modul Otorisasi Dokumen MAR (`resources/views/app/docs/partials/sec_approvals.blade.php`)
- **ID Seksi**: `id="sec-approvals"`
- **Tautan Cepat Header**:
  - `route('approvals.inbox')` (Kotak Masuk Otorisasi)
  - `route('approvals.history')` (Riwayat Dokumen Selesai)
  - `route('approval-rules.index')` (Aturan Plafon Otorisasi)
- **Ringkasan Eksekutif POV Owner**:
  - Masalah: Mencegah kebocoran dana dan pembelian fiktif oleh staf kasir atau admin gudang tanpa sepengetahuan pemilik bisnis.
  - 4 Pilar Manfaat:
    1. Prinsip Maker-Checker (pembuat draf tidak bisa mencairkan uang sendiri).
    2. Aturan Plafon Otomatis (pembelian kecil langsung lolos, pembelian besar wajib persetujuan bertahap).
    3. Rekam Jejak Audit Log Digital (mencatat siapa penyetuju, jam eksekusi, dan IP address).
    4. Penolakan Bertanggung Jawab (alasan wajib minimal 3 karakter untuk edukasi staf).
- **Alur Kerja Praktis 4 Langkah**:
  - Langkah 1: Pengaturan Plafon Nominal & Peran Penyetuju (Supervisor/Manager/Owner).
  - Langkah 2: Staf Mengajukan Draf Dokumen (PO, Biaya Kas, Faktur Supplier, Koreksi Stok).
  - Langkah 3: Pimpinan Memeriksa Tiket Masuk di Menu Approvals.
  - Langkah 4: Setujui (Approve) Dokumen atau Tolak (Reject) dengan Catatan Arahan.
- **Matriks Implementasi 20 Sektor Industri (6 Klaster)**:
  - *1. F&B*: Pembelian daging/sayur segar > Rp 2.000.000 oleh Chef wajib disetujui Manajer Resto sebelum kasir mengeluarkan kas kecil.
  - *2. Manufaktur*: Pengadaan kain rol konveksi atau kayu glondongan mebel > Rp 10.000.000 wajib persetujuan bertahap Kepala Gudang -> Direktur.
  - *3. Ritel & Apotek*: Penyesuaian stok opname rusak/kadaluarsa (Stock Adjustment) wajib verifikasi Apoteker Penanggung Jawab / Owner.
  - *4. Jasa Harian*: Belanja drum oli mesin bengkel atau pengadaan mesin cuci baru laundry butuh otorisasi Owner.
  - *5. Jasa Proyek*: Pembelian material semen/besi proyek renovasi wajib dicocokkan dengan RAB sebelum disetujui Manajer Proyek.
  - *6. Distribusi & Agro*: Retur muatan pupuk/panen rusak dari toko mitra wajib otorisasi Manajer Distribusi.

#### 3. Modul Langganan, Kuota & Billing (`resources/views/app/docs/partials/sec_billing.blade.php`)
- **ID Seksi**: `id="sec-billing"`
- **Tautan Cepat Header**:
  - `route('billing.limits')` (Status Kuota & Kapasitas Usaha)
  - `route('billing.checkout')` (Pilih Paket & Perpanjang)
  - `route('billing.history')` (Riwayat Transaksi Langganan)
  - `route('billing.storage.files')` (Manajer Berkas Penyimpanan)
- **Ringkasan Eksekutif POV Owner**:
  - Masalah: Memberikan fleksibilitas biaya operasional perangkat lunak sesuai ukuran usaha, tanpa biaya tersembunyi, dan memastikan kasir tetap bisa beroperasi meski kuota sementara habis.
  - 4 Pilar Manfaat:
    1. Pembayaran Otomatis 24/7 TriPay (QRIS Instan & Virtual Account bank terkemuka).
    2. Buffer Darurat Transaksi (kasir tetap bisa memproses pesanan darurat walau kuota habis).
    3. Jaminan Keamanan Data (No-Data-Punishment: data transaksi tidak pernah dihapus).
    4. Penghematan Kuota Mandiri (fitur hitung ulang dan hapus berkas usang).
- **Alur Kerja Praktis 4 Langkah**:
  - Langkah 1: Pantau Penggunaan Kuota (Kasir POS, Penyimpanan Foto, Token AI).
  - Langkah 2: Buka Halaman Checkout dan Pilih Paket (Standard/Premium/Prestige atau Top-up).
  - Langkah 3: Scan QRIS Dinamis atau Transfer ke Nomor VA Resmi Cooca Pay.
  - Langkah 4: Sistem Mengaktifkan Paket Secara Instan & Mengirimkan Faktur Resmi (PDF).
- **Matriks Implementasi 20 Sektor Industri (6 Klaster)**:
  - *1. F&B*: Cafe & Resto bervolume tinggi menggunakan paket Standard/Premium untuk transaksi POS tak terbatas dan kalkulasi resep HPP.
  - *2. Manufaktur*: Konveksi & Percetakan memanfaatkan kapasitas penyimpanan awan untuk file artwork cetak, mockup sablon, dan surat jalan B2B.
  - *3. Ritel & Apotek*: Minimarket & Apotek membutuhkan kuota ribuan master produk SKU dan histori stok opname barcode.
  - *4. Jasa Harian*: Bengkel & Salon menyimpan foto kondisi fisik kendaraan atau gaya rambut pelanggan tanpa khawatir kuota lokal ponsel penuh.
  - *5. Jasa Proyek*: Kontraktor & Agency menyimpan dokumen kontrak kerja (SPK), foto progres fisik renovasi, dan tagihan invoice bertahap.
  - *6. Distribusi & Agro*: Distributor B2B menggunakan multi-user staf dan sinkronisasi pesanan toko online cabang.

#### 4. Integrasi ke `resources/views/app/docs/index.blade.php`
- Menambahkan tab navigasi dan link Left Sidebar TOC:
  - Di Klaster *Core Business*: Tambahkan link ke `#sec-analytics` dengan ikon `bar-chart-2`.
  - Di Klaster *Finance & Accounting*: Update link `#sec-security` menjadi `#sec-approvals` (Otorisasi Dokumen & MAR).
  - Di Klaster *System & Panduan*: Pastikan link `#sec-billing` mengarah ke panduan billing lengkap.
- Mengganti teks statis seksi dengan `@include`:
  - `@include('app.docs.partials.sec_analytics')`
  - `@include('app.docs.partials.sec_approvals')`
  - `@include('app.docs.partials.sec_billing')`
- Menambahkan kata kunci pencarian pada `matchesSearch(...)` agar pencarian instan spotlight di header dapat menemukan ketiga modul dengan kata kunci seperti `analitik`, `omzet`, `laba rugi`, `peak hours`, `jam sibuk`, `approval`, `maker checker`, `otorisasi`, `plafon`, `billing`, `kuota`, `tripay`, `qris`, `token ai`, `paket langganan`.

---
*Laporan ini disusun secara mandiri berbasis bukti kode aktual oleh Explorer 3.*
