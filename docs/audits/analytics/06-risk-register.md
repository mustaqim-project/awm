# Risk Register — Modul Analytics
> Modul: Analytics | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Auditor: AI Agent (/laravel-module-audit) | Status Dokumen: Final

---

## 1. Register Risiko & Status Mitigasi

| ID Risiko | Temuan Terkait | Judul Risiko & Dampak | Severity | Likelihood | Impact | Status | Task Mitigasi | Bukti Penutupan |
|---|---|---|:---:|:---:|:---:|:---:|:---:|---|
| **R-ANA-001** | `F-ANA-003` | Kurva Laba Kotor Kosong / Error Mismatch pada Periode Hari Ini | **High** | High | High | **Mitigated** | `T-ANA-003` | Array `profit` terisi penuh sinkron dengan `labels` (17 titik data 07:00–23:00) |
| **R-ANA-002** | `F-ANA-002` | Server Crash HTTP 500 akibat Tanggal Malformed / Terbalik | **High** | Medium | High | **Mitigated** | `T-ANA-002` | Unit test `test_analytics_handles_malformed_and_inverted_custom_dates_gracefully` lulus |
| **R-ANA-003** | `F-ANA-001` | Bypass Permission jika Endpoint Dipanggil di Luar Route Group | **Medium** | Low | High | **Mitigated** | `T-ANA-001` | `AnalyticsWebController implements HasMiddleware` dengan static middleware |
| **R-ANA-004** | `F-ANA-005` | Distorsi Omzet Cabang akibat Pesanan Online Tidak Terfilter | **Medium** | High | Medium | **Mitigated** | `T-ANA-005` | Kueri `CommerceOrder` memuat `where('location_id', $locationId)` |
| **R-ANA-005** | `F-ANA-006` | Bottleneck CPU Database saat Volume Transaksi POS Membesar | **Medium** | High | Medium | **Mitigated** | `T-ANA-006` | Refaktor `whereHas` menjadi direct `JOIN` pada `pos_order_items` & `payments` |
| **R-ANA-006** | `F-ANA-004` | Error Sintaks SQL jika Aplikasi Dideploy pada Engine PostgreSQL | **Medium** | Low | High | **Mitigated** | `T-ANA-004` | Kueri ekspresi jam mendukung MySQL, SQLite, dan PostgreSQL |
| **R-ANA-007** | `F-ANA-009` | Ketidaksesuaian Bahasa Antarmuka untuk Pengguna Bahasa Inggris | **Medium** | High | Low | **Mitigated** | `T-ANA-009` | Dibuat file translasi `lang/id/analytics.php` dan `lang/en/analytics.php` |
| **R-ANA-008** | `F-ANA-010` | Regresi Kode Baru akibat Kekosongan Negative Test Otorisasi | **Medium** | Medium | Medium | **Mitigated** | `T-ANA-010` | Test suite ditambah 6 test case komprehensif mencakup 403, tenant leak, dan edge case |
| **R-ANA-009** | `F-ANA-007` | Pelanggaran Standar Desain Apple HIG Anti-Pill pada Persentase Margin | **Low** | High | Low | **Mitigated** | `T-ANA-007` | Badge `rounded-full` dihapus, diganti tipografi semantik tabular |
| **R-ANA-010** | `F-ANA-008` | Tombol Filter Sulit Disentuh pada Layar HP Kecil (360px) | **Low** | Medium | Low | **Mitigated** | `T-ANA-008` | Touch target dinaikkan ke min 38px dengan pembungkus `overflow-x-auto` |
| **R-ANA-011** | `F-ANA-011` | Pemilik Bisnis Tidak Bisa Mencetak Dashboard Eksekutif | **Low** | Medium | Low | **Mitigated** | `T-ANA-011` | Ditambahkan tombol Cetak Laporan (`window.print()`) dan CSS print rapi |

---

## 2. Analisis Akar Masalah (Root Cause Analysis)

1. **RCA-1 (Grafik Laba Hari Ini Kosong):**
   - *Gejala:* Kurva laba kotor tidak muncul saat memilih filter "Hari Ini".
   - *Penyebab Langsung:* `$profitData` tidak pernah di-push di loop per-jam baris 307.
   - *Penyebab Akar:* Pengujian awal hanya memeriksa HTTP 200 pada halaman utama tanpa memvalidasi struktur data JSON grafik jam per jam.
   - *Pencegahan:* Menambahkan assertion eksplisit pada test: `$this->assertNotEmpty($response->json('data.trend.profit'))`.

2. **RCA-2 (Potensi Crash Input Tanggal):**
   - *Gejala:* URL dengan parameter acak memicu eksepsi internal Carbon.
   - *Penyebab Langsung:* Tidak ada try/catch pada `Carbon::parse()`.
   - *Penyebab Akar:* Asumsi input selalu berasal dari datepicker HTML5 bawaan browser, mengabaikan manipulasi URL manual atau bot spider.
   - *Pencegahan:* Penanganan fail-safe selalu disiapkan untuk setiap parser input eksternal.
