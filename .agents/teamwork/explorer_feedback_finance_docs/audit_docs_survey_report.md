# AUDIT & SURVEY REPORT: FEEDBACK & FINANCE MODULE HEALTH, TEST SUITES & DOCUMENTATION

**Survey Date**: 2026-11-08  
**Surveyor**: Explorer 3 (Teamwork Subagent)  
**Target Modul**: Feedback (`resources/views/app/feedback`) & Finance (`resources/views/app/finance`)  
**Scope**: Module Health, Error Handling, Existing Test Coverage, Missing Acceptance Tests, Business Owner Documentation (`resources/views/app/docs/`)  
**Status**: `SURVEY_COMPLETE` [VERIFIED]

---

## 1. Executive Summary & Audit Scorecard

Berdasarkan mandat dari `ORIGINAL_REQUEST.md`, `cooca-agent-directive`, `laravel-module-audit`, dan `business-owner-doc-generator`, telah dilakukan audit menyeluruh tanpa modifikasi source code (read-only) terhadap modul Feedback dan Finance pada COOCA Core.

### Ringkasan Kondisi Saat Ini (Current State Scorecard)

| Aspek Audit | Feedback Modul | Finance Modul | Status Verifikasi |
|---|---|---|---|
| **Syntax PHP & Blade** | 100% Bebas Syntax Error (`php -l` PASS) | 100% Bebas Syntax Error (`php -l` PASS pada 10 Controller & 16 View) | `[VERIFIED]` PASS |
| **Route Integrity** | 16 Rute (`php artisan route:list --path=feedback`) OK | 87 Rute (`php artisan route:list --path=finance`) OK | `[VERIFIED]` PASS |
| **Controller-Level RBAC** | `HasMiddleware` + `require.role:owner` | `HasMiddleware` + 7 permission terdaftar di `RbacSeeder.php` | `[VERIFIED]` PASS |
| **Existing Test Suite** | 11 Test Lolos (64 Assertions, 0 Failure) | 35 Test Lolos (354 Assertions, 0 Failure) | `[VERIFIED]` PASS |
| **Empty State UI** | Tersedia di `index.blade.php` | Tersedia di 15 view; 1 minor gap di `settlements/show.blade.php` | `[VERIFIED]` 95% PASS |
| **Zero-Overlap View Layout** | Mobile `pb-28 sm:pb-32 lg:pb-10`, z-index rapi | Mobile `pb-28 sm:pb-32`, modal `z-[200]`, action bar `sticky` | `[VERIFIED]` PASS |
| **Business Owner Docs** | **TIDAK ADA** (Belum dibuat sama sekali) | **STUB MINIMALIS** (35 baris di `index.blade.php`, tanpa matriks 20 industri) | `[VERIFIED]` **CRITICAL GAP** |

---

## 2. Survey 1: Module Health & Error Handling

### 2.1 Modul Feedback (`resources/views/app/feedback/`)

#### A. Arsitektur Controller & Route
- **Web Controller**: `app/Http/Controllers/Web/FeedbackWebController.php` (183 baris)
  - Mengimplementasikan `Illuminate\Routing\Controllers\HasMiddleware`
  - Controller-level RBAC:
    ```php
    public static function middleware(): array
    {
        return [
            new Middleware('require.role:owner'),
        ];
    }
    ```
    Hanya pemilik usaha (*Owner*) yang diizinkan mengakses modul Feedback untuk platform.
  - Multi-tenant isolation: Setiap aksi (`bugs`, `showBug`, `features`, `showFeature`, `streamBugAttachment`) memverifikasi kepemilikan tenant:
    ```php
    $business = Context::requireBusiness();
    abort_unless($bugReport->business_id === $business->id, 403);
    ```
- **Admin Controller**: `app/Http/Controllers/Admin/AdminFeedbackController.php`
  - Terproteksi oleh `require.admin.permission:admin.feedback.view` dan `admin.feedback.manage`.

