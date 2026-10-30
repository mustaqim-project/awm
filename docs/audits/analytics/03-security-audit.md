# Security Audit   Modul Analytics
> Modul: Analytics | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Auditor: AI Agent (/laravel-module-audit) | Status Dokumen: Final

---

## 1. Tabel Endpoint × Kontrol Keamanan

| Method | Endpoint URI | Middleware Proteksi | RBAC Permission | Multi-Tenant Scope | Input Validation / Sanitasi | Status Keamanan |
|---|---|---|---|---|---|:---:|
| `GET` | `/analytics` | `web, auth:web, wa.otp, business.active, email.verified` | `reports.view` (Controller-Level) | `Context::requireBusiness()` & `business_id` | Whitelist period, try/catch date parse, location UUID check | **SECURE** |
| `GET` | `/analytics?format=json` | `web, auth:web, wa.otp, business.active, email.verified` | `reports.view` (Controller-Level) | `Context::requireBusiness()` & `business_id` | JSON payload response, no PII leakage | **SECURE** |

---

## 2. Hasil Audit Otorisasi & Multi-Tenant Isolation

1. **Multi-Tenant Data Leaks Test:**
   - Seluruh query Eloquent (`PosOrder`, `Invoice`, `CommerceOrder`, `Expense`, `Location`) menyertakan klausa `where('business_id', $business->id)` yang diambil dari context bisnis aktif terautentikasi (`Context::requireBusiness()`).
   - Uji penetrasi lokal membuktikan User dari Bisnis A tidak dapat mengakses ringkasan maupun data agregasi Bisnis B.
2. **Location Parameter Boundary Check:**
   - Parameter `location_id` diperiksa terhadap koleksi lokasi aktif milik bisnis:
     ```php
     if ($selectedLocationId && ! $locations->contains('id', $selectedLocationId)) {
         $selectedLocationId = null;
     }
     ```
   - Jika pengguna memalsukan UUID lokasi milik tenant lain atau lokasi yang tidak aktif, sistem otomatis meresetnya ke `null` sehingga hanya data terkonsolidasi milik bisnis sendiri yang ditampilkan tanpa error atau kebocoran data.

---

## 3. Peta OWASP Top 10:2025 & Temuan Keamanan

### A01:2021   Broken Access Control
- **Audit RBAC:** Endpoint dilindungi permission `reports.view`. Pengguna tanpa izin ini (misal kasir tanpa izin laporan) ditolak dengan respon HTTP `403 Forbidden`.
- **Temuan F-ANA-001:** Deklarasi middleware permission dipindahkan ke controller (`HasMiddleware`) untuk menjamin proteksi Controller-Level RBAC yang permanen.

### A03:2021   Injection (SQL & XSS)
- **SQL Injection:**
  - Kueri `selectRaw` pada controller menggunakan ekspresi agregasi statis tanpa variabel input pengguna:
    `COALESCE(SUM(total_amount), 0) as revenue`
    `whereDate('order_date', '>=', $fromStr)`
  - Parameter tanggal dibersihkan menjadi `toDateString()` (`YYYY-MM-DD`) sebelum diikat ke query builder.
  - Tidak ditemukan kerentanan SQL Injection pada modul ini.
- **Cross-Site Scripting (XSS):**
  - View Blade `resources/views/app/analytics/index.blade.php` tidak menggunakan unescaped syntax `{!! !!}` maupun direktif Alpine `x-html`.
  - Passing data dari PHP ke JavaScript Chart.js menggunakan facade resmi `Js::from()`, yang otomatis mengamankan karakter kontrol dan tanda kutip dari potensi payload XSS.

### A04:2021   Insecure Design (Handling Input Tidak Valid)
- **Temuan F-ANA-002 (High):**
  - **Lokasi:** `AnalyticsWebController.php:261-272`
  - **Kerentanan:** Input parameter `from` dan `to` langsung dimasukkan ke `Carbon::parse($fromInput)` tanpa blok penanganan eksepsi.
  - **Skenario Eksploitasi:** Penyerang mengirimkan URL `GET /analytics?period=custom&from=invalid-string&to=evil` yang memicu `InvalidFormatException`, menyebabkan layar putih / error 500 dan potensi kebocoran stack trace jika debug aktif.
  - **Perbaikan:** Menambahkan penanganan `try/catch (\Throwable)` yang mengembalikan nilai rentang ke awal dan akhir bulan berjalan secara aman, serta membalik rentang secara otomatis jika tanggal awal lebih besar dari tanggal akhir.

---

## 4. Hasil Audit Dependensi (`composer audit`)

- Dependensi framework dan package audit dijalankan pada lingkungan lokal: tidak ditemukan vulnerabilitas kritis pada package inti pelaporan dan analitik (`carbonphp/carbon-doctrine-types`, `illuminate/support`, `laravel/framework`).
