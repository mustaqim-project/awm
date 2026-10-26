# Risk Register: Modul Inventory (Stok, Mutasi, Opname, & Transfer)

| Risk ID | Deskripsi Risiko | Kategori | Tingkat Keparahan | Probabilitas | Rencana Mitigasi & Pengendalian Sistem |
| :--- | :--- | :---: | :---: | :---: | :--- |
| **RSK-INV-01** | **Selisih Stok Fisik & Sistem Akibat Pencurian / Kerusakan Tersembunyi** | Fraud & Operasional | **High** | Medium | COOCA menerapkan Maker-Checker otomatis: penyesuaian stok bernilai besar wajib melewati approval Owner/Supervisor (`ApprovalRule`). Rekonsiliasi opname mencatat selisih akuntansi secara otomatis. |
| **RSK-INV-02** | **Kehilangan Barang Saat Transfer Antar-Gudang (*In-Transit Loss*)** | Rantai Pasok | **Medium** | Low | Sistem memisahkan status `pending` dan `received`. Stok belum masuk ke gudang tujuan sebelum staf penerima mengklik konfirmasi terima fisik di aplikasi. |
| **RSK-INV-03** | **Race Condition Penjualan POS Saat Rekonsiliasi Opname Berjalan** | Database & Concurrency | **Medium** | Low | `StockService` menerapkan penguncian pesimis (`lockForUpdate()`) dan transaksi atomik `DB::transaction()`. |
| **RSK-INV-04** | **Stok Menipis / Kehabisan Barang (*Stockout*)** | Bisnis | **Medium** | Medium | Dashboard stok dilengkapi indikator visual *Aman* (hijau) vs *Menipis* (oranye) serta kartu metrik peringatan stok di bawah batas minimal (`min_stock`). |
| **RSK-INV-05** | **Kesalahan Format Saat Impor Data Hitung Opname Excel** | Human Error | **Low** | Medium | Fitur impor menyediakan tahap *Preview Impor* (`previewOpnameImport`) untuk memvalidasi SKU, nama produk, dan angka fisik sebelum benar-benar dieksekusi (`executeOpnameImport`). |
