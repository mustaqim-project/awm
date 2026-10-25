# Risk Register - community

> Modul: Community & Networking UMKM | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Tabel Risiko

| ID Risiko | Kategori | Deskripsi Risiko | Severity | Probabilitas | Mitigasi yang Diterapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **RSK-COMM-01** | Keuangan | Akses simulasi pembayaran QRIS di production membocorkan tiket berbayar gratis | Critical | Sedang | Penambahan `abort_unless(app()->environment('local', 'testing'), 403)` pada controller dan penyembunyian form di Blade | **Closed** |
| **RSK-COMM-02** | Keamanan | Percobaan manipulasi atau penghapusan postingan milik pengguna lain (IDOR) | High | Rendah | Validasi otorisasi `post.owner_id === user.id` dengan abort 403, dibuktikan via feature test | **Closed** |
| **RSK-COMM-03** | Reputasi | Penyebaran tautan spam, penipuan, pinjol, atau promosi judi online di postingan & komentar | High | Sedang | Pemindaian otomatis melalui `ContentModerationService` berbasis tabel blacklist kata | **Closed** |
| **RSK-COMM-04** | Operasional | Akses postingan tanpa batas dari akun bot / free tier yang menghabiskan kapasitas disk | Medium | Sedang | Integrasi `EntitlementService` (wajib langganan berbayar) dan pemotongan kuota storage tenant | **Closed** |
| **RSK-COMM-05** | Integritas | Pemalsuan presensi kehadiran peserta event menggunakan manipulasi GPS | Medium | Sedang | Kombinasi verifikasi radius Haversine formula + opsi cadangan barcode scan fisik di lokasi | **Closed** |
| **RSK-COMM-06** | Audit Trail | Ketiadaan jejak audit saat postingan dihapus oleh pengguna | Medium | Rendah | Pencatatan otomatis ke `audit_logs` sebelum eksekusi `$post->delete()` | **Closed** |
| **RSK-COMM-07** | UX / CS | Keluhan pengguna atas pembatalan pendaftaran tiket berbayar tanpa pengembalian dana | Low | Sedang | Penyempurnaan microcopy konfirmasi pembatalan pendaftaran tiket berbayar | **Closed** |

## Risiko Diterima (Acceptance Formal)
- **RSK-COMM-ACC-01 (Ketergantungan Sinyal GPS Ponsel):**
  - Akurasi Geolocation GPS di dalam gedung bertingkat atau basement terkadang melebihi toleransi 200 meter.
  - *Acceptance:* Diterima dengan catatan panitia acara wajib menyediakan fasilitas pemindaian barcode manual (`community.events.checkin-barcode`) menggunakan kamera atau scanner panitia.

## Analisis Root Cause
- Celah keamanan awal pada fitur simulasi pembayaran terjadi karena fungsi prototipe sandbox belum dipisahkan secara ketat dengan aturan environment produksi sebelum tahap audit komprehensif.
