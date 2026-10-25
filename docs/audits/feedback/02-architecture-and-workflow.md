# Architecture and Workflow - feedback

> Modul: Platform Feedback & Bug Support | Commit: 38cdcdf | Tanggal: 2026-10-25

## 1. Arsitektur Komponen
Modul Feedback mengintegrasikan arsitektur Laravel Controller, Eloquent Models, Storage Management, dan Audit Trail:

```
[Owner Browser]
      │
      ▼
[FeedbackWebController] ── (HasMiddleware: require.role:owner)
      │
      ├── [StorageTrackingService & OwnerStorageQuotaService] ── (Validasi Kuota Disk)
      │
      ├── [BugReport / FeatureRequest Models] ── (BelongsToBusiness Scope)
      │
      ├── [FeedbackUpdate Model] ── (Histori Linimasa Status)
      │
      └── [AuditLog::create] ── (feedback.bug_reported / feedback.feature_requested)
```

## 2. Alur Pelaporan Bug (Bug Report Workflow)
1. **Inisiasi:** Owner mengakses `/feedback/bugs/create`.
2. **Validasi Formulir:**
   - `title`: Wajib diisi, string maksimal 255 karakter.
   - `severity`: Wajib dipilih (`low`, `medium`, `high`, `critical`).
   - `description`: Wajib diisi (uraian kendala).
   - `reproduce_steps`: Opsional (langkah mereproduksi masalah).
   - `attachments.*`: Opsional, berkas gambar atau PDF (`mimes:jpg,jpeg,png,pdf`), maksimal 5120 KB (5 MB).
3. **Verifikasi Kuota Penyimpanan:**
   - Sistem menjumlahkan total byte lampiran yang diunggah.
   - `OwnerStorageQuotaService::assertCanUploadBytes($business, $totalBytes)` mengevaluasi sisa kuota tenant.
   - Jika kuota tidak mencukupi, operasi dibatalkan dengan pesan galat ramah pengguna.
4. **Penyimpanan Data & Berkas:**
   - Data laporan disimpan ke tabel `bug_reports` dengan `business_id` dan `user_id`.
   - Lampiran disimpan ke disk aman dan dicatat di tabel `storage_files` melalui `StorageTrackingService`.
5. **Pencatatan Audit Log:**
   - Mencatat aksi `feedback.bug_reported` ke tabel `audit_logs` dengan detail `report_id` dan `severity`.
6. **Redireksi & Notifikasi:**
   - Pengguna diarahkan ke halaman detail `/feedback/bugs/{id}` dengan pesan sukses.

## 3. Alur Pengajuan Ide Fitur (Feature Request Workflow)
1. **Inisiasi:** Owner mengakses `/feedback/features/create`.
2. **Validasi Formulir:**
   - `title`: Wajib diisi, string maksimal 255 karakter.
   - `description`: Wajib diisi (latar belakang kebutuhan).
   - `use_case`: Opsional (skenario penggunaan pada bisnis).
3. **Penyimpanan & Audit:**
   - Data disimpan ke tabel `feature_requests`.
   - Mencatat aksi `feedback.feature_requested` ke tabel `audit_logs`.
4. **Redireksi:** Pengguna diarahkan ke `/feedback/features/{id}`.

## 4. Alur Linimasa Status (Status Update Lifecycle)
Setiap tiket dapat mengalami transisi status:
- `open` -> Laporan baru diterima.
- `under_review` -> Laporan sedang dianalisis oleh Product Team.
- `in_progress` -> Perbaikan atau fitur sedang dikerjakan oleh tim insinyur.
- `resolved` -> Kendala telah diperbaiki atau fitur telah dirilis ke sistem.
- `closed` -> Tiket ditutup.

Setiap transisi status dicatat di `feedback_updates` dan ditampilkan dalam bentuk linimasa visual vertikal di halaman `show.blade.php`.
