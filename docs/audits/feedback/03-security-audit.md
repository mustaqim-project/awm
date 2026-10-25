# Security Audit - feedback

> Modul: Platform Feedback & Bug Support | Commit: 38cdcdf | Tanggal: 2026-10-25

## 1. Analisis Otentikasi & Otorisasi (RBAC)
- **Role Isolation:**
  - Modul Feedback ditujukan khusus untuk pengguna dengan peran pemilik bisnis (`owner`).
  - **Temuan Awal:** Sebelumnya proteksi `require.role:owner` hanya dideklarasikan di level rute `routes/owner.php`.
  - **Perbaikan:** Menerapkan Controller-Level RBAC melalui interface `HasMiddleware` pada `FeedbackWebController`. Hal ini mencegah bypass jika rute dipindahkan atau dipanggil secara terpisah.
  - **Hasil Pengujian:** Pengguna dengan peran non-owner (misal: kasir/karyawan) secara instan diblokir dengan kode HTTP 403 Forbidden.

## 2. Isolasi Multi-Tenant & Perlindungan IDOR
- **Tenant Scope Enforcement:**
  - Model `BugReport` dan `FeatureRequest` menggunakan `BelongsToBusiness` (`BusinessScope`), yang secara otomatis menyaring seluruh query berdasarkan `business_id` tenant aktif.
  - Controller menambahkan pertahanan berlapis (*defense-in-depth*):
    ```php
    abort_unless($bugReport->business_id === $business->id, 403);
    abort_unless($featureRequest->business_id === $business->id, 403);
    ```
  - **Hasil Pengujian:** Upaya akses lintas tenant terhadap ID tiket milik bisnis lain menghasilkan 404 (ModelNotFoundException dari global scope) atau 403, menjamin tidak adanya kebocoran data.

## 3. Keamanan Unggahan Berkas (File Upload Security)
- **Cek Ekstensi & MIME Type:**
  - Masukan `attachments.*` divalidasi dengan aturan:
    ```php
    'attachments.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
    ```
  - Sistem secara aktif menolak ekstensi berbahaya seperti `.php`, `.phtml`, `.exe`, `.sh`, `.bat`, dan script sisi server lainnya.
  - Batasan ukuran maksimal 5120 KB (5 MB) per berkas mencegah serangan *Denial of Service* berbasis pengisian memori atau disk.

## 4. Perlindungan Kuota Penyimpanan (Storage Quota Protection)
- Sebelum berkas disimpan secara fisik ke disk penyimpanan, sistem melakukan pengecekan proaktif:
  ```php
  $this->quotaService->assertCanUploadBytes($business, $totalBytes);
  ```
- Jika tenant telah melebihi kuota berlangganan mereka, pengunggahan berkas dibatalkan secara aman dan memunculkan peringatan validasi yang informatif bagi pemilik toko.

## 5. Kepatuhan Audit Trail (Audit Logging)
- Setiap pengiriman laporan bug atau permintaan fitur kini mencatat log permanen ke tabel `audit_logs`:
  - Aksi: `feedback.bug_reported` dan `feedback.feature_requested`.
  - Record ID: UUID dari laporan bug atau permintaan fitur terkait.
  - Metadata: Informasi judul dan tingkat severity.
