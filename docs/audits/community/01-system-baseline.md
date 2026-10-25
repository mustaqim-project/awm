# System Baseline - community

> Modul: Community & Networking UMKM | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Versi & Dependensi
- **Framework:** Laravel 11.x (PHP 8.2+)
- **Database:** PostgreSQL (Production) / SQLite in-memory (Automated Testing)
- **Frontend Engine:** Laravel Blade, Alpine.js (x-data, x-teleport), Tailwind CSS
- **Library & Domain Service Pendukung:**
  - `Bacon/BaconQrCode`: Generator SVG QR Code untuk tiket digital & transaksi QRIS
  - `Picqer/php-barcode-generator`: Generator SVG Barcode (Code128) untuk pemindaian tiket
  - `App\Domain\Billing\EntitlementService`: Gate hak akses partisipasi komunitas berbasis paket langganan
  - `App\Domain\Community\ContentModerationService`: Mesin moderasi konten berbasis blacklist kata terlarang
  - `App\Domain\Community\EventCheckinService`: Mesin kalkulasi jarak geografis (Haversine Formula) untuk presensi GPS
  - `App\Domain\Community\EventTicketService`: Layanan rendering tiket digital (QR Code & Barcode)
  - `App\Domain\Community\TrendingTopicService`: Ekstraksi hashtag terpopuler dari postingan aktif
  - `App\Domain\Payment\TripayService`: Integrasi payment gateway QRIS closed-loop
  - `App\Domain\Storage\OwnerStorageQuotaService`: Pengawas kuota upload gambar komunitas

## Commit dan Status Git yang Diaudit
- **Commit Baseline:** `38cdcdf` (branch `main`)
- **Status Repository:** Clean working tree pada commit awal, dilakukan surgical hardening pada controller, view, dan test suite.
- **Modified Files selama Audit:**
  - `app/Http/Controllers/Web/CommunityWebController.php` (HasMiddleware, AuditLog, safe simulation guard, destroy audit)
  - `resources/views/app/community/event_payment.blade.php` (Environment-restricted simulation button)
  - `resources/views/app/community/events.blade.php` (Enhanced cancellation confirmation alert)
  - `tests/Feature/CommunityEventFeatureTest.php` (Admin active fixture fix)
  - `tests/Feature/CommunityWordFilterTest.php` (Admin active fixture fix)
  - `tests/Feature/CommunityModuleAuditTest.php` (New dedicated audit feature test)

## Inventaris Modul
- **Controllers:**
  - `App\Http\Controllers\Web\CommunityWebController` (Owner Web App)
  - `App\Http\Controllers\Admin\AdminCommunityEventController` (Backoffice Admin)
  - `App\Http\Controllers\Admin\AdminWordFilterController` (Backoffice Moderasi Kata)
- **Models:**
  - `CommunityPost` (`owner_id`, `business_id`, `content`, `image_path`)
  - `CommunityComment` (`post_id`, `user_id`, `content`)
  - `CommunityLike` (`post_id`, `user_id`)
  - `CommunityRepost` (`post_id`, `user_id`, `business_id`)
  - `CommunityNotification` (`user_id`, `actor_id`, `post_id`, `type`, `message`, `is_read`)
  - `CommunityEvent` (`title`, `slug`, `description`, `event_type`, `price_type`, `price`, `location_name`, `start_time`, `end_time`, `quota`, `status`)
  - `CommunityEventRegistration` (`event_id`, `user_id`, `business_id`, `ticket_code`, `status`, `payment_status`, `payment_gateway`, `amount_paid`)
  - `CommunityWordFilter` (`word`, `is_regex`, `created_by_admin_id`)
- **Blade Views (`resources/views/app/community`):**
  - `index.blade.php`: Feed diskusi komunitas, tab Filter (Semua, Tren, Mengikuti, Diskusi Saya), trending topics, storage quota widget, form posting, interaksi Like/Komentar/Repost.
  - `events.blade.php`: Direktori event mendatang, tab Tiket Saya, modal tiket digital SVG, check-in radius GPS, form batal registrasi.
  - `event_payment.blade.php`: Halaman checkout QRIS event berbayar, detail biaya, QR code dinamis, tombol refresh status.