#### B. Validasi Input & Keamanan File Upload
- **`storeBug` (Baris 50-78)**:
  - Validasi ketat: `title` (max:180), `category` (`BugReport::CATEGORIES`), `severity` (`BugReport::SEVERITIES`), `description` (max:10000), `steps_to_reproduce`, `expected_behavior`, `actual_behavior`, `environment`.
  - Validasi upload attachment: `nullable|file|mimes:jpg,jpeg,png,webp,pdf,txt,log|max:5120`.
  - Enforce kuota storage per tenant via `StorageTrackingService::assertCanUpload($owner, (int) $file->getSize(), 'attachment')` dan pencatatan riwayat file di `StorageFile::CATEGORY_FEEDBACK_ATTACHMENT`.
  - Disimpan pada disk `local` privat di direktori `feedback/bugs` (bukan public unauthenticated asset).
- **`storeFeature` (Baris 142-150)**:
  - Validasi `title`, `category`, `description`, `business_value`, `use_case`, `proposed_solution`, `priority` (`in:low,normal,high`).
- **Audit Logging**:
  - `storeBug` mencatat `action => 'feedback.bug_reported'` di tabel `audit_logs`.
  - `storeFeature` mencatat `action => 'feedback.feature_requested'` di tabel `audit_logs`.

#### C. Flash Notifications & Penanganan Error
- **Pemberitahuan Sukses**:
  - `storeBug` mengembalikan `redirect()->route('feedback.bugs.show', $report)->with('success', 'Laporan bug berhasil dikirim.')`.
  - `storeFeature` mengembalikan `redirect()->route('feedback.features.show', $feature)->with('success', 'Request fitur berhasil dikirim.')`.
  - Ditampilkan di `show.blade.php:44-49` via banner Apple HIG hijau (`bg-[#34C759]/10 border-[#34C759]/20 text-[#34C759]`) dengan ikon Lucide `check-circle`.
- **Penanganan Error Validasi**:
  - Di `create.blade.php:37-46`, error form ditampilkan via `@if ($errors->any())` summary card merah (`bg-[#FF3B30]/10 border-[#FF3B30]/20 text-[#FF3B30]`) dengan ikon `alert-circle`.
  - Field-level error juga disediakan via `@error('title')` dan `@error('description')`.

#### D. Empty State Views & Null Safety
- **Empty State**:
  - Di `index.blade.php:92-111`, ketika `$items->count() === 0`, dirender kartu kosong Apple HIG dengan ikon Lucide (`bug` atau `sparkles`), teks judul `empty_bugs`/`empty_features`, subtitle penjelasan, dan tombol CTA langsung (`feedback.bugs.create` atau `feedback.features.create`).
- **Null Safety**:
  - `show.blade.php:84`: `@if ($item->reporter ?? $item->requester)` aman terhadap relasi null.
  - `show.blade.php:56`: Fallback dinamis bila localization key tidak ditemukan:
    ```php
    {{ __('feedback.status_' . $item->status) !== 'feedback.status_' . $item->status ? __('feedback.status_' . $item->status) : str_replace('_', ' ', ucfirst($item->status)) }}
    ```
  - `streamBugAttachment`: Dilindungi oleh pengecekan null path (`abort_unless($bugReport->attachment_path, 404)`) dan verifikasi keberadaan fisik file di storage sebelum streaming response.

---

### 2.2 Modul Finance (`resources/views/app/finance/`)

#### A. Arsitektur 7 Web Controller Finance & Penegakan RBAC
Semua controller di `app/Http/Controllers/Web/Finance/` mengadopsi standar Controller-Level RBAC melalui `HasMiddleware`:

1. **`PosFinanceWebController.php`** (274 baris)
   - `accounting.view` &rarr; `journals`
   - `expenses.view` &rarr; `expenses`, `viewReceipt`
   - `expenses.manage` &rarr; `storeExpense`
