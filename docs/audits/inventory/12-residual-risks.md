# Residual Risks: Modul Inventory (Stok, Mutasi, Opname, & Transfer)

| Risiko Tersisa | Tingkat Risiko | Dampak Potensial | Mitigasi Yang Telah & Harus Diterapkan |
| :--- | :---: | :--- | :--- |
| **Pencatatan Fisik Opname Saat Transaksi Kasir Sedang Aktif** | Medium | Terjadi selisih semu jika kasir menjual barang yang sedang dihitung staf gudang. | SOP Rekomendasi: Lakukan penghitungan fisik di luar jam operasional toko (sebelum buka / sesudah tutup toko), atau gunakan fitur lembar cetak hitung fisik (*blank sheet*). |
| **Keterlambatan Konfirmasi Penerimaan Transfer Stok** | Low | Barang telah tiba di outlet cabang namun staf kasir belum mengklik "Konfirmasi Terima", sehingga stok kasir tampak kosong. | Antarmuka transfer menampilkan status badge oranye yang mencolok untuk transfer yang belum dikonfirmasi agar supervisor cabang segera menindaklanjuti. |
| **Kuantitas Negatif Akibat Penjualan Offline yang Belum Tersinkron** | Low | Penjualan offline di kasir dapat menjual barang yang secara fisik ada tetapi di server belum tercatat restoknya. | `StockService` mendukung flag toleransi offline dengan rekonsiliasi otomatis saat sinkronisasi online terjadi (*catch-up ledger*). |
