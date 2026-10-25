# System Baseline - approvals

> Modul: approvals | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Lingkungan Eksekusi
- **Framework:** Laravel 11.x (PHP 8.3+)
- **Database:** SQLite in-memory (Testing) / PostgreSQL/MySQL (Production)
- **Multi-Tenant Context:** `App\Support\Context` via header/session `active_business_id`
- **UI Architecture:** Blade Templates + Tailwind CSS + Alpine.js + Lucide Icons (Apple HIG Bento Grid)

## Komponen yang Diaudit
1. **Controllers:**
   - `app/Http/Controllers/Web/Approval/ApprovalWebController.php`
2. **Domain Services:**
   - `app/Domain/Approval/ApprovalWorkflowService.php`
3. **Models:**
   - `app/Models/ApprovalRequest.php`
   - `app/Models/ApprovalRule.php`
   - `app/Models/ApprovalLog.php`
4. **Views:**
   - `resources/views/app/approvals/inbox.blade.php` (Pusat otorisasi tiket pending)
   - `resources/views/app/approvals/history.blade.php` (Arsip log audit riwayat selesai)
   - `resources/views/app/approvals/rules.blade.php` (Konfigurasi plafon dan hak peran)
5. **Routes:**
   - `routes/owner.php` (Baris 258-276)
     - `approvals.inbox` (`GET /approvals`)
     - `approvals.history` (`GET /approvals/history`)
     - `approvals.approve` (`POST /approvals/{approvalRequest}/approve`)
     - `approvals.reject` (`POST /approvals/{approvalRequest}/reject`)
     - `approval-rules.index` (`GET /settings/approval-rules`)
     - `approval-rules.store` (`POST /settings/approval-rules`)
     - `approval-rules.update` (`PUT /settings/approval-rules/{approvalRule}`)
     - `approval-rules.destroy` (`DELETE /settings/approval-rules/{approvalRule}`)
     - Alias routes: `settings.approval-rules.*` (`/settings/rules/approvals`)
6. **I18n Localization:**
   - `lang/id/approvals.php` (Indonesian locale)
   - `lang/en/approvals.php` (English locale)
7. **Test Suites:**
   - `tests/Feature/DocumentApprovalWorkflowTest.php`
   - `tests/Feature/ApprovalModuleAuditTest.php`

## Baseline Kondisi Sebelum Audit
- Pada awal audit, eksekusi `php artisan test tests/Feature/DocumentApprovalWorkflowTest.php` mengalami kegagalan pada test `test_umkm_scale_bypasses_mandatory_multi_level_approval()` akibat *Integrity constraint violation: 19 NOT NULL constraint failed: audit_logs.auditable_type* di file `app/Http/Controllers/Web/PurchaseOrderWebController.php` baris 267-277.
- Setelah diperbaiki dengan menyelaraskan kolom `auditable_type` dan `auditable_id`, seluruh suite baseline berhasil berjalan 100% hijau.