2. **`CashLedgerWebController.php`** (571 baris)
   - `finance.cash_bank` &rarr; `index`, `ledger`, `storeAccount`, `updateAccount`, `toggleAccount`, `storeInflow`, `storeOutflow`, `transfer`
   - `finance.receivables` &rarr; `receivables`, `exportReceivables`, `receivablesTemplate`, `previewReceivables`, `executeReceivables`
   - `finance.payables` &rarr; `payables`, `exportPayables`, `payablesTemplate`, `previewPayables`, `executePayables`
3. **`AccountingWebController.php`** (515 baris)
   - `accounting.view` &rarr; `coaIndex`, `coaExport`, `coaTemplate`, `coaImportPreview`, `balanceSheet`, `trialBalance`, `generalLedger`, `reconciliationIndex`, `openingBalanceTemplate`, `openingBalancePreview`
   - `['accounting.view', 'entitlement:export']` &rarr; `balanceSheetExportExcel`, `trialBalanceExportExcel`, `generalLedgerExportExcel`
   - `accounting.manage` &rarr; `coaStore`, `coaUpdate`, `coaDestroy`, `coaImportExecute`, `openingBalanceExecute`, `reconciliationUpload`, `reconciliationMatch`, `reconciliationUnmatch`
4. **`ExternalReconciliationWebController.php`**
   - `finance.cash_bank` &rarr; Seluruh method (dashboard likuiditas, import statement eksternal, selisih rekonsiliasi)
5. **`StoreEdcTerminalWebController.php`**
   - `finance.cash_bank` &rarr; Pendaftaran dan toggle mesin EDC toko
6. **`MerchantPayoutAccountWebController.php`**
   - `finance.cash_bank` &rarr; Rekening bank pencairan dana merchant
7. **`PaymentSettlementWebController.php`**
   - `finance.cash_bank` &rarr; Settlement pembayaran digital Cooca Pay

Semua permission ini terverifikasi 100% sinkron pada `database/seeders/RbacSeeder.php` baris 98-106.

#### B. Transaksi Database & Integritas Finansial
- Seluruh mutasi kas, pencatatan beban operasional, dan jurnal pembukuan ganda dieksekusi di dalam `DB::transaction(...)`.
- `PosFinanceWebController@storeExpense` (baris 204-252) menjalankan transaksi atomik:
  1. `Expense::create(...)`
  2. `AutoJournalService::recordExpenseJournal(...)` (membuat entri debit/kredit seimbang)
  3. `CashLedgerService::recordOutflow(...)` (mengurangi saldo akun kas/bank terkait)
  4. `AuditLog::create(...)` dengan `risk_level => AuditLog::RISK_LOW`
- Integritas formula ekspor dilindungi oleh `CsvSanitizer::sanitize(...)` untuk mencegah *CSV / Formula Injection* (mencegah prefix berbahaya `=`, `+`, `-`, `@`, `|`, `cmd`).

#### C. Flash Notifications & Penanganan Error
- Aplikasi memiliki dua layer pemberitahuan:
  1. **Global AppAlert Toast System** di `resources/views/layouts/app.blade.php:2753-2776`: Membaca `session('success')`, `session('error')`, `session('warning')`, `session('info')` secara terpusat via toast floating Apple HIG, mencegah duplikasi banner.
  2. **Local Form Errors**: Ditampilkan di view input seperti `expenses.blade.php:41` dan `cash-bank/index.blade.php:64`.

#### D. Empty State Views & Identifikasi Gap
- **15 Views dengan Empty State Sempurna**:
  - `expenses.blade.php:215` (tabel kosong & filter kosong)
  - `journals.blade.php:208`
  - `payables.blade.php:229` & `line 302`
  - `receivables.blade.php:235` & `line 352`
  - `accounting/balance-sheet.blade.php:138, 169, 223`
  - `accounting/coa.blade.php:243`
  - `accounting/general-ledger.blade.php:184`
  - `accounting/reconciliation.blade.php:91, 182`
  - `accounting/trial-balance.blade.php:155`
  - `cash-bank/index.blade.php:164, 429, 490`
  - `cash-bank/ledger.blade.php:233, 298`
  - `edc-terminals/index.blade.php:150`
  - `external-reconciliation/index.blade.php:193, 270`
  - `payout-accounts/index.blade.php:107`
  - `settlements/index.blade.php:366`
