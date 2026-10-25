# Security Audit - community

> Modul: Community & Networking UMKM | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Tabel Endpoint x Kontrol Keamanan

| Endpoint | Method | Auth Guard | IDOR Check | CSRF | AuditLog | Rate Limit |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `/community` | GET | `auth` | Scoped Feed | N/A | - | Standard |
| `/community` | POST | `auth` | `Context::requireBusiness()` | Ya | - | 10 req/min |
| `/community/{post}` | DELETE | `auth` | `post.owner_id === user.id` | Ya | `community.post.deleted` | Standard |
| `/community/{post}/like` | POST | `auth` | - | Ya | - | 60 req/min |
| `/community/{post}/comment` | POST | `auth` | Subscribed Check | Ya | - | 20 req/min |
| `/community/{post}/repost` | POST | `auth` | Subscribed Check | Ya | - | 20 req/min |
| `/community/events` | GET | `auth` | Scoped Registrations | N/A | - | Standard |
| `/community/events/{event}/register` | POST | `auth` | Quota & IsOpen | Ya | `community.event.registered` | 10 req/min |
| `/community/events/{event}/cancel` | POST | `auth` | Own Registration | Ya | `community.event.registration_cancelled` | Standard |
| `/community/events/{event}/ticket` | GET | `auth` | Own Registration | N/A | - | Standard |
| `/community/events/{event}/checkin-location` | POST | `auth` | Paid Check & Distance | Ya | - | 15 req/min |
| `/community/events/{event}/checkin-barcode` | POST | `auth` | Paid Check & Ticket Match | Ya | - | 30 req/min |
| `/community/events/registrations/{reg}/payment` | GET | `auth` | `reg.user_id === user.id` | N/A | - | Standard |
| `/community/events/registrations/{reg}/simulate-pay` | POST | `auth` | `reg.user_id === user.id` & Env Guard | Ya | `community.event.payment_simulated` | Local/Test Only |

## Hasil Test Otorisasi
- **Guest Access:** Terbukti 100% diblokir dan dialihkan ke `/login` (`test_guest_is_redirected_to_login_on_community_endpoints`).
- **Pencegahan IDOR:**
  - Pengguna lain dilarang menghapus postingan milik pengguna berbeda (`test_idor_protection_user_cannot_delete_another_users_post` -> 403 Forbidden).
  - Pengguna lain dilarang membuka atau membayar tagihan pendaftaran milik pengguna berbeda (`test_idor_protection_user_cannot_view_or_simulate_payment_of_another_users_registration` -> 403 Forbidden).
- **Environment Protection:**
  - Simulasi pembayaran pada environment production diblokir mutlak (`test_simulate_payment_forbidden_in_production_environment` -> 403 Forbidden).

## Hasil Composer Audit
- Status audit composer: Dependensi aplikasi terverifikasi aman.
- `Bacon/BaconQrCode` dan `Picqer/php-barcode-generator` beroperasi offline murni tanpa external network request, menghilangkan risiko SSRF atau kebocoran data.

## Temuan Keamanan
1. **Financial Bypass (Terselesaikan):**
   - *Deskripsi:* `simulateEventPayment` sebelumnya tidak memeriksa environment aplikasi.
   - *Perbaikan:* Menambahkan `abort_unless(app()->environment('local', 'testing'), 403);` di baris pertama controller method.
2. **Missing Post Deletion Audit (Terselesaikan):**
   - *Deskripsi:* Penghapusan postingan belum terekam di tabel `audit_logs`.
   - *Perbaikan:* Menambahkan `AuditLog::create` dengan level risiko Medium sebelum penghapusan record.

## Peta OWASP Relevan
- **A01:2021 - Broken Access Control:** Tertangani. Validasi kepemilikan postingan (`owner_id`) dan registrasi event (`user_id`) diterapkan ketat pada setiap controller method.
- **A03:2021 - Injection & XSS:** Tertangani. Teks postingan dan komentar diescape via Blade `{{ $content }}` dan divalidasi via `ContentModerationService`.
- **A04:2021 - Insecure Design:** Tertangani. Akses simulasi pembayaran QRIS diproteksi di level kode aplikasi dan UI Blade.
- **A08:2021 - Software and Data Integrity Failures:** Tertangani. Webhook Tripay diverifikasi via HMAC SHA256 signature sebelum mengubah status pembayaran tiket.
