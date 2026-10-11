# Modul Komunitas & Manajemen Event UMKM (Community & Event Management)

> **Layer 2: Technical Module Specification**  
> **Package:** `App\Domain\Community`, `App\Http\Controllers\Web\CommunityWebController`, `App\Http\Controllers\Admin\AdminCommunityEventController`  
> **Status:** Production-Ready (Verified)

---

## 1. Ikhtisar & Tujuan Modul
Modul Komunitas & Event menyediakan ruang jejaring bisnis dan edukasi antar-pelaku UMKM di ekosistem COOCA. Modul ini memungkinkan:
1. **Akses Universal Tanpa Batas Langganan:** Dibuka untuk seluruh tingkatan paket (Free, Standard, Premium, Prestige).
2. **Katalog Event Terbaru:** Menampilkan event mendatang di bagian paling atas halaman Komunitas, dengan filter otomatis agar event yang sudah berakhir (`end_time < now()`) tidak lagi ditampilkan kepada pengguna umum.
3. **Pendaftaran 1-Klik:** Pengguna bisnis dapat mendaftarkan diri secara instan dengan kuota yang tervalidasi secara atomik.
4. **Tiket Digital & Presensi Ganda:**
   - **Presensi Lokasi (GPS Geofencing):** Menghitung jarak pengguna terhadap titik koordinat venue event (rumus Haversine) dengan toleransi radius meter yang dikonfigurasi admin.
   - **Presensi Barcode/QR Code:** Tiket dilengkapi barcode linier dan QR code unik berbasis SVG. Peserta dapat memindai barcode venue atau panitia memindai tiket peserta melalui live camera scanner.
5. **Konsol Administrasi Event:** Admin COOCA mengelola publikasi event, menetapkan kuota, memantau daftar hadir peserta secara real-time, memverifikasi check-in manual/scanner, dan mengekspor roster peserta ke CSV.

---

## 2. Struktur Database & Model

### 2.1 Tabel `community_events`
Menyimpan data master event yang diselenggarakan oleh admin/platform.
- `id` (bigint, PK)
- `title` (varchar 255): Judul event.
- `slug` (varchar 255, unique): URL identifier ramah SEO.
- `description` (text): Deskripsi lengkap, susunan acara, dan pembicara.
- `location_name` (varchar 255): Nama lokasi/gedung (misal: "Menara Batavia Lantai 12").
- `location_address` (text, nullable): Alamat lengkap fisik venue.
- `latitude` (decimal 10,7, nullable): Titik koordinat garis lintang GPS venue.
- `longitude` (decimal 10,7, nullable): Titik koordinat garis bujur GPS venue.
- `checkin_radius_meters` (integer, default 200): Radius batas toleransi kehadiran GPS dalam meter.
- `start_time` (datetime): Waktu mulai acara.
- `end_time` (datetime): Waktu selesai acara.
- `registration_deadline` (datetime): Batas akhir pendaftaran peserta.
- `max_participants` (integer, default 0): Kapasitas maksimal kuota (0 = tidak terbatas).
- `venue_checkin_code` (varchar 64): Token unik untuk barcode/QR check-in mandiri di lokasi acara.
- `banner_url` (varchar 255, nullable): Tautan visual cover event.
- `status` (enum: `draft`, `published`, `cancelled`, default `published`).
- `created_by` (foreign key `users.id`, nullable).
- Timestamps.

### 2.2 Tabel `community_event_registrations`
Menyimpan riwayat dan status tiket setiap pendaftar event.
- `id` (bigint, PK)
- `event_id` (foreign key `community_events.id`, cascade on delete).
- `user_id` (foreign key `users.id`, cascade on delete).
- `business_id` (foreign key `businesses.id`, nullable).
- `ticket_code` (varchar 64, unique): Kode unik tiket digital (misal: `EVT-20261011-AB12CD`).
- `status` (enum: `registered`, `attended`, `cancelled`, default `registered`).
- `attended_at` (datetime, nullable): Waktu presensi berhasil dicatat.
- `checkin_method` (enum: `location`, `barcode`, `manual`, nullable): Kanal kehadiran terverifikasi.
- `notes` (text, nullable): Catatan kehadiran.
- Timestamps.
- **Unique Constraint:** `(event_id, user_id)` mencegah duplikasi pendaftaran ganda.