- **1 Minor Gap Ditemukan**:
  - File: `resources/views/app/finance/settlements/show.blade.php:219-235`
  - Isu: Loop breakdown alokasi transaksi menggunakan `@foreach($settlement->allocations as $allocation)` tanpa blok `@empty`. Bila settlement tidak memiliki alokasi, tabel `<tbody>` akan kosong melompong tanpa pesan informatif.
  - Rekomendasi perbaikan untuk implementer: Ganti dengan `@forelse($settlement->allocations as $allocation) ... @empty <tr><td colspan="3" class="py-8 text-center text-black/40 dark:text-white/40">Belum ada transaksi teralokasi pada pencairan ini.</td></tr> @endforelse`.

---

## 3. Survey 2: Existing Test Coverage & Missing Test Scenarios

### 3.1 Katalog Test yang Sudah Ada (Existing Test Suites)
Dua rangkaian test khusus untuk Feedback dan Finance telah dijalankan dan diverifikasi:

1. **Feedback Test Suite**:
   - `tests/Feature/FeedbackModuleAuditTest.php` (7 test cases):
     - `test_guest_is_redirected_to_login` `[PASS]`
     - `test_non_owner_role_is_blocked_from_feedback_module` `[PASS]`
     - `test_owner_can_view_bugs_and_features_index` `[PASS]`
     - `test_owner_can_create_bug_report_with_audit_log` `[PASS]`
     - `test_owner_can_create_feature_request_with_audit_log` `[PASS]`
     - `test_idor_protection_bug_report_and_feature_request_show` `[PASS]`
     - `test_attachment_validation_rejects_dangerous_file_types` `[PASS]`
   - `tests/Feature/FeedbackWorkflowTest.php` (4 test cases):
     - `test_owner_can_submit_bug_and_admin_can_track_progress` `[PASS]`
     - `test_owner_can_submit_feature_request_and_admin_can_release_it` `[PASS]`
     - `test_bug_reports_and_features_are_isolated_by_belongs_to_business_scope` `[PASS]`
     - `test_feedback_pages_render_3_row_apple_hig_header_tabs_and_breadcrumbs` `[PASS]`
   - **Hasil**: 11 passed, 64 assertions, 0 failures (waktu eksekusi: ~23.5 detik).

2. **Finance Test Suite**:
   - `tests/Feature/FinanceModuleAuditTest.php` (8 test cases)
   - `tests/Feature/FinanceAndAccountingNavigationTest.php` (13 test cases)
   - `tests/Feature/FinanceApiIntegrationTest.php` (6 test cases)
   - `tests/Feature/AdminFinanceTest.php` (8 test cases)
   - **Hasil**: 35 passed, 354 assertions, 0 failures (waktu eksekusi: ~66.8 detik).

Total test terverifikasi: **46 passing tests, 418 assertions, 0 failures**.

---

### 3.2 Analisis Kesenjangan Test (Missing Test Coverage)
Untuk memenuhi 100% kriteria penerimaan di `ORIGINAL_REQUEST.md`:

1. **Missing 403 Tests pada Sub-Rute Feedback**:
   - Saat ini `FeedbackModuleAuditTest` hanya memeriksa rute `feedback.bugs.index` dan `feedback.features.index` terhadap role non-owner (`staff`).
   - Rute create, store, dan show (`feedback.bugs.create`, `feedback.bugs.store`, `feedback.bugs.show`, `feedback.features.create`, `feedback.features.store`, `feedback.features.show`) belum memiliki assertion eksplisit bahwa non-owner menerima respons HTTP 403 Forbidden.
