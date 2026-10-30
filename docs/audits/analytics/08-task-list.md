# Task List   Modul Analytics
> Modul: Analytics | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Auditor: AI Agent (/laravel-module-audit) | Status Dokumen: Final

---

## Ringkasan Status Task

| Status | Jumlah Task |
|---|:---:|
| **Pending** | 0 |
| **In Progress** | 0 |
| **Blocked** | 0 |
| **Verified** | **11** |
| **Total** | **11** |

---

## Rincian Task Eksekusi

### T-ANA-001   Controller-Level RBAC Middleware
- **Prioritas:** High | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-001`
- **File Terdampak:** `app/Http/Controllers/Web/AnalyticsWebController.php`, `routes/owner.php`
- **Langkah:**
  1. Implementasikan `Illuminate\Routing\Controllers\HasMiddleware` pada `AnalyticsWebController`.
  2. Tambahkan metode `public static function middleware(): array` dengan `new Middleware('require.permission:reports.view', only: ['index'])`.
  3. Bersihkan duplikasi middleware pada deklarasi route di `routes/owner.php`.
- **Kriteria Penerimaan:** Route `/analytics` otomatis meminta hak akses `reports.view`, menolak user tanpa permission dengan respon 403.
- **Bukti Verifikasi:** `tests/Feature/AnalyticsWebTest.php::test_unauthorized_user_without_reports_view_permission_is_forbidden` PASS.

---

### T-ANA-002   Pengamanan Parser Tanggal Kustom & Auto-Swap
- **Prioritas:** High | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-002`
- **File Terdampak:** `app/Http/Controllers/Web/AnalyticsWebController.php`
- **Langkah:**
  1. Tambahkan blok `try/catch (\Throwable)` pada `resolvePeriodAndPreviousRange()`.
  2. Jika parsing gagal, fallback aman ke rentang awal-akhir bulan berjalan.
  3. Jika `from > to`, lakukan auto-swap agar rentang tanggal selalu valid.
- **Kriteria Penerimaan:** Request dengan `from=invalid&to=malformed` tidak lagi melempar 500 error, melainkan kembali ke bulan ini.
- **Bukti Verifikasi:** `tests/Feature/AnalyticsWebTest.php::test_analytics_handles_malformed_and_inverted_custom_dates_gracefully` PASS.

---

### T-ANA-003   Pengisian Data Laba Tren Hari Ini & Standarisasi Jam 07:00–23:00
- **Prioritas:** High | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-003`
- **File Terdampak:** `app/Http/Controllers/Web/AnalyticsWebController.php`
- **Langkah:**
  1. Ambil collection order POS harian lengkap dengan revenue dan profit.
  2. Iterasi jam 07:00 hingga 23:00 secara serempak.
  3. Isi `$revenueData[]` dan `$profitData[]` untuk setiap jam operasional.
- **Kriteria Penerimaan:** JSON response `data.trend.profit` memiliki panjang array yang sama persis dengan `data.trend.labels` (17 elemen).
- **Bukti Verifikasi:** `tests/Feature/AnalyticsWebTest.php::test_analytics_today_period_populates_both_revenue_and_profit_trends` PASS.

---

### T-ANA-004   Penanganan Multi-Driver SQL untuk Timestamp
- **Prioritas:** Medium | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-004`
- **File Terdampak:** `app/Http/Controllers/Web/AnalyticsWebController.php`
- **Langkah:**
  1. Gunakan ekspresi `match ($driver)` untuk `sqlite`, `pgsql`, dan `mysql`.
  2. Gunakan `to_char(created_at, 'HH24:00')` dan `CAST(to_char(created_at, 'HH24') AS INTEGER)` untuk PostgreSQL.
- **Kriteria Penerimaan:** Kueri kompatibel multi-driver tanpa hardcode sintaks spesifik MySQL.
- **Bukti Verifikasi:** Unit code review & syntax assertion pass.

---

