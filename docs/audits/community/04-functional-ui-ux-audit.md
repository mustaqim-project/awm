# Functional, UI/UX & Human Error Audit - community

> Modul: Community & Networking UMKM | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Matriks Fitur x Skenario

| Fitur | Skenario Normal | Skenario Edge Case | Hasil Verifikasi |
| :--- | :--- | :--- | :--- |
| **Feed Komunitas** | Menampilkan postingan urut waktu terbaru | Tidak ada postingan (Empty state) | Menampilkan ilustrasi & panduan membuat postingan pertama |
| **Pencarian & Topik** | Mencari kata kunci / klik hashtag | Keyword tidak ditemukan | Menampilkan pesan ramah & tombol reset filter |
| **Posting Komunitas** | Teks + gambar valid dikirim | Upload file > 5 MB atau format non-gambar | Ditolak validasi client & server |
| **Moderasi Kata** | Postingan berisi percakapan bisnis biasa | Postingan disusupi kata promosi judi / pinjol | Diblokir otomatis dengan notifikasi error ramah |
| **Direktori Event** | Menampilkan daftar event mendatang | Kuota event habis / batas registrasi lewat | Tombol daftar otomatis terkunci (disabled) |
| **Pembayaran QRIS** | Membuka tagihan & bayar lewat m-banking | Menunggu pembayaran hingga 15 menit | Polling status otomatis, opsi refresh status manual |
| **Tiket Digital** | Membuka modal tiket QR + Barcode | Event berbayar belum lunas | Ditolak dengan arahan melanjutkan pembayaran QRIS |
| **Presensi GPS** | Klik check-in dalam radius 200m venue | Peserta berada 1 km di luar venue | Ditolak dengan info jarak riil & toleransi maksimal |

## Temuan Fungsional
1. **Validasi Kuota Peserta:** Berfungsi dengan presisi. Event yang telah memenuhi kuota tidak dapat didaftar kembali.
2. **Re-aktivasi Pendaftaran:** Jika peserta sebelumnya membatalkan pendaftaran lalu ingin mendaftar kembali selagi kuota masih ada, sistem mengaktifkan kembali tiket tanpa membuat duplikasi baris registrasi.
3. **Storage Tracking Integrasi:** Kuota upload gambar terpotong otomatis dari batas penyimpanan bisnis.

## Temuan UI/UX
1. **Bento Apple HIG Alignment:**
   - Menggunakan card rounded-3xl (`rounded-3xl border border-slate-200/80 dark:border-white/10`).
   - Warna aksen konsisten dengan iOS System Blue (`#007AFF`).
   - Modal tiket digital dan pop-up interaksi di-teleport ke `<body>` (`<template x-teleport="body">`) dengan z-index `z-[200]` untuk mencegah terpotong oleh layout pembungkus.
2. **Touch Targets & Thumb Zone:**
   - Tombol tab navigasi, tombol check-in, dan tombol checkout memiliki tinggi minimal 44px (`py-3` / `min-h-[44px]`).
   - Tombol Batal pada pendaftaran diberi jarak yang aman agar tidak tertekan secara tidak sengaja.
3. **Anti-Slop Standard:**
   - Tidak menggunakan emoji generik bertumpuk.
   - Menggunakan ikon vector modern Lucide (`scan-barcode`, `qr-code`, `map-pin`, `calendar`, `message-square`).

## Human Error Prevention
- **Konfirmasi Pembatalan Kontekstual:** Pada tombol pembatalan registrasi event berbayar yang telah lunas, dialog konfirmasi memberikan informasi jelas bahwa tiket tidak otomatis di-refund.
- **Pencegahan Double-Click:** Tombol pendaftaran dan submit komentar menonaktifkan diri (`:disabled="submitting"`) saat proses pengiriman berlangsung.

## Matriks BR -> Test

| Business Rule | Skenario Uji | Test Case | Status |
| :--- | :--- | :--- | :--- |
| **BR-COMM-01** | Blokir kata terlarang / judol | `CommunityModuleAuditTest::test_content_moderation_blocks_*` | PASSED |
| **BR-COMM-02** | Batas ukuran gambar & format | `CommunityFeatureTest::test_owner_can_create_post_with_image` | PASSED |
| **BR-COMM-03** | Pembatasan kuota & deadline | `CommunityEventFeatureTest::test_cannot_register_when_quota_full` | PASSED |
| **BR-COMM-04** | Tiket berbayar pending payment | `CommunityEventPaymentTest::test_qris_payment_page_renders_*` | PASSED |
| **BR-COMM-05** | Validasi radius GPS Haversine | `CommunityEventFeatureTest::test_checkin_location_within_radius` | PASSED |
| **BR-COMM-06** | Kepemilikan postingan (IDOR) | `CommunityModuleAuditTest::test_idor_protection_user_cannot_*` | PASSED |
| **BR-COMM-07** | Simulasi bayar non-produksi | `CommunityModuleAuditTest::test_simulate_payment_forbidden_*` | PASSED |
