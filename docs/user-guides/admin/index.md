# Panduan Pengguna - Platform Backoffice & Superadmin (`admin`)

> Modul: `admin` | Commit: `a17c5ae` | Tanggal Verifikasi: 2026-10-26 | Status Dokumen: **Resmi (Verified)**

Selamat datang di Panduan Resmi Konsol Administrator COOCA. Dokumen ini dirancang sebagai pegangan praktis bagi staf pengelola platform, pimpinan operasional, dan tim technical support untuk mengoperasikan fitur-fitur backoffice secara optimal, aman, dan efisien.

---

## 1. Peta Navigasi Modul & Rute Resmi Terverifikasi

Seluruh tautan fitur di bawah ini telah diverifikasi langsung terhadap rute sistem (`routes/admin.php`):

| Menu Navigasi | Rute URL | Rute Name | Hak Akses Utama | Fungsi & Kegunaan |
| :--- | :--- | :--- | :---: | :--- |
| **🏠 Dashboard** | [`/admin/dashboard`](https://cooca.id/admin/dashboard) | `admin.dashboard` | `admin.dashboard.view` | Pantau metrik omzet, jumlah tenant aktif, dan tiket pending. |
| **🏢 Kelola Bisnis (Tenant)**| [`/admin/businesses`](https://cooca.id/admin/businesses) | `admin.businesses.index` | `admin.businesses.view` | Kelola tenant UMKM, aktivasi/suspend, dan masuk investigasi (*impersonate*). |
| **👥 Daftar Pengguna** | [`/admin/users`](https://cooca.id/admin/users) | `admin.users.index` | `admin.users.view` | Cari data user terdaftar, reset kata sandi, dan ekspor data CSV. |
| **🪪 Pemulihan Akun** | [`/admin/account-recoveries`](https://cooca.id/admin/account-recoveries) | `admin.account-recoveries.index` | `admin.account_recoveries.view` | Verifikasi dokumen KTP & NIB pemilik bisnis yang hilang akses nomor/email. |
| **💳 Rekening Pembayaran** | [`/admin/payment-accounts`](https://cooca.id/admin/payment-accounts) | `admin.payment_accounts.index` | `admin.payment_accounts.view` | Atur nomor rekening bank transfer manual penerimaan biaya langganan. |
| **📦 Paket Langganan** | [`/admin/billing-packages`](https://cooca.id/admin/billing-packages) | `admin.billing_packages.index` | `admin.billing_packages.view` | Konfigurasi harga paket SaaS, durasi hari, dan add-on kuota storage. |
| **📑 Transaksi Langganan** | [`/admin/subscriptions`](https://cooca.id/admin/subscriptions) | `admin.subscriptions.index` | `admin.subscriptions.view` | Pantau transaksi pembayaran langganan tenant secara real-time. |
| **💰 Ringkasan Keuangan** | [`/admin/finance`](https://cooca.id/admin/finance) | `admin.finance.index` | `admin.finance.view` | Rekapitulasi mutasi dana masuk, payout vendor, dan komparasi laba. |
| **🤖 Monitoring AI Token** | [`/admin/ai-tokens`](https://cooca.id/admin/ai-tokens) | `admin.ai_tokens.index` | `admin.ai_tokens.view` | Pantau pemakaian token AI platform dan berikan hibah token manual. |
| **💬 Pengaturan WhatsApp**| [`/admin/whatsapp`](https://cooca.id/admin/whatsapp) | `admin.whatsapp.index` | `admin.whatsapp.view` | Kelola template pesan Meta WhatsApp Cloud API dan riwayat pengiriman. |
| **⚙️ Pengaturan Platform** | [`/admin/settings`](https://cooca.id/admin/settings) | `admin.settings.index` | `admin.settings.view` | Konfigurasi TriPay, SMTP Email, nama aplikasi, dan mode pemeliharaan. |
| **🛡️ Hak Akses (RBAC)** | [`/admin/roles`](https://cooca.id/admin/roles) | `admin.roles.index` | `admin.roles.view` | Buat role administrator baru dan tetapkan izin 92 permission granular. |
| **📋 Log Galat Server** | [`/admin/error-logs`](https://cooca.id/admin/error-logs) | `admin.error_logs.index` | `admin.error_logs.view` | Inspeksi jejak error sistem backend untuk investigasi teknis mendalam. |

---

## 2. Panduan Tugas Berdasarkan Peran Staf (*Role-Based SOP*)

### A. Peran: Super Administrator (Pimpinan & CTO)
1. **Investigasi Kendala Tenant via Impersonasi:**
   - Buka menu [`/admin/businesses`](https://cooca.id/admin/businesses).
   - Cari nama tenant yang melaporkan kendala menggunakan kotak pencarian.
   - Klik tombol **Masuk ke Bisnis (Impersonate)**. Anda akan dialihkan langsung ke antarmuka toko/bisnis penyewa tanpa memerlukan kata sandi mereka.
   - Setelah selesai melakukan pengecekan atau perbaikan konfigurasi, klik pita merah di atas layar: **Keluar dari Impersonasi**.
2. **Kelola Hak Akses Staf:**
   - Buka menu [`/admin/roles`](https://cooca.id/admin/roles).
   - Pastikan staf operasional hanya diberikan permission sesuai tupoksinya untuk menjaga keamanan data tenant.

### B. Peran: Customer Support & Verifikator Akun
1. **Memproses Tiket Pemulihan Akun:**
   - Buka menu [`/admin/account-recoveries`](https://cooca.id/account-recoveries).
   - Buka tiket dengan status warna oranye (*Menunggu Review*).
   - Klik tombol **Periksa Berkas**. Cocokkan dokumen Foto KTP Asli, NIB/Bukti Usaha, dan Foto Selfie pemegang identitas.
   - Jika bukti absah, klik **Setujui Permohonan**. Kontak email/WA baru akan langsung aktif dan sistem secara otomatis mengirimkan notifikasi WA ke nomor baru pengguna.
   - Jika dokumen buram atau tidak cocok, klik **Tolak Permohonan** dan cantumkan alasan jelasnya agar pemohon dapat mengunggah ulang dokumen yang benar.

### C. Peran: Finance & Billing Admin
1. **Memeriksa Pembayaran Langganan Manual:**
   - Buka menu [`/admin/subscriptions`](https://cooca.id/admin/subscriptions).
   - Gunakan filter status *Menunggu Konfirmasi* jika ada tenant yang memilih pembayaran transfer bank manual.
   - Periksa kesesuaian bukti transfer dengan mutasi di rekening bank perusahaan ([`/admin/payment-accounts`](https://cooca.id/admin/payment-accounts)).
   - Setujui transaksi agar masa aktif paket tenant bertambah secara otomatis.

---

## 3. Fitur Kenyamanan Kerja (*Productivity Shortcuts*)

- **Pencarian Global Cepat (`⌘K` / `Ctrl+K`):** Tekan kombinasi tombol ini kapan saja untuk membuka kapsul pencarian global guna menemukan bisnis, pengguna, atau tiket permohonan dalam hitungan detik.
- **Mode Gelap / Terang (*Dark/Light Mode*):** Tombol toggle tema tersedia di topbar kanan untuk kenyamanan penglihatan saat bertugas malam hari.