## Peta Dependensi Lintas Modul
1. **Modul Billing & Subscription (`App\Domain\Billing`):**
   - Menentukan apakah bisnis memiliki hak partisipasi komunitas (`canParticipateInCommunity`). Pengguna paket free dibatasi hanya membaca feed, sedangkan paket berbayar dapat posting/komentar/repost.
2. **Modul Storage & Tenant Quota (`App\Domain\Storage`):**
   - Setiap upload gambar pada postingan (`max: 5MB`) diverifikasi terhadap kuota storage bisnis (`assertCanUpload`) dan dicatat via `StorageTrackingService`.
3. **Modul Payment Gateway (`App\Domain\Payment\TripayService`):**
   - Menginisiasi transaksi QRIS saat pendaftaran event berbayar, menerima webhook callback, dan mengupdate status pelunasan tiket.
4. **Modul Audit Log (`App\Models\AuditLog`):**
   - Merekam setiap event registrasi, pembatalan pendaftaran, simulasi pembayaran, dan penghapusan postingan.

## Aktor & Permission
- **Tamu / Guest:** Dialihkan langsung ke route `login`.
- **User / Owner Bisnis (Paket Free):**
  - Dapat mengakses feed komunitas (`community.index`) untuk membaca postingan.
  - Dapat melihat direktori event (`community.events.index`), mendaftar event gratis/berbayar, dan check-in kehadiran.
  - Dilarang membuat postingan, komentar, atau repost sebelum melakukan upgrade paket berlangganan.
- **User / Owner Bisnis (Paket Berbayar - Core / Standard / Premium / Prestige):**
  - Akses penuh ke seluruh fitur feed komunitas, interaksi diskusi, dan event.
- **Super Admin Cooca:**
  - Mengelola publikasi event di portal admin (`AdminCommunityEventController`).
  - Mengelola daftar kata terlarang dan blacklist sensor konten (`AdminWordFilterController`).

## Business Rules (BR-COMMUNITY)
- **BR-COMM-01:** Konten postingan (maks 5.000 karakter) dan komentar (maks 1.000 karakter) wajib lolos validasi `ContentModerationService`. Konten yang mengandung kata terlarang dibatalkan dengan pesan error 422.
- **BR-COMM-02:** Upload gambar pada postingan dibatasi maksimal 5 MB dan tipe berkas jpeg, png, webp, gif serta memotong kuota tenant.
- **BR-COMM-03:** Registrasi event hanya dapat dilakukan jika status event adalah `published`, waktu mulai belum lewat, batas registrasi belum kedaluwarsa, dan kuota peserta masih tersedia.
- **BR-COMM-04:** Event berbayar menghasilkan status tiket `registered` dengan `payment_status = pending`. Tiket digital baru dapat dibuka atau digunakan untuk check-in setelah pembayaran lunas (`payment_status = paid`).
- **BR-COMM-05:** Presensi via GPS (`checkin-location`) menghitung jarak koordinat pengguna dengan venue acara menggunakan rumus Haversine. Check-in berhasil jika jarak <= `checkin_radius_meters` (default 200m).
- **BR-COMM-06:** Penghapusan postingan (`destroy`) hanya diizinkan bagi pemilik postingan itu sendiri (`post.owner_id === user.id`).
- **BR-COMM-07:** Simulasi pembayaran QRIS (`simulate-pay`) dibatasi mutlak hanya pada lingkungan non-produksi (`local`, `testing`).

## Implementasi vs Dokumentasi vs Ekspektasi
- **Kesesuaian Desain:** Tampilan telah menerapkan panduan Apple Human Interface Guidelines (Bento grid, warna aksen iOS `#007AFF`, modal backdrop-blur dengan teleportasi `x-teleport="body"`, z-[200]).
- **Mobile First & Touch Targets:** Semua tombol interaksi utama dan kontrol tab memiliki tinggi minimal 44px dan aman disentuh di layar smartphone.

## Batas Lingkungan (Dev/Staging/Production)
- Endpoint simulasi pembayaran (`community.events.payment.simulate`) secara otomatis melempar `403 Forbidden` pada lingkungan production.
- Pada production, integrasi pembayaran QRIS sepenuhnya bergantung pada webhook resmi Tripay dengan verifikasi signature HMAC SHA256.

## Tidak Dapat Diverifikasi
- Pengujian fisik perangkat keras GPS riil (disimulasikan secara akurat melalui unit & feature test dengan koordinat lintang/bujur valid).
