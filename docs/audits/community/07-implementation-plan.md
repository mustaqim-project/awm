# Implementation Plan - community

> Modul: Community & Networking UMKM | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Strategi & Urutan
1. **Fase 1: Investigasi Baseline & Pemulihan Fixture Test:**
   - Menjalankan 4 test suite baseline (`CommunityEventFeatureTest`, `CommunityEventPaymentTest`, `CommunityFeatureTest`, `CommunityWordFilterTest`).
   - Memperbaiki kegagalan fixture admin pada `EnsureAdminIsActive` middleware dengan memberikan atribut `role => super_admin` dan `is_active => true`.
2. **Fase 2: Remediasi Keamanan & Finansial:**
   - Menutup celah financial bypass pada `CommunityWebController::simulateEventPayment` dengan `abort_unless(app()->environment('local', 'testing'), 403)`.
   - Mengondisikan tombol simulasi pembayaran di Blade `event_payment.blade.php` agar hanya tampil pada environment local/testing.
3. **Fase 3: Penguatan Jejak Audit & RBAC:**
   - Menambahkan kontrak `implements HasMiddleware` pada `CommunityWebController`.
   - Menambahkan `AuditLog::create` pada registrasi event, pembatalan tiket, simulasi bayar, dan penghapusan postingan (`destroy`).
4. **Fase 4: Pembuatan Dedicated Audit Test Suite:**
   - Mengembangkan `tests/Feature/CommunityModuleAuditTest.php` untuk memvalidasi otorisasi guest, IDOR, audit logging, environment guard, dan moderasi kata.
5. **Fase 5: Verifikasi Menyeluruh & Dokumentasi:**
   - Menjalankan seluruh 48 tes otomatis hingga tercapai 100% Pass.
   - Menyusun 13 dokumen audit, dokumen user guide, dan verifikasi tautan internal.

## Dependensi Antartask
- Perbaikan fixture admin menjadi prasyarat eksekusi sukses test suite baseline.
- Penambahan guard environment pada controller menjadi prasyarat lulusnya `CommunityModuleAuditTest`.

## Risiko Perubahan
- Risiko regresi pada alur pendaftaran event: Terbukti nihil, 48 tes otomatis lulus 100%.

## Rollback Tingkat Rilis
- Seluruh perubahan bersifat backward compatible (tidak ada modifikasi destruktif pada struktur skema database).
- Rollback dapat dilakukan secara instan via `git revert` tanpa downtime.

## Test Plan & Regression Scope
- Cakupan regresi mencakup 5 test suite:
  - `CommunityEventFeatureTest.php`
  - `CommunityEventPaymentTest.php`
  - `CommunityFeatureTest.php`
  - `CommunityWordFilterTest.php`
  - `CommunityModuleAuditTest.php`
- Total 48 skenario teruji dengan 224 asersi.

## Menunggu Keputusan (Blocked)
- Status: None (Semua task terselesaikan secara mandiri).