---

## 3. Layanan Domain (Services)

### 3.1 `EventTicketService` (`app/Domain/Community/EventTicketService.php`)
- **Fungsi:** Menghasilkan tiket unik, kode presensi venue, serta rendering grafis QR Code dan Barcode secara server-side.
- **Engine Barcode & QR:**
  - Menggunakan library `bacon/bacon-qr-code` (`SvgImageBackEnd`) untuk menghasilkan SVG QR Code tajam, independen dari CDN eksternal.
  - Menggunakan generator barcode SVG murni dengan checksum modulo untuk menampilkan barcode linier standar tiket fisik.

### 3.2 `EventCheckinService` (`app/Domain/Community/EventCheckinService.php`)
- **Fungsi:** Memvalidasi presensi peserta melalui algoritma geolokasi dan verifikasi token.
- **Algoritma Haversine Formula:**
  ```php
  $dLat = deg2rad($lat2 - $lat1);
  $dLon = deg2rad($lon2 - $lon1);
  $a = sin($dLat/2) * sin($dLat/2) +
       cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
       sin($dLon/2) * sin($dLon/2);
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
  $distance = $earthRadius * $c; // dalam meter
  ```
- **Kondisi Validasi:**
  - Memverifikasi apakah event sedang berlangsung atau dalam jendela check-in (mulai 2 jam sebelum event s/d waktu selesai).
  - Memverifikasi apakah jarak peserta ke koordinat venue `<= $event->checkin_radius_meters`.
  - Memverifikasi token barcode tiket atau venue check-in code.

---

## 4. Alur & Antarmuka Pengguna

### 4.1 Halaman Komunitas Tenant (`/community`)
- **Top Spotlight Event Bento Grid:**
  - Ditempatkan di bagian paling atas feed komunitas.
  - Memfilter event aktif mendatang (`CommunityEvent::upcoming()`), menyaring tuntas event yang sudah lewat.
  - Menampilkan badge "Terbuka untuk Semua Paket", tanggal pelaksanaan, sisa kuota, batas akhir pendaftaran, dan nama venue.
  - Tombol aksi kontekstual: "Daftar Event" (jika belum daftar), "Buka Tiket Digital" (jika sudah daftar), atau "Pendaftaran Ditutup" (jika kuota penuh / lewat deadline).
- **Modal Tiket Digital & Presensi Cepat:**
  - Menampilkan SVG QR Code & Barcode tiket.
  - Tab presensi via GPS: Meminta izin geolokasi browser peserta (`navigator.geolocation.getCurrentPosition`), mengirimkan koordinat ke endpoint, dan memperbarui status menjadi "Hadir".
  - Tab presensi via Barcode: Mengaktifkan pemindaian barcode venue via kamera atau input kode venue.

### 4.2 Konsol Admin Event (`/admin/events`)
- **Dashboard Ringkasan KPI:** Total event aktif, total pendaftar terakumulasi, event mendatang, dan rata-rata tingkat kehadiran.
- **CRUD Event:** Form pembuatan dan pengeditan event dengan validasi batas waktu, kuota, koordinat lintang/bujur, serta radius toleransi meter.
- **Live Event Scanner & Roster:**
  - Menampilkan QR Code resmi venue untuk ditempel di meja resepsionis acara.
  - Scanner kamera terintegrasi untuk panitia memindai tiket barcode peserta langsung di pintu masuk.
  - Tabel roster peserta interaktif dengan tombol "Check-in Manual" untuk verifikasi darurat.
  - Ekspor daftar hadir ke CSV format spreadsheet.

---

## 5. Keamanan & Kebijakan Akses (RBAC)
- **Tenant Scope:** Pendaftaran terikat pada `user_id` dan `business_id` aktif. Pembatalan hanya diizinkan untuk registrasi milik pengguna yang bersangkutan.
- **Admin Scope:** Seluruh rute `/admin/events/*` diproteksi middleware `auth:web` dan `role:super_admin|admin`.
- **Anti-Duplikasi:** Pendaftaran menggunakan transaksi database dengan kunci `firstOrCreate` atau pencegahan integritas ganda berbasis index unik.