2. **Missing 403 Tests pada Hub Finance Tertentu**:
   - Saat ini `FinanceModuleAuditTest` hanya memeriksa rute `cash-bank.index`, `coa.index`, dan `expenses.index`.
   - Rute-rute berikut belum diuji respons 403-nya saat diakses oleh user tanpa hak akses:
     - `finance.balance-sheet`
     - `finance.trial-balance`
     - `finance.general-ledger`
     - `finance.reconciliations.index`
     - `finance.payables`
     - `finance.receivables`
     - `finance.external-recon.index`
     - `finance.edc-terminals.index`
     - `finance.payout-accounts.index`
     - `finance.settlements.index`
3. **Automated Zero-Overlap & Button Hierarchy View Test**:
   - Belum ada automated test yang memverifikasi bahwa:
     - Kontainer utama seluruh view modul Feedback dan Finance memiliki padding bawah aman (`pb-28` atau `pb-32`) untuk mencegah benturan dengan mobile bottom navigation.
     - Setiap kartu atau tabel aksi memiliki maksimal 1 tombol primer (`bg-[#007AFF]`).
     - Seluruh modal dialog memiliki kelas `z-[200]` terisolasi tanpa bleed.

---

## 4. Survey 3: Business Owner Documentation Architecture & Blueprint

### 4.1 Arsitektur Dokumentasi di `resources/views/app/docs/`
- Halaman induk: `resources/views/app/docs/index.blade.php` (978 baris).
- Struktur layout dua kolom:
  1. **Sticky Left Sidebar TOC** (`lg:col-span-3`, baris 98-373):
     - Memuat navigasi terkelompok dalam 5 Klaster:
       - Klaster 1: Core Business (Onboarding, POS, B2B, Analitik, AI, AI Studio, Komunitas)
       - Klaster 2: Inventory & Supply Chain (Stok/BOM, Pengadaan PO)
       - Klaster 3: Finance & Accounting (Keuangan, Otorisasi MAR)
       - Klaster 4: Human Resources & CRM (HRM, Pelanggan, Loyalitas CRM, Storefront)
       - Klaster 5: System & Panduan (Billing, Shortcuts, FAQ)
     - Setiap item TOC memiliki ikon Lucide, tag pill semantik (misal: "20 Sektor", "Maker-Checker"), dan click-handler Alpine `@click="if(activeTab !== 'all') activeTab = 'all'"`.
  2. **Main Content Stream** (`lg:col-span-9`, baris 376-805):
     - Memuat seksi-seksi panduan via `@include('app.docs.partials.sec_{nama}')`.
     - Tiap seksi dikendalikan reaktif oleh Alpine.js via `x-show="(activeTab === 'all' || activeTab === '{modul}') && matchesSearch('...keywords...')"` yang terhubung dengan live search spotlight di header.

### 4.2 Standar Anatomi Partial Dokumentasi COOCA (The Universal Doc Contract)
Mempelajari partial bereputasi tinggi (`sec_customers.blade.php`, `sec_billing.blade.php`, `sec_approvals.blade.php`):

```text
sec_{module}.blade.php
├── 1. Header & Quick Links (Judul, Subtitle, Badges, Tombol Akses Langsung route(...))
├── 2. Ringkasan Eksekutif (POV Bisnis Owner: Masalah Nyata + 4 Pilar Nilai Finansial/Operasional)
├── 3. Alur Kerja Langkah demi Langkah (4 Langkah SOP Praktis Toko)
├── 4. Matriks Penerapan Nyata untuk 20 Jenis Usaha (6 Klaster Industri UMKM)
│   ├── Klaster 1: Kuliner & F&B (Resto, Cafe, Bakery, Cloud Kitchen, Katering)
│   ├── Klaster 2: Manufaktur & HPP (Konveksi, Percetakan, Furniture, Bengkel Bubut, Craft)
│   ├── Klaster 3: Ritel & Apotek (Minimarket, Apotek)
│   ├── Klaster 4: Jasa Harian (Bengkel, Barbershop, Laundry, Cuci Mobil)
│   ├── Klaster 5: Jasa Proyek (Kontraktor, WO, Agency)
│   └── Klaster 6: Distribusi & Agro (Distributor B2B/Agro)
├── 5. Keamanan, Otorisasi RBAC & Anti-Fraud Guardrails
└── 6. Tanya Jawab Bisnis Owner (FAQ Praktis)
```

