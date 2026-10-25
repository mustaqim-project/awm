# Pertanyaan Umum (FAQ): Modul Keuangan & Akuntansi (Finance & Accounting)

> Modul: Finance & Accounting | Status: Final | Terverifikasi: 2026-10-25 | Target Pembaca: Pemilik Usaha & Tim Akuntansi

Berikut adalah kumpulan pertanyaan yang sering diajukan mengenai modul Keuangan dan Akuntansi pada platform COOCA.

---

## 1. Standar & Metodologi Akuntansi

### Q: Apa standar akuntansi yang diterapkan pada modul Keuangan COOCA?
**A:** COOCA mengadopsi standar **SAK EMKM** (Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah) yang diterbitkan oleh Ikatan Akuntan Indonesia (IAI). Standar ini menyederhanakan penyajian laporan keuangan yang relevan, andal, dan siap digunakan untuk keperluan pelaporan pajak tahunan (SPT) maupun pengajuan fasilitas perbankan / KUR. Laporan yang dihasilkan mencakup:
- Laporan Posisi Keuangan (Neraca / *Balance Sheet*)
- Laporan Laba Rugi (*Income Statement*)
- Catatan atas Laporan Keuangan sederhana

### Q: Apakah pencatatan akuntansi di COOCA menggunakan metode Kas atau Akrual?
**A:** COOCA menggunakan perpaduan sistem berbasis **Akrual Terotomatisasi**. Transaksi penjualan POS dan pembayaran tunai dicatat seketika, sedangkan transaksi penjualan tempo (kredit B2B) dan pembelian barang dari supplier dicatat sebagai Piutang Usaha (*Accounts Receivable*) dan Hutang Usaha (*Accounts Payable*) yang kemudian diselesaikan saat pelunasan diterima atau dibayarkan.

---

## 2. Otomasi Transaksi & Integrasi POS

### Q: Apakah kasir toko harus memahami akuntansi saat melayani pelanggan?
**A:** **Sama sekali tidak.** Seluruh transaksi yang diproses di kasir POS (baik pembayaran tunai, QRIS, kartu debit, maupun transfer) secara otomatis dipetakan oleh mesin *Auto-Journaling* ke dalam entri debit-kredit di [Jurnal Akuntansi](/finance/journals) tanpa perlu campur tangan manual dari staf kasir.

### Q: Bagaimana cara sistem mencatat transaksi penjualan POS ke dalam pembukuan?
**A:** Setiap transaksi penjualan akan membentuk jurnal otomatis:
- **Debit**: Akun Kas Laci Kasir (untuk uang tunai) atau Kas Rekening Bank / Kliring Payment Gateway (untuk non-tunai).
- **Kredit**: Pendapatan Penjualan Barang / Jasa.
- **Kredit**: Hutang PPN Keluaran (jika bisnis Anda mengaktifkan pengaturan pajak).
- **Debit / Kredit**: Penyesuaian Beban Pokok Penjualan (HPP) dan Persediaan Barang secara otomatis (metode persediaan perpetual).

---

## 3. Keamanan Data & Proteksi Spreadsheet

### Q: Bagaimana COOCA melindungi data ekspor Excel dari risiko manipulasi formula berbahaya?
**A:** Seluruh data yang diekspor dari sistem (misalnya daftar bagan akun di [Bagan Akun (COA)](/finance/chart-of-accounts), piutang di [Piutang Usaha (AR)](/finance/receivables), dan hutang di [Hutang Usaha (AP)](/finance/payables)) diproses melalui mesin sanitasi khusus (`CsvSanitizer`). Mesin ini secara otomatis mendeteksi dan menetralkan karakter berbahaya (`=`, `+`, `-`, `@`, tab, atau newline) pada awal teks dengan menambahkan tanda kutip tunggal (`'`). Hal ini memastikan file aman dibuka di Microsoft Excel, Google Sheets, maupun LibreOffice tanpa memicu eksekusi *Formula / CSV Injection* (CWE-1236).

### Q: Mengapa kasir saya tidak bisa mengekspor laporan Neraca ke Excel?
**A:** Menu ekspor laporan keuangan dilindungi oleh hak akses ganda: Controller-Level RBAC (`accounting.view`) serta fitur langganan (*Entitlement check*: `export`). Staf operasional biasa dibatasi agar tidak dapat mengunduh seluruh data neraca atau histori keuangan tanpa otorisasi resmi dari pemilik bisnis (*Business Owner*).

---

## 4. Multi-Cabang (Multi-Outlet) & Isolasi Bisnis

### Q: Bisakah saya memisahkan buku kas untuk tiap cabang/outlet yang berbeda?
**A:** **Bisa.** Saat membuat akun kas di [Buku Kas & Bank](/finance/cash-bank), Anda dapat menentukan apakah akun kas tersebut berstatus *Toko Spesifik* (terikat pada satu outlet tertentu, misal: *Kas Laci Outlet Sudirman*) atau *Kas Pusat / Global* (misal: *Rekening Utama BCA Operasional*). Kasir di outlet hanya akan melihat saldo dan riwayat kas yang ditugaskan untuk tokonya.

### Q: Apakah data keuangan bisnis saya terjamin tidak bisa dilihat oleh pemilik toko lain di COOCA?
**A:** Sangat terjamin. Seluruh model data (Akun Kas, Bagan Akun, Jurnal, Piutang, Hutang, Rekonsiliasi) menerapkan *Global Multi-Tenant Scope* (`BelongsToBusiness`). Setiap kueri database secara otomatis dikunci dengan `business_id` tenant yang sedang aktif, sehingga upaya akses silang antar bisnis akan menghasilkan penolakan otorisasi (HTTP 403 / 404).

---

## 5. Hubungan Antar Modul

### Q: Apa perbedaan antara menu Buku Kas & Bank dengan Buku Besar Umum?
**A:** 
- [Buku Kas & Bank](/finance/cash-bank): Berfokus pada pengelolaan fisik uang tunai dan saldo bank operasional harian (manajemen likuiditas kasir, pencairan payout, mutasi masuk/keluar).
- [Buku Besar Umum](/finance/general-ledger): Berfokus pada seluruh klasifikasi akun akuntansi (Aset, Kewajiban, Modal, Pendapatan, dan Beban) untuk keperluan audit, analisis akuntansi, dan penyusunan neraca saldo.

---

## 6. Navigasi Dokumen Terkait

- [Kembali ke Panduan Utama Modul Keuangan](index.md)
- [Lihat Panduan Troubleshooting Keuangan](troubleshooting.md)
