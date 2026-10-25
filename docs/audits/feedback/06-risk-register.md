# Risk Register - feedback

> Modul: Platform Feedback & Bug Support | Commit: 38cdcdf | Tanggal: 2026-10-25

## Matriks Evaluasi Risiko

| ID | Kategori | Deskripsi Risiko | Severity | Kemungkinan | Dampak | Status Mitigasi |
|---|---|---|---|---|---|---|
| **RSK-FB-01** | Keamanan | Eksekusi script berbahaya melalui unggahan lampiran tiket (.php/.exe) | High | Rendah | Tinggi | **Terselesaikan:** Validasi ketat `mimes:jpg,jpeg,png,pdf` dan limit 5MB. |
| **RSK-FB-02** | Otorisasi | Staf non-owner mengakses tiket laporan bug internal usaha | Medium | Sedang | Sedang | **Terselesaikan:** Controller-Level RBAC `require.role:owner` via `HasMiddleware`. |
| **RSK-FB-03** | Kepatuhan | Ketiadaan jejak audit atas pengiriman tiket masalah | Medium | Sedang | Rendah | **Terselesaikan:** Integrasi `AuditLog::create` otomatis pada pembuatan tiket. |
| **RSK-FB-04** | Infrastruktur | Kehabisan kuota penyimpanan akibat unggahan berkas gambar masif | Medium | Sedang | Sedang | **Terselesaikan:** Proteksi `OwnerStorageQuotaService::assertCanUploadBytes`. |
| **RSK-FB-05** | UI/UX | Kesulitan menekan tombol aksi pada layar smartphone/tablet | Low | Sedang | Rendah | **Terselesaikan:** Penyesuaian Apple HIG touch target minimal 44px (`h-11`). |
| **RSK-FB-06** | Data Isolation | Akses lintas tenant terhadap tiket bug atau fitur bisnis lain (IDOR) | Critical | Sangat Rendah | Sangat Tinggi | **Terselesaikan:** Isolasi `BusinessScope` dan pengecekan ganda `business_id`. |
