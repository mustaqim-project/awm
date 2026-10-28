# Rencana Implementasi Bertahap & Task List: COOCA AI Studio Generation & Viral Inspiration Library

> **ID Dokumen:** `PLAN-2026-AI-STUDIO-VIRAL-INSPIRATION`  
> **Status:** APPROVED & READY TO EXECUTE (Rencana Resmi Disetujui)  
> **PRD / BRD / FSD Terkait:** `COOCA_AI_Studio_Viral_Inspiration_Blueprint_v2.docx` (BRD/PRD/FSD v1 + Addendum v2)  
> **Standar Rekayasa:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md), Bento Apple HIG v2.0  
> **Target Repositori:** `c:\laragon\www\cooca_core`  
> **Tanggal Efektif:** 28 Oktober 2026  

---

## 1. Ringkasan Eksekutif & Sasaran Rekayasa

Rencana ini merinci langkah-langkah implementasi teknis menyeluruh (*end-to-end*) untuk membangun modul **COOCA AI Studio Generation & Viral Inspiration Library**. Sistem ini menyediakan fasilitas pembuatan dan perancangan materi visual pemasaran berbasis AI (gambar produk dan materi media sosial) yang dirancang untuk dua panel pengguna:

1. **Admin Panel:** Mengelola provider kredensial terpusat (*server-side*), *model capabilities registry*, konfigurasi tarif/margin kredit, kurasi gambar referensi berlisensi, dan rekonsiliasi biaya provider.
2. **Owner Panel:** Menjelajahi perpustakaan inspirasi visual, mengaitkan aset katalog produk riil, menghasilkan prompt terstruktur secara transparan (*anti-blackbox*), menghasilkan gambar berkualitas tinggi, dan mengekspor hasilnya langsung ke Media Library, katalog produk, atau modul media sosial COOCA.

