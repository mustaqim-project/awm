# Troubleshooting & Pemecahan Masalah - Modul Platform Backoffice & Superadmin (`admin`)

> Modul: `admin` | Panduan Pemulihan Mandiri Staf Operasional | Tanggal: 2026-10-26

---

## 1. Masalah Otentikasi & Hak Akses

### Kendala 1: "Akses Ditolak: Akun administrator Anda telah dinonaktifkan"
- **Gejala:** Muncul pesan peringatan merah saat mencoba login atau sesi langsung terputus seketika saat berpindah halaman.
- **Penyebab:** Kolom status `is_active` pada tabel `admins` bernilai `false` (dinonaktifkan oleh Pimpinan atau organisasi).
- **Langkah Solusi:**
  1. Hubungi Super Administrator utama atau Lead Developer.
  2. Buka basis data atau konsol Tinker:
     ```bash
     php artisan tinker --execute="App\Models\Admin::where('email', 'email_anda@cooca.id')->update(['is_active' => true]);"
     ```
  3. Coba login kembali di [`/admin/login`](https://cooca.id/admin/login).

---

### Kendala 2: Galat 403 Saat Mencoba Impersonasi Bisnis Tenant
- **Gejala:** Muncul pesan *403 Unauthorized* saat menekan tombol "Masuk ke Bisnis" di menu [`/admin/businesses`](https://cooca.id/admin/businesses).
- **Penyebab:** Akun admin yang digunakan tidak memiliki izin `admin.businesses.impersonate` atau bukan berstatus Super Administrator.
- **Langkah Solusi:**
  1. Buka menu [`/admin/roles`](https://cooca.id/admin/roles) menggunakan akun Superadmin.
  2. Edit peran admin bersangkutan, centang izin **"Impersonasi Tenant Bisnis (admin.businesses.impersonate)"**.
  3. Simpan perubahan dan minta staf terkait memuat ulang halaman (*refresh*).

---

## 2. Masalah Integrasi Layanan Pihak Ketiga

### Kendala 3: Notifikasi WhatsApp Tidak Terkirim Saat Menyetujui Pemulihan Akun
- **Gejala:** Akun pemohon berhasil di-approve, tetapi nomor baru tidak menerima pesan konfirmasi WhatsApp resmi dari sistem.
- **Penyebab:** Token akses Meta Cloud API kedaluwarsa atau Phone Number ID salah konfigurasi.
- **Langkah Solusi:**
  1. Buka menu [`/admin/settings`](https://cooca.id/admin/settings) lalu pilih tab **WhatsApp**.
  2. Klik tombol **Uji Pengiriman Pesan WhatsApp (Test WhatsApp Connection)**.
  3. Jika pesan uji coba gagal, periksa token permanen (*System User Access Token*) di dasbor Meta for Developers.
  4. Perbarui token di form pengaturan dan klik **Simpan Pengaturan**.

---

### Kendala 4: Email Verifikasi atau Reset Password Tidak Masuk ke Inbox
- **Gejala:** Pengguna atau admin tidak menerima email tautan reset kata sandi.
- **Penyebab:** Konfigurasi kredensial SMTP Mailer tidak valid atau masuk folder Spam/Junk.
- **Langkah Solusi:**
  1. Buka menu [`/admin/settings`](https://cooca.id/admin/settings) tab **SMTP / Email**.
  2. Klik tombol **Kirim Email Uji Coba**. Masukkan alamat email pribadi Anda.
  3. Periksa respons sistem: jika muncul pesan galat koneksi (misal *Connection timed out* atau *Auth failed*), pastikan port SMTP (587 untuk TLS, 465 untuk SSL) dan kata sandi aplikasi (*App Password*) sudah tepat.

---

### Kendala 5: Status Transaksi TriPay Tidak Otomatis Berubah Menjadi Lunas
- **Gejala:** Tenant sudah menyelesaikan pembayaran via QRIS atau VA TriPay, tetapi status paket di dashboard admin tetap *Menunggu Pembayaran*.
- **Penyebab:** URL Callback / Webhook di dasbor merchant TriPay belum diatur atau terblokir firewall.
- **Langkah Solusi:**
  1. Masuk ke Dasbor Merchant TriPay Anda.
  2. Buka menu **Pengaturan Transaksi -> URL Callback**.
  3. Pastikan URL Callback mengarah ke: `https://cooca.id/api/v1/callbacks/tripay`.
  4. Salin Private Key dari dasbor TriPay dan pastikan cocok dengan yang tersimpan di [`/admin/settings`](https://cooca.id/admin/settings) tab Pembayaran.

---

## 3. Masalah Tampilan & Berkas Dokumen

### Kendala 6: Dokumen KTP atau NIB Pemohon Pemulihan Akun Gagal Tampil
- **Gejala:** Jendela pratinjau dokumen menampilkan ikon berkas rusak atau status galat HTTP 403.
- **Penyebab:** Sesi admin kedaluwarsa saat membuka jendela pratinjau atau berkas terhapus dari disk server.
- **Langkah Solusi:**
  1. Muat ulang halaman Meja Uji Bukti Otentik di [`/admin/account-recoveries`](https://cooca.id/admin/account-recoveries).
  2. Jika sesi telah habis, login kembali ke Admin Console.
  3. Jika berkas tetap tidak dapat dimuat, hubungi pemohon via telepon resmi yang tertera pada permohonan untuk meminta pengiriman ulang berkas via kanal WhatsApp resmi COOCA.
