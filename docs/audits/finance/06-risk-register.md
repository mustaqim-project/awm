# Risk Register - finance

> Modul: Keuangan & Akuntansi Korporasi | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Matriks Registrasi Risiko (Risk Register)

| ID Risiko | Kategori | Deskripsi Risiko | Severity Awal | Mitigasi yang Diterapkan | Severity Akhir | Status |
|---|---|---|---|---|---|---|
| **RSK-FIN-01** | Keamanan | **Formula Injection (CWE-1236) pada Ekspor Spreadsheet**: Karakter berbahaya (`=`, `+`, `-`, `@`) dieksekusi oleh Microsoft Excel saat pengguna membuka data ekspor COA, Piutang, atau Hutang. | **High** | Sanitasi seluruh teks kolom via `CsvSanitizer::sanitize()` di `DataExchangeService.php`. | **Insignificant** | **Terselesaikan** |
| **RSK-FIN-02** | Otorisasi | **Bypass Otorisasi via Missing Controller RBAC**: Akses langsung ke controller tanpa melewati route middleware group dapat membuka akses ke data keuangan sensitif. | **High** | Menerapkan `implements HasMiddleware` pada 7 Controller dengan permission granular (`accounting.view`, `finance.cash_bank`, dll). | **Insignificant** | **Terselesaikan** |
| **RSK-FIN-03** | Kepatuhan | **Ketiadaan Jejak Audit pada Perubahan Master Akun**: Perubahan akun COA dan kas tidak tercatat sehingga menyulitkan audit investigasi fraud internal. | **Medium** | Integrasi `AuditLog::create` otomatis pada penambahan, pengubahan, dan penghapusan akun serta beban. | **Insignificant** | **Terselesaikan** |
| **RSK-FIN-04** | Integritas | **Inkonsistensi Saldo Buku Kas Akibat Transaksi Parsial**: Kegagalan jaringan di tengah proses transfer kas menyebabkan satu akun terpotong tanpa akun tujuan bertambah. | **Medium** | Mewajibkan pembungkusan seluruh mutasi ganda ke dalam blok atomik `DB::transaction()`. | **Insignificant** | **Terselesaikan** |
| **RSK-FIN-05** | Privasi | **Akses Silang Akun Keuangan Antar Bisnis (IDOR)**: Pengguna memanipulasi parameter ID akun kas milik tenant lain. | **High** | Penegakan model global scope `BelongsToBusiness` pada seluruh model akuntansi dan kas. | **Insignificant** | **Terselesaikan** |
| **RSK-FIN-06** | Operasional | **Variasi Format Kolom CSV Rekening Koran Bank**: Perubahan header kolom pada internet banking pihak ketiga menyebabkan kegagalan parsing rekonsiliasi. | **Low** | Validasi header kolom yang fleksibel dan pesan kesalahan instruktif pada antarmuka rekonsiliasi. | **Low** | **Terkontrol** |