### 4.3 Analisis Kesenjangan & Blueprint Implementasi

#### A. Dokumen Panduan Feedback (`sec_feedback.blade.php`)
- **Status Saat Ini**: Belum ada file partial `resources/views/app/docs/partials/sec_feedback.blade.php` dan belum ada entri di Left Sidebar TOC `docs/index.blade.php`.
- **Spesifikasi Blueprint**:
  - File: `resources/views/app/docs/partials/sec_feedback.blade.php`
  - Anchor ID: `id="sec-feedback"`
  - Filter: `activeTab === 'all' || activeTab === 'feedback' || activeTab === 'system'`
  - Search Keywords: `'feedback masukan kendala bug request fitur pengembang teknis prioritas status perbaikan saran tiket komplain bantuan engineering'`
  - Tombol Quick Action:
    - `feedback.bugs.create` ("Laporkan Kendala / Bug")
    - `feedback.bugs.index` ("Riwayat Laporan Kendala")
    - `feedback.features.create` ("Ajukan Request Fitur")
    - `feedback.features.index` ("Daftar Request Fitur")
    - Seluruh tombol dibungkus `@if(\App\Support\Context::isOwner())`.
  - POV Owner:
    - Masalah yang diselesaikan: Tidak perlu bingung mencari nomor WhatsApp tim teknis saat printer POS macet atau struk tidak keluar; saluran langsung ke meja engineering Cooca Core.
    - 4 Pilar: Komunikasi Jalur Langsung, Pelacakan Real-time 0-100%, Bukti Tangkapan Layar & Log Aman, Inovasi Kolaboratif Berbasis Kebutuhan Industri Nyata.
  - Matriks 20 Industri:
    - Contoh konkret: Resto melaporkan error KDS dapur; Konveksi meminta penambahan rumus susut kain; Apotek meminta fitur pembacaan barcode 2D BPOM; Laundry meminta pengingat WA rak pakaian; Kontraktor meminta form termin SPK, dsb.

#### B. Dokumen Panduan Finance (`sec_finance.blade.php`)
- **Status Saat Ini**: Di `resources/views/app/docs/index.blade.php:684-721`, hanya terdapat placeholder 35 baris dengan 3 kartu generik yang merujuk ke rute laporan (`reports.index`), bukan modul Finance yang sesungguhnya.
- **Spesifikasi Blueprint**:
  - File: `resources/views/app/docs/partials/sec_finance.blade.php`
  - Inklusi di `index.blade.php`: Ganti baris 684-721 dengan `@include('app.docs.partials.sec_finance')`.
  - Anchor ID: `id="sec-finance"`
  - Filter: `activeTab === 'all' || activeTab === 'finance'`
  - Search Keywords: `'keuangan kas bank akuntansi laporan laba rugi neraca arus kas p&l jurnal buku besar coa chart of accounts biaya beban operasional hutang piutang ap ar rekonsiliasi edc settlement cooca pay'`
  - Tombol Quick Action:
    - `finance.cash-bank.index` ("Kas & Rekening Bank") &rarr; `finance.cash_bank`
    - `finance.expenses.index` ("Catat Beban Toko") &rarr; `expenses.view` / `expenses.manage`
    - `finance.journals.index` ("Jurnal Pembukuan") &rarr; `accounting.view`
    - `finance.receivables` ("Piutang Pelanggan / AR") &rarr; `finance.receivables`
    - `finance.payables` ("Hutang Vendor / AP") &rarr; `finance.payables`
    - `finance.coa.index` ("Bagan Akun COA") &rarr; `accounting.view`
    - `finance.balance-sheet` ("Laporan Neraca") &rarr; `accounting.view`
    - `finance.reconciliations.index` ("Rekonsiliasi Bank") &rarr; `accounting.view`
    - `finance.settlements.index` ("Pencairan Dana Cooca Pay") &rarr; `finance.cash_bank`
  - POV Owner:
    - Masalah yang diselesaikan: Menghilangkan kebiasaan mencampur uang pribadi dengan uang toko; otomatisasi jurnal akuntansi tanpa perlu menggaji akuntan bersertifikasi; pencegahan nota belanja fiktif kasir lewat foto struk wajib; monitoring saldo kas laci vs rekening bank harian.
    - 4 Pilar: Pembukuan Ganda Otomatis, Pemantauan Kas Laci & Bank Terpisah, Kendali Ketat Piutang & Hutang Usaha, Rekonsiliasi Otomatis & Sanitasi Formula Audit.
  - Matriks 20 Industri:
    - 20 skenario nyata kas/beban/pembukuan pada 6 klaster bisnis UMKM di Indonesia.

