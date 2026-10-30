# Functional, UI/UX & Human Error Audit   Modul Analytics
> Modul: Analytics | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Auditor: AI Agent (/laravel-module-audit) | Status Dokumen: Final

---

## 1. Matriks Fitur × Skenario Pengujian Fungsional

| Fitur Analitik | Skenario Normal | Skenario Batas (Edge Case) | Skenario Input Salah / Malformed | Hasil Verifikasi |
|---|---|---|---|:---:|
| **Filter Periode Standar** | Hari Ini, Minggu Ini, Bulan Ini, Tahun Ini menampilkan data sesuai rentang | Pergantian tahun/bulan (kabisat, akhir bulan 31 hari) | Periode tidak dikenal fallback ke `month` | `[VERIFIED] PASS` |
| **Rentang Kustom (Custom Range)** | Memilih tanggal dari & sampai secara spesifik | Rentang 1 hari (from = to) | Tanggal format acak / string teks; tanggal from > to | `[VERIFIED] PASS` (Auto-swap & Fallback) |
| **Filter Lokasi / Cabang** | Memilih outlet tertentu menyaring POS, Faktur, Toko Online & Beban | Bisnis hanya memiliki 1 lokasi (dropdown cabang otomatis tersembunyi) | Lokasi tidak valid / milik tenant lain fallback ke konsolidasi semua cabang | `[VERIFIED] PASS` |
| **Metrik KPI Finansial** | Omzet, HPP, Laba Kotor, Laba Bersih, AOV terhitung presisi | Periode baru tanpa transaksi (menampilkan Rp 0 tanpa divide-by-zero) | Transaksi berstatus void/refund tidak masuk omzet | `[VERIFIED] PASS` |
| **Pertumbuhan (% Growth)** | Membandingkan periode ini vs periode lalu | Periode lalu bernilai 0 (pertumbuhan +100% atau -100%) | Laba merugi di periode berjalan | `[VERIFIED] PASS` |
| **Grafik Tren Penjualan & Laba** | Kurva omzet dan laba bergaris ganda per hari/jam | Periode Hari Ini menampilkan kurva per jam 07:00–23:00 | Jam sepi tanpa transaksi tetap memuat titik 0 | `[VERIFIED] PASS` |
| **Jam Sibuk Kasir (Heatmap)** | Diagram batang frekuensi transaksi jam 07:00–23:00 | Jam di luar operasional tidak ditampilkan | Toko baru buka tanpa antrean menampilkan 0 transaksi | `[VERIFIED] PASS` |
| **Top 10 Produk Terlaris** | Menampilkan nama produk, qty, omzet, dan margin laba | Produk tanpa HPP menampilkan margin 0% tanpa crash | Menampilkan maksimal 10 produk dengan kuantitas tertinggi | `[VERIFIED] PASS` |
| **Metode Pembayaran** | Donat persentase QRIS, Tunai, EDC, Transfer, Kasbon | Belum ada transaksi menampilkan placeholder ramah | Pembayaran campuran multi-metode | `[VERIFIED] PASS` |
| **Cetak Eksekutif (Print)** | Tombol Print memicu dialog cetak browser bersih | Cetak pada kertas A4 / PDF tanpa tombol navigasi yang mengganggu | Layout responsif cetak rapi | `[VERIFIED] PASS` |

---

## 2. Temuan UI/UX & Kepatuhan Bento Apple HIG

### F-ANA-007   Anti-Pill-Abuse Violation pada Persentase Margin Produk
- **Severity:** Low | **Bukti:** `[VERIFIED]` | **Aspek:** UI/UX & Design System
- **Lokasi:** `resources/views/app/analytics/index.blade.php:471-473, 498-500`
- **Bukti:**
  ```blade
  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $prod['margin_pct'] >= 40 ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF9500]/15 text-[#FF9500]' }} tabular-nums">
      {{ $prod['margin_pct'] }}%
  </span>
  ```
- **Masalah:** Penerapan badge `rounded-full` pada angka persentase margin melanggar *Anti-Pill-Abuse Mandate* COOCA Bento Apple HIG. Di COOCA, badge pill hanya boleh digunakan untuk status siklus hidup entitas (mis. Lunas, Pending, Dibatalkan), bukan untuk angka metrik.
- **Perbaikan:** Menghapus badge pill `rounded-full` dan menggantinya dengan tipografi semantik Apple HIG murni: `tabular-nums font-semibold {{ $prod['margin_pct'] >= 40 ? 'text-[#34C759]' : 'text-[#FF9500]' }}`.

---

### F-ANA-008   Sub-standard Mobile Touch Target & Horizontal Overflow Risk
- **Severity:** Low | **Bukti:** `[VERIFIED]` | **Aspek:** Responsive UI/UX
- **Lokasi:** `resources/views/app/analytics/index.blade.php:253-269`
- **Bukti:**
  ```blade
  <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-xs font-semibold">
      <button type="button" @click="selectPeriod('today')" class="px-3 py-1.5 ...">Hari Ini</button>
  ```
- **Masalah:** Tombol filter waktu memiliki padding vertikal `py-1.5` yang menghasilkan tinggi sentuh ~30px, di bawah standar Apple Touch Target minimum 44×44px. Selain itu, pada layar smartphone sempit (360px), deretan 5 tombol segmented control berisiko terpotong horizontal.
- **Perbaikan:** Menambahkan kontainer pembungkus dengan `overflow-x-auto no-scrollbar` serta menaikkan ukuran sentuh tombol menjadi `py-2 px-3.5 min-h-[38px] sm:min-h-[36px]` yang ergonomis untuk ibu jari (*thumb zone*).

---

### F-ANA-009   Absennya File Translasi i18n (Zero Hardcoded Text Mandate)
- **Severity:** Medium | **Bukti:** `[VERIFIED]` | **Aspek:** Internationalization (i18n)
- **Lokasi:** `resources/views/app/analytics/index.blade.php:1-565`, `app/Http/Controllers/Web/AnalyticsWebController.php:480-487`
- **Masalah:** Seluruh teks antarmuka, judul kartu, label grafik, dan tooltip ditulis secara hardcode dalam Bahasa Indonesia tanpa fungsi helper `__('analytics....')`. Pengguna berbahasa Inggris melihat teks Indonesia yang tidak konsisten dengan navigasi aplikasi.
- **Perbaikan:** Membuat file bahasa resmi:
  - `lang/id/analytics.php`
  - `lang/en/analytics.php`
  Mengganti seluruh teks statis di Blade view dan controller dengan translasi dinamis.

---

## 3. Human Error Prevention Controls

1. **Pencegahan Data Kosong/Minus Ekstrem:** Rata-rata nilai keranjang (*Average Order Value / AOV*) dilindungi logika pembagian aman: jika pesanan 0, AOV bernilai 0.0 (mencegah error *Division by zero*).
2. **Pencegahan Kesalahan Input Tanggal:** Form rentang kustom membatasi tanggal melalui `type="date"` dan divalidasi di backend agar rentang tidak terbalik.
3. **Pemberitahuan Status Kosong (*Empty State*):** Tabel produk terlaris dan grafik metode pembayaran memiliki tampilan state kosong yang bersahaja dan informatif bila bisnis baru beroperasi atau belum memiliki data transaksi pada rentang terkait.
