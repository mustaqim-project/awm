# System Baseline - feedback

> Modul: Platform Feedback & Bug Support | Commit: 38cdcdf | Tanggal: 2026-10-25 | Auditor: AI Audit Agent (laravel-module-audit)

## 1. Lingkungan & Repositori
- **Repositori:** `c:\laragon\www\cooca_core`
- **Framework:** Laravel 11.x (PHP 8.2+)
- **Database:** MariaDB / MySQL
- **Baseline Git Commit:** `38cdcdf`
- **Branch:** `main`

## 2. Inventaris Modul Feedback
### A. Routing (`routes/owner.php`)
Modul ini didaftarkan di dalam grup prefix `/feedback` dengan proteksi middleware awal `require.role:owner`:
- `GET /feedback/bugs` (`feedback.bugs.index`) -> `FeedbackWebController@indexBugs`
- `GET /feedback/bugs/create` (`feedback.bugs.create`) -> `FeedbackWebController@createBug`
- `POST /feedback/bugs` (`feedback.bugs.store`) -> `FeedbackWebController@storeBug`
- `GET /feedback/bugs/{bugReport}` (`feedback.bugs.show`) -> `FeedbackWebController@showBug`
- `GET /feedback/features` (`feedback.features.index`) -> `FeedbackWebController@indexFeatures`
- `GET /feedback/features/create` (`feedback.features.create`) -> `FeedbackWebController@createFeature`
- `POST /feedback/features` (`feedback.features.store`) -> `FeedbackWebController@storeFeature`
- `GET /feedback/features/{featureRequest}` (`feedback.features.show`) -> `FeedbackWebController@showFeature`

### B. Controller
- `app/Http/Controllers/Web/FeedbackWebController.php`:
  - Mengelola logika presentasi, validasi masukan formulir tiket, verifikasi kuota berkas, dan relasi multi-tenant.

### C. Model & Migrasi
1. **`App\Models\BugReport`**:
   - Primary key: UUID (`HasUuid`).
   - Tenant Scoping: `BelongsToBusiness`.
   - Kolom: `id`, `business_id`, `user_id`, `title`, `description`, `severity`, `status`, `reproduce_steps`, `system_info`, `created_at`, `updated_at`.
   - Relasi: `business()`, `user()`, `attachments()`, `updates()`.
2. **`App\Models\FeatureRequest`**:
   - Primary key: UUID (`HasUuid`).
   - Tenant Scoping: `BelongsToBusiness`.
   - Kolom: `id`, `business_id`, `user_id`, `title`, `description`, `use_case`, `status`, `created_at`, `updated_at`.
   - Relasi: `business()`, `user()`, `attachments()`, `updates()`.
3. **`App\Models\FeedbackUpdate`**:
   - Model linimasa status: mencatat riwayat perubahan status dari tim COOCA beserta catatan penjelasan.

### D. Tampilan Antarmuka (Blade Views)
- `resources/views/app/feedback/index.blade.php`: Tampilan tab ganda untuk melihat daftar Laporan Bug dan Usulan Fitur.
- `resources/views/app/feedback/create.blade.php`: Formulir pembuatan tiket baru (dinamis antara Bug Report dan Feature Request).
- `resources/views/app/feedback/show.blade.php`: Detail tiket, lampiran berkas, dan linimasa status riwayat update.

## 3. Baseline Test Suite
- `tests/Feature/FeedbackWorkflowTest.php`:
  - `test_owner_can_submit_and_view_bug_report`: Verifikasi alur pelaporan bug oleh owner.
  - `test_owner_can_submit_and_view_feature_request`: Verifikasi alur pengajuan ide fitur oleh owner.
  - `test_feedback_enforces_storage_quota`: Verifikasi proteksi batas kuota storage tenant saat upload lampiran.
  - `test_feedback_status_history_display`: Verifikasi linimasa riwayat status tiket ditampilkan dengan benar.
  - Status Baseline: 4 passed, 41 assertions.