---

## 5. Rekomendasi & Task List untuk Fase Implementasi (Phase 1)

Berikut adalah daftar rekomendasi terarah bagi implementer:

| ID Task | Area | Deskripsi Tindakan | Target File |
|---|---|---|---|
| **T-DOC-001** | Documentation | Buat berkas panduan `sec_feedback.blade.php` lengkap dengan 6 seksi, 4 pilar nilai owner, alur 4 langkah, dan matriks 20 industri. | `resources/views/app/docs/partials/sec_feedback.blade.php` |
| **T-DOC-002** | Documentation | Buat berkas panduan `sec_finance.blade.php` lengkap mencakup 15 hub finansial, kas & bank, beban, jurnal, AR/AP, COA, dan rekonsiliasi. | `resources/views/app/docs/partials/sec_finance.blade.php` |
| **T-DOC-003** | Documentation | Perbarui `resources/views/app/docs/index.blade.php`: Ganti stub seksi finance inline dengan `@include('app.docs.partials.sec_finance')`, tambahkan `@include('app.docs.partials.sec_feedback')`, dan update Left Sidebar TOC Klaster 3 & Klaster 5. | `resources/views/app/docs/index.blade.php` |
| **T-UI-001** | UI Polish | Tambahkan penanganan `@empty` pada tabel alokasi settlement di `settlements/show.blade.php` agar zero-data render dengan pesan ramah. | `resources/views/app/finance/settlements/show.blade.php` |
| **T-TST-001** | Test Suite | Tambahkan test cases 403 Forbidden untuk seluruh sub-rute aksi Feedback terhadap role non-owner (`bugs.create`, `bugs.store`, `features.create`, `features.store`). | `tests/Feature/FeedbackModuleAuditTest.php` |
| **T-TST-002** | Test Suite | Tambahkan test cases 403 Forbidden untuk seluruh hub Finance web controller yang belum diuji otorisasi negatifnya (`balance-sheet`, `trial-balance`, `general-ledger`, `payables`, `receivables`, `reconciliations`, `external-recon`, `edc`, `settlements`). | `tests/Feature/FinanceModuleAuditTest.php` |
| **T-TST-003** | Test Suite | Buat test feature baru yang memverifikasi Blade views Feedback dan Finance tidak memiliki overlap layout (memastikan adanya padding bawah responsif `pb-28`/`pb-32` dan status 200 OK saat dirender). | `tests/Feature/FeedbackAndFinanceLayoutIntegrityTest.php` |

---

*Laporan ini disusun secara code-first, objektif, dan dapat diverifikasi secara mandiri.*
