# Risk Register - Modul Faktur Penjualan & Piutang (Invoices)

> Modul: Invoices & Accounts Receivable | Tanggal Audit: 2026-10-26 | Target: `resources/views/app/invoices`

## 1. Matriks Penilaian Risiko (Risk Matrix)

| ID Risiko | Kategori | Deskripsi Skenario Risiko | Dampak Awal | Probabilitas Awal | Tingkat Risiko Awal | Tindakan Mitigasi / Status | Dampak Sisa | Probabilitas Sisa | Tingkat Risiko Sisa |
|---|---|---|---|---|---|---|---|---|---|
| **RSK-INV-01** | Keamanan / RBAC | Pemanggilan rute faktur tanpa proteksi Controller-Level RBAC | Tinggi | Sedang | **HIGH** | Menerapkan `implements HasMiddleware` pada `InvoiceWebController` dengan 6 permission terperinci | Rendah | Sangat Rendah | **LOW** |
| **RSK-INV-02** | Keamanan / CWE-1236 | Spreadsheet Formula Injection saat ekspor data faktur ke Excel/CSV | Tinggi | Tinggi | **HIGH** | Sanitasi seluruh teks dinamis dengan `CsvSanitizer::sanitize()` | Rendah | Sangat Rendah | **LOW** |
| **RSK-INV-03** | Akuntansi / Fraud | Manipulasi data faktur tanpa jejak audit (*untracked modifications*) | Tinggi | Sedang | **MEDIUM** | Merekam `AuditLog::create` otomatis pada 6 metode mutasi | Rendah | Sangat Rendah | **LOW** |
| **RSK-INV-04** | Keamanan / Multi-Tenant | Akses atau manipulasi faktur lintas bisnis (IDOR) | Kritis | Rendah | **HIGH** | Menegakkan `Context::requireBusiness()` dan global scope `BelongsToBusiness` | Rendah | Sangat Rendah | **LOW** |
| **RSK-INV-05** | Operasional / Stok | Inkonsistensi stok barang akibat pembatalan atau rilis ganda | Tinggi | Sedang | **MEDIUM** | Mengunci transaksi dalam `DB::transaction()` dan memvalidasi `InsufficientStockException` | Rendah | Sangat Rendah | **LOW** |
| **RSK-INV-06** | UI/UX & Brand | Teks antarmuka hardcoded tidak terlokalisasi dalam bahasa Inggris | Rendah | Tinggi | **LOW** | Menstandarisasi 358 pemanggilan i18n via kamus `sales.php` | Sangat Rendah | Sangat Rendah | **INFO** |

---

## 2. Rincian Penanganan Risiko Kritis

### RSK-INV-01: Bypass Otorisasi Akibat Route Misconfiguration
- **Strategi:** Defense-in-Depth. Otorisasi tidak hanya bergantung pada route grouping di `routes/owner.php`, melainkan dideklarasikan langsung di dalam `InvoiceWebController::middleware()`.
- **Hasil:** Jika rute baru didaftarkan atau controller dipanggil dari titik masuk lain, middleware controller otomatis menolak permintaan yang tidak memiliki izin yang sesuai.

### RSK-INV-02: Formula Injection pada Laporan Penjualan Excel/CSV (CWE-1236)
- **Strategi:** Netralisasi karakter formula saat pembuatan dokumen.
- **Hasil:** Nilai input berbahaya seperti `=SUM(...)`, `+CMD`, atau `-DDE` otomatis diubah menjadi `\'=SUM(...)` sehingga aplikasi spreadsheet membacanya murni sebagai teks biasa tanpa mengeksekusinya sebagai macro/formula.

### RSK-INV-03: Fraud Pembayaran & Pembatalan Piutang Fiktif
- **Strategi:** Perekaman jejak audit permanen (*append-only*).
- **Hasil:** Setiap kali pembayaran dicatat, faktur dibatalkan (void), atau draf dihapus, sistem mencatat identitas user yang mengeksekusi, alamat IP, user-agent, nilai tagihan, alasan pembatalan, dan waktu pencatatan.