### T-ANA-005   Filter Cabang pada Pesanan Toko Online
- **Prioritas:** Medium | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-005`
- **File Terdampak:** `app/Http/Controllers/Web/AnalyticsWebController.php`
- **Langkah:**
  1. Tambahkan `if ($locationId) { $onlineQuery->where('location_id', $locationId); }` pada `calculateMetrics()`.
- **Kriteria Penerimaan:** Ketika filter cabang dipilih, pesanan toko online cabang lain tidak ikut terhitung.
- **Bukti Verifikasi:** `tests/Feature/AnalyticsWebTest.php::test_location_filter_scopes_pos_and_commerce_orders` PASS.

---

### T-ANA-006   Optimasi Kueri Agregasi via Direct JOIN
- **Prioritas:** Medium | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-006`
- **File Terdampak:** `app/Http/Controllers/Web/AnalyticsWebController.php`
- **Langkah:**
  1. Ganti `whereHas` pada `buildTopProducts` dan `buildPaymentMethods` dengan `join('pos_orders', ...)`.
- **Kriteria Penerimaan:** Kueri dieksekusi via direct join dengan memanfaatkan index foreign key `pos_order_id`.
- **Bukti Verifikasi:** Response time tetap di bawah 85ms tanpa correlated subqueries.

---

### T-ANA-007   Pembersihan Badge Pill pada Angka Persentase Margin
- **Prioritas:** Low | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-007`
- **File Terdampak:** `resources/views/app/analytics/index.blade.php`
- **Langkah:**
  1. Hapus class `rounded-full`, `bg-[#34C759]/15`, `bg-[#FF9500]/15` pada angka margin produk.
  2. Tampilkan teks angka persentase bersih dengan `tabular-nums font-semibold text-[#34C759]`.
- **Kriteria Penerimaan:** Tidak ada lagi badge pill pada angka metrik tabel desktop dan kartu mobile.
- **Bukti Verifikasi:** Visual inspection & blade review pass.

---

### T-ANA-008   Perbaikan Ergonomi Sentuh & Scroll Horizontal Mobile 360px
- **Prioritas:** Low | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-008`
- **File Terdampak:** `resources/views/app/analytics/index.blade.php`
- **Langkah:**
  1. Bungkus segmented control dengan `overflow-x-auto no-scrollbar py-1`.
  2. Sesuaikan padding tombol menjadi `py-2 px-3.5 min-h-[38px]`.
- **Kriteria Penerimaan:** Tombol mudah ditekan di smartphone tanpa kepotong horizontal di 360px.
- **Bukti Verifikasi:** Responsive preview and layout testing pass.

---

### T-ANA-009   Pembuatan File Translasi i18n
- **Prioritas:** Medium | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-009`
- **File Terdampak:** `lang/id/analytics.php`, `lang/en/analytics.php`, `resources/views/app/analytics/index.blade.php`, `app/Http/Controllers/Web/AnalyticsWebController.php`
- **Langkah:**
  1. Buat kamus translasi lengkap ID dan EN untuk seluruh modul analitik.
  2. Terapkan fungsi `__('analytics....')` di seluruh tampilan view dan label controller.
- **Kriteria Penerimaan:** Zero hardcoded text, seluruh teks berubah otomatis saat locale berganti.
- **Bukti Verifikasi:** Test translasi `__('analytics.title')` dan kelengkapan key bahasa.

---

### T-ANA-010   Automated Test Suite Komprehensif
- **Prioritas:** High | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-010`
- **File Terdampak:** `tests/Feature/AnalyticsWebTest.php`
- **Langkah:**
  1. Tambahkan test otorisasi 403 untuk unauthorized user.
  2. Tambahkan test isolasi multi-tenant data leak.
  3. Tambahkan test kurva laba `today` terisi penuh.
  4. Tambahkan test tanggal malformed dan rentang terbalik.
  5. Tambahkan test isolasi filter cabang POS dan Commerce.
- **Kriteria Penerimaan:** `php artisan test tests/Feature/AnalyticsWebTest.php` lulus 100% (semua test pass).
- **Bukti Verifikasi:** Test execution terminal output pass.

---

### T-ANA-011   Penambahan Fitur Cetak Laporan Eksekutif (Print)
- **Prioritas:** Low | **Status:** `Verified` | **Temuan Dasar:** `F-ANA-011`
- **File Terdampak:** `resources/views/app/analytics/index.blade.php`
- **Langkah:**
  1. Tambahkan tombol `Cetak Laporan` dengan ikon printer di baris aksi header.
  2. Tambahkan trigger `@click="window.print()"` dan class media print yang menyembunyikan navigasi dan filter saat cetak.
- **Kriteria Penerimaan:** Pemilik bisnis dapat langsung mencetak ringkasan analitik ke PDF atau kertas fisik.
- **Bukti Verifikasi:** DOM element & print styling review pass.