```text
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│ FASE 1: Fondasi Basis Data, Model Eloquent & Two-Phase Credit Ledger (P1 - Kritis)              │
│         - 4 Berkas migrasi database modular (sources, items, projects, generations, ledger)     │
│         - Model Eloquent, relasi multi-tenant (business_id), casts & UUID primary key           │
│         - CreditLedgerService: Transaksi Two-Phase (Hold -> Settle -> Release/Refund)          │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                ↓                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 2: Domain Engine, Provider Adapters & AI Prompt Compiler (P1 - Kritis)                     │
│         - Provider Adapter Interface & OpenAI Image Generation Adapter                          │
│         - VisualReferenceAnalyzer (Multimodal Vision API Parser)                                │
│         - PromptCompilerService (Model-Neutral Prompt & Provider-Specific Payload)              │
│         - RightsPolicyService (Binary Rights Gate: CC0, Commercial, Attribution)                │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                ↓                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 3: Orchestrator Asinkron, Antrean Job & Controller-Level RBAC (P1 - Kritis)               │
│         - GenerationJob: Worker antrean Laravel dengan Idempotency & Concurrency Lock           │
│         - Controller Admin Console (Kredensial terenkripsi & Pricing Engine)                   │
│         - Controller Owner Panel dengan HasMiddleware RBAC & CSRF Protection                    │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                ↓                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 4: Antarmuka Pengguna Bento Apple HIG (Layar A, B, C, D) (P1 - Kritis)                     │
│         - Halaman A: Inspiration Library (Filter sumber, lisensi & relevansi industri)          │
│         - Halaman B: Brief Builder (Pemilih produk katalog & role aset)                         │
│         - Halaman C: Prompt Transparency Sheet (Vision analysis + editable prompt + cost quote) │
│         - Halaman D: Output Canvas, Revisions, dan 1-Click Export ke Media Library             │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                ↓                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 5: Stock API Integrations, Trend Scoring & Hardening Keamanan (P2 - Tinggi)               │
│         - OpenverseAdapter & PexelsAdapter dengan rate limit server-side & cache 24 jam         │
│         - TrendScoringService (Pemisahan objektif: momentum pencarian vs performa konten)       │
│         - SSRF Shield pada image fetcher & Prompt Injection Sanitizer                           │
│         - Suite Pengujian Otomatis 100% Lolos (AC-001 s/d AC-016) & Pembaruan Dokumentasi 3-Layer│
└─────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Inventaris Berkas Kode Terlibat (Codebase Mapping)

### A. Berkas yang Akan Dibuat Baru (*Files to be Generated*):

#### 1. Migrasi Basis Data (`database/migrations/`):
- `2026_11_01_000001_create_ai_inspiration_sources_tables.php` (Sources, Credentials, Policies)
- `2026_11_01_000002_create_ai_inspiration_catalog_tables.php` (Categories, Items, Metrics, Trend Scores, Visual Analyses)
- `2026_11_01_000003_create_ai_studio_project_tables.php` (Projects, Project Assets, Prompt Versions)
- `2026_11_01_000004_create_ai_studio_generation_and_ledger_tables.php` (Generations, Generation Assets, Credit Transactions)

#### 2. Model Eloquent (`app/Models/AiStudio/`):
- `app/Models/AiStudio/InspirationSource.php`
- `app/Models/AiStudio/InspirationSourcePolicy.php`
- `app/Models/AiStudio/InspirationItem.php`
- `app/Models/AiStudio/InspirationCategory.php`
- `app/Models/AiStudio/InspirationVisualAnalysis.php`
- `app/Models/AiStudio/InspirationTrendScore.php`
- `app/Models/AiStudio/BusinessInspirationProject.php`
- `app/Models/AiStudio/BusinessInspirationProjectAsset.php`
- `app/Models/AiStudio/BusinessInspirationPromptVersion.php`
- `app/Models/AiStudio/BusinessInspirationGeneration.php`
- `app/Models/AiStudio/BusinessInspirationGenerationAsset.php`
- `app/Models/AiStudio/AiCreditTransaction.php`

#### 3. Domain Services & Adapters:
- `app/Domain/Ai/Inspiration/Contracts/InspirationAdapterInterface.php`
- `app/Domain/Ai/Inspiration/Adapters/OpenverseAdapter.php`
- `app/Domain/Ai/Inspiration/Adapters/PexelsAdapter.php`
- `app/Domain/Ai/Inspiration/Adapters/CuratedLibraryAdapter.php`
- `app/Domain/Ai/Inspiration/InspirationSearchService.php`
- `app/Domain/Ai/Inspiration/RightsPolicyService.php`
- `app/Domain/Ai/Inspiration/TrendScoringService.php`
- `app/Domain/Ai/Studio/Contracts/ImageGenerationProviderInterface.php`
- `app/Domain/Ai/Studio/Adapters/OpenAiImageAdapter.php`
- `app/Domain/Ai/Studio/Analyzers/VisualReferenceAnalyzer.php`
- `app/Domain/Ai/Studio/Analyzers/BusinessAssetAnalyzer.php`
- `app/Domain/Ai/Studio/Compilers/CreativeBriefResolver.php`
- `app/Domain/Ai/Studio/Compilers/PromptCompilerService.php`
- `app/Domain/Ai/Studio/Billing/CostQuoteService.php`
- `app/Domain/Ai/Studio/Billing/CreditLedgerService.php`
- `app/Domain/Ai/Studio/Orchestration/GenerationOrchestrator.php`
- `app/Domain/Ai/Studio/Jobs/ProcessImageGenerationJob.php`
- `app/Domain/Ai/Studio/Storage/AssetStorageManager.php`

#### 4. Controllers & Requests:
- `app/Http/Controllers/Web/AiStudio/OwnerAiStudioWebController.php`
- `app/Http/Controllers/Web/AiStudio/OwnerInspirationWebController.php`
- `app/Http/Controllers/Admin/AdminAiStudioSourceController.php`
- `app/Http/Controllers/Admin/AdminAiStudioPricingController.php`
- `app/Http/Requests/AiStudio/CompilePromptRequest.php`
- `app/Http/Requests/AiStudio/DispatchGenerationRequest.php`

#### 5. Views (Bento Apple HIG Blade):
- `resources/views/app/ai-studio/index.blade.php` (Dashboard Utama & Saldo)
- `resources/views/app/ai-studio/inspiration/index.blade.php` (Layar A: Inspiration Library)
- `resources/views/app/ai-studio/create.blade.php` (Layar B: Brief & Aset Produk)
- `resources/views/app/ai-studio/prompt-preview.blade.php` (Layar C: Sheet Transparansi Prompt)
- `resources/views/app/ai-studio/show.blade.php` (Layar D: Output Canvas & Revisi)
- `resources/views/admin/ai-studio/sources/index.blade.php` (Admin: Manajemen Provider)
- `resources/views/admin/ai-studio/pricing/index.blade.php` (Admin: Pengaturan Tarif Kredit)

#### 6. Automated Feature Tests (`tests/Feature/AiStudio/`):
- `tests/Feature/AiStudio/TenantIsolationTest.php`
- `tests/Feature/AiStudio/RightsPolicyGateTest.php`
- `tests/Feature/AiStudio/TwoPhaseCreditLedgerTest.php`
- `tests/Feature/AiStudio/PromptCompilerTest.php`
- `tests/Feature/AiStudio/GenerationIdempotencyTest.php`

### B. Berkas Eksisting yang Dimodifikasi (*Existing Files to be Updated*):
- `routes/owner.php` (Mendaftarkan grup rute `/ai-studio`)
- `routes/admin.php` (Mendaftarkan grup rute `/admin/ai-studio`)
- `resources/views/layouts/partials/sidebar.blade.php` (Menambahkan menu navigasi AI Studio)
- `database/seeders/RbacSeeder.php` (Mendaftarkan permission `ai_studio.*`)
- `docs/AiWorkHistory.md` (Pencatatan riwayat rekayasa)
- `docs/SYSTEM_GUIDE.md` (Pembaruan arsitektur sistem)

---

## 3. Rincian Langkah Implementasi Per Fase

---

### FASE 1: Fondasi Basis Data, Model Eloquent & Two-Phase Credit Ledger (P1)
**Tujuan:** Membangun struktur tabel yang *audit-safe*, model Eloquent dengan isolasi tenant otomatis, serta sistem ledger kredit finansial dua tahap (*Hold $\rightarrow$ Settle $\rightarrow$ Release*).

1. **Migrasi Basis Data:**
   - Membuat 4 file migrasi dengan foreign key cascade/restrict yang tepat, UUID v4 primary keys, dan indeks performa multi-kolom `(business_id, status)`.
2. **Model Eloquent:**
   - Menerapkan trait `BelongsToBusiness` pada semua model yang menyimpan data bisnis merchant.
   - Menambahkan casting JSON untuk field metadata, brief, dan snapshot harga.
3. **CreditLedgerService:**
   - Menerapkan metode `reserveHold(string $businessId, int $credits, string $idempotencyKey)`.
   - Menggunakan query `lockForUpdate()` dalam transaksi DB untuk mencegah *race condition*.
   - Menerapkan metode `settleHold(...)` untuk mendebit final, dan `releaseHold(...)` untuk mengembalikan kredit saat provider gagal atau timeout.

---

### FASE 2: Domain Engine, Provider Adapters & AI Prompt Compiler (P1)
**Tujuan:** Menghadirkan mesin analisis visual multimodal dan perakit prompt cerdas yang mematuhi batasan produk fisik riil merchant.

1. **Provider Adapters:**
   - Membangun `InspirationAdapterInterface` dengan metode `search()`, `getItem()`, `getAttribution()`.
   - Membangun `OpenAiImageAdapter` yang memanggil endpoint image generation server-side dengan parameter resolusi dan aspek rasio terpetakan.
2. **VisualReferenceAnalyzer:**
   - Menggunakan model multimodal (Vision) untuk mengekstrak atribut visual referensi ke dalam JSON terstruktur: subjek, komposisi, pencahayaan, sudut kamera, palet warna, mood, dan ruang teks (*negative space*).
3. **PromptCompilerService:**
   - Menggabungkan data analisis visual referensi dengan brief merchant dan aset katalog produk.
   - **Guardrail Utama:** Menetapkan instruksi tegas agar AI tidak mengutak-atik bentuk kemasan produk, teks label paten, logo bisnis, atau mengarang klaim sertifikasi.
4. **RightsPolicyService:**
   - Evaluasi biner: Menolak referensi yang lisensinya tidak mengizinkan pemrosesan AI komersial sebelum memanggil Vision API.

---

### FASE 3: Orchestrator Asinkron, Antrean Job & Controller-Level RBAC (P1)
**Tujuan:** Menjalankan generasi gambar secara asinkron tanpa *blocking* request HTTP web, menjamin idempotency, dan menegakkan otorisasi controller.

1. **GenerationJob (Queue Worker):**
   - Job antrean Laravel (`ShouldQueue`) dengan batas *timeout* 120 detik dan percobaan maksimal 2 kali (*bounded retry*).
   - Menggunakan kunci idempotensi unik `business_id + idempotency_key` agar tidak terjadi tagihan ganda (*double charge*).
2. **Controller Owner & Admin:**
   - Menerapkan `implements HasMiddleware` pada controller web.
   - Memvalidasi kepemilikan aset produk sebelum dihubungkan ke proyek AI Studio (`$product->business_id === Context::businessId()`).
   - Admin Controller mengisolasi pembacaan secret provider agar tidak pernah terkirim ke klien dalam keadaan polos (*masked display*).

---

### FASE 4: Antarmuka Pengguna Bento Apple HIG (P1)
**Tujuan:** Menghadirkan antarmuka pengguna 4-langkah yang bersih, lapang, ramah pengguna UMKM usia 40–65 tahun, dan bebas tumpang tindih (*anti-overlap*).

1. **Halaman A (Inspiration Library):**
   - Grid kartu Bento untuk menampilkan gambar referensi lengkap dengan lencana lisensi (*CC0*, *Komersial Diizinkan*), nama fotografer, rasio, dan tombol `[ Gunakan Ide ]`.
2. **Halaman B (Creative Brief Builder):**
   - Dropdown pemilih produk katalog COOCA dengan pratinjau thumbnail instan.
   - Pilihan peran aset (*Primary Product*, *Logo*, *Background*).
   - Pengaturan rasio visual (1:1, 4:5, 9:16, 16:9) dan kanal tujuan (Instagram, Toko Online, Marketplace).
3. **Halaman C (Prompt Transparency Sheet):**
   - Panel popover Apple HIG: menampilkan analisis visual, brief pengguna, dan prompt final yang dapat diedit secara manual.
   - Box estimasi kredit yang jelas sebelum konfirmasi akhir.
4. **Halaman D (Output Canvas):**
   - Tampilan gambar resolusi tinggi dengan lencana status proses (`queued`, `processing`, `succeeded`).
   - Tombol 1-klik: `[ Simpan ke Katalog Produk ]`, `[ Unduh File ]`, dan `[ Revisi Cepat ]`.

---

### FASE 5: Integrasi Stock API, Trend Scoring & Hardening Keamanan (P2)
**Tujuan:** Menghubungkan API stok gambar legal, menyempurnakan kalkulasi tren tanpa manipulasi, dan memastikan seluruh celah keamanan tertutup.

1. **Stock API Adapters:**
   - Mengaktifkan `OpenverseAdapter` dan `PexelsAdapter` dengan sistem cache 24 jam untuk meminimalkan beban request API.
2. **TrendScoringService:**
   - Mengukur *Trend Momentum Score* dari data riil kata kunci pencarian, bukan angka buatan.
3. **Hardening Keamanan:**
   - Mengimplementasikan filter SSRF pada fetcher URL eksternal (memblokir IP lokal/private seperti `127.0.0.1`, `10.0.0.0/8`, metadata AWS/GCP).
   - Membersihkan metadata EXIF pada file unggahan pengguna dan memverifikasi MIME type berbasis *Magic Bytes*.
4. **Verifikasi & Dokumentasi:**
   - Menjalankan suite pengujian unit dan integrasi otomatis dengan target 100% lulus.
   - Memperbarui dokumentasi 3-layer sesuai SOP rekayasa COOCA.

---

## 4. Daftar Tugas Rinci & Terukur (Step-by-Step Task List)

### Bagian 1: Migrasi Basis Data & Model (Database & ORM Layer)
- [x] **TASK-01:** Buat migrasi basis data sumber inspirasi & kebijakan lisensi `2026_11_01_000001_create_ai_inspiration_sources_tables.php`.
- [x] **TASK-02:** Buat migrasi katalog referensi, analisis visual & trend score `2026_11_01_000002_create_ai_inspiration_catalog_tables.php`.
- [x] **TASK-03:** Buat migrasi proyek merchant, aset produk & prompt versioning `2026_11_01_000003_create_ai_studio_project_tables.php`.
- [x] **TASK-04:** Buat migrasi generasi gambar, aset output & ledger transaksi kredit `2026_11_01_000004_create_ai_studio_generation_and_ledger_tables.php`.
- [x] **TASK-05:** Buat Model Eloquent di `app/Models/AiStudio/` dengan relasi lengkap, casts JSON, dan trait `BelongsToBusiness`.
- [x] **TASK-06:** Daftarkan permission `ai_studio.view`, `ai_studio.generate`, `ai_studio.manage`, `admin.ai_studio.sources`, `admin.ai_studio.pricing` pada `RbacSeeder.php`.

### Bagian 2: Layanan Finansial & Ledger Kredit (Financial Engine)
- [x] **TASK-07:** Implementasikan `CreditLedgerService` dengan transaksi *two-phase atomic hold* (`reserveHold`, `settleHold`, `releaseHold`).
- [x] **TASK-08:** Buat `CostQuoteService` untuk menghitung kebutuhan kredit dinamis berdasarkan rasio dan resolusi model aktif.
- [x] **TASK-09:** Buat automated test `tests/Feature/AiStudio/TwoPhaseCreditLedgerTest.php` untuk menguji pencegahan *double-spend* dan *race condition*.

### Bagian 3: Domain Engine AI, Vision & Prompt Compiler (AI Subsystem)
- [x] **TASK-10:** Buat kontrak interface `InspirationAdapterInterface` dan `ImageGenerationProviderInterface`.
- [x] **TASK-11:** Buat `VisualReferenceAnalyzer` untuk mengekstrak struktur visual gambar via model Vision.
- [x] **TASK-12:** Buat `BusinessAssetAnalyzer` untuk membaca data master produk dan atribut penting yang wajib dipertahankan.
- [x] **TASK-13:** Buat `PromptCompilerService` yang menghasilkan prompt *model-neutral* dan payload spesifik provider.
- [x] **TASK-14:** Implementasikan `RightsPolicyService` sebagai *gatekeeper* lisensi komersial (CC0, Pexels License, dll).
- [x] **TASK-15:** Buat `OpenAiImageAdapter` untuk eksekusi generasi gambar via OpenAI / Azure / Compatible API.

### Bagian 4: Antrean & Orchestration (Async Job Worker)
- [x] **TASK-16:** Buat `GenerationOrchestrator` yang mengatur siklus hidup generasi: validasi kuota $\rightarrow$ hold kredit $\rightarrow$ dispatch job.
- [x] **TASK-17:** Buat job antrean `ProcessImageGenerationJob` lengkap dengan *error handling*, sanitasi respon, dan auto-release kredit saat gagal.
- [x] **TASK-18:** Buat `AssetStorageManager` untuk menyimpan output pada disk privat: `businesses/{business_id}/ai-studio/...`.

### Bagian 5: Controller & Routing (HTTP & Webhook Layer)
- [ ] **TASK-19:** Buat `OwnerAiStudioWebController` untuk menangani CRUD proyek, kompilasi prompt, dan pemesanan generasi.
- [ ] **TASK-20:** Buat `OwnerInspirationWebController` untuk endpoint pencarian dan penjelajahan katalog inspirasi.
- [ ] **TASK-21:** Buat controller Admin Console `AdminAiStudioSourceController` dan `AdminAiStudioPricingController`.
- [ ] **TASK-22:** Daftarkan rute lengkap di `routes/owner.php` dan `routes/admin.php` dengan middleware RBAC controller.

### Bagian 6: Antarmuka Pengguna Bento Apple HIG (Blade & Alpine.js)
- [ ] **TASK-23:** Implementasikan `resources/views/app/ai-studio/index.blade.php` (Dashboard, metrik saldo kredit, tombol Buat Baru).
- [ ] **TASK-24:** Implementasikan `resources/views/app/ai-studio/inspiration/index.blade.php` (Layar A: Masonry grid, filter lisensi & industri).
- [ ] **TASK-25:** Implementasikan `resources/views/app/ai-studio/create.blade.php` (Layar B: Form brief kreatif, selector produk katalog COOCA).
- [ ] **TASK-26:** Implementasikan `resources/views/app/ai-studio/prompt-preview.blade.php` (Layar C: Sheet transparansi prompt & kuotasi kredit).
- [ ] **TASK-27:** Implementasikan `resources/views/app/ai-studio/show.blade.php` (Layar D: Output canvas, status polling, dan tombol aksi 1-klik).
- [ ] **TASK-28:** Implementasikan antarmuka Admin Console di `resources/views/admin/ai-studio/`.
- [ ] **TASK-29:** Tambahkan menu navigasi AI Studio pada sidebar utama `resources/views/layouts/partials/sidebar.blade.php`.

### Bagian 7: Hardening Keamanan, Pengujian & Dokumentasi
- [ ] **TASK-30:** Pasang proteksi SSRF pada fetcher URL gambar eksternal di `InspirationSearchService`.
- [ ] **TASK-31:** Buat automated test `tests/Feature/AiStudio/TenantIsolationTest.php` untuk memverifikasi isolasi multi-tenant data.
- [ ] **TASK-32:** Buat automated test `tests/Feature/AiStudio/RightsPolicyGateTest.php` untuk memverifikasi pemblokiran lisensi non-komersial.
- [ ] **TASK-33:** Jalankan seluruh rangkaian pengujian fitur (`php vendor/phpunit/phpunit/phpunit tests/Feature/AiStudio/`) dan pastikan 100% lulus.
- [ ] **TASK-34:** Perbarui riwayat rekayasa pada `docs/AiWorkHistory.md` dan panduan sistem pada `docs/SYSTEM_GUIDE.md`.

---

## 5. Matriks Pengujian & Kriteria Penerimaan (Acceptance Criteria)

| Kode AC | Kriteria Pengujian | Bukti Verifikasi |
|---|---|---|
| **AC-001** | Merchant dapat menjelajahi gambar referensi dan melihat atribusi/lisensi sah. | Feature test `InspirationSearchTest@test_can_browse_with_attribution`. |
| **AC-002** | Gambar dengan lisensi non-komersial/terlarang otomatis diblokir dari proses generasi. | Feature test `RightsPolicyGateTest@test_blocked_license_cannot_be_generated`. |
| **AC-003** | Merchant dapat memilih produk dari katalog bisnisnya sendiri dan menentukan peran aset. | Feature test `AiStudioProjectTest@test_attach_valid_catalog_product`. |
| **AC-004** | Upaya menautkan produk milik bisnis lain menghasilkan respon HTTP 403 / 404. | Feature test `TenantIsolationTest@test_cannot_attach_other_tenant_product`. |
| **AC-005** | Vision AI menghasilkan atribut visual terstruktur dan Prompt Builder merakit prompt dinamis. | Unit test `PromptCompilerTest@test_compiles_prompt_from_reference_and_brief`. |
| **AC-006** | Prompt final dapat disunting manual oleh merchant dan menjadi teks aktual yang dikirim ke provider. | Feature test `AiStudioGenerationTest@test_custom_prompt_is_sent_to_provider`. |
| **AC-007** | Saldo kredit terpotong saat reservasi awal (`hold`); jika provider gagal, saldo otomatis kembali 100%. | Feature test `TwoPhaseCreditLedgerTest@test_hold_and_auto_release_on_failure`. |
| **AC-008** | Request paralel ganda dengan `idempotency_key` yang sama hanya memproses 1 transaksi debit. | Concurrency test `GenerationIdempotencyTest@test_concurrent_requests_safe`. |
| **AC-009** | Kunci API provider Admin tidak pernah bocor pada respons JSON klien maupun log exception. | Security test `AdminSecretExposureTest@test_api_keys_are_never_exposed`. |
| **AC-010** | Gambar hasil render dapat disimpan langsung menjadi foto katalog produk baru di database. | Feature test `AiStudioMediaSaveTest@test_save_output_to_product_catalog`. |

---

## 6. Definition of Done (DoD)

Pekerjaan implementasi modul ini hanya dinyatakan **SELESAI (DONE)** apabila memenuhi seluruh kriteria berikut:
1. Seluruh 4 berkas migrasi database berhasil dieksekusi tanpa galat di lingkungan pengujian.
2. Seluruh class service, adapter, controller, dan request memiliki *type-hinting* ketat (`declare(strict_types=1)`).
3. 100% rute baru diproteksi oleh middleware isolasi tenant dan izin controller-level RBAC.
4. Tampilan antarmuka mematuhi standar Bento Apple HIG COOCA: bebas emoji Unicode, menggunakan Lucide icon, dan responsif dari layar smartphone (360px) hingga desktop (1280px+).
5. Seluruh suite pengujian otomatis `tests/Feature/AiStudio/` (minimal 10 test case, 40+ assertions) lulus 100% tanpa kegagalan.
6. Riwayat pengembangan dicatat secara lengkap pada `docs/AiWorkHistory.md` dan ringkasan arsitektur dipromosikan ke `docs/SYSTEM_GUIDE.md`.
