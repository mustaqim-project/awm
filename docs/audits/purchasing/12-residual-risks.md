# Residual Risks: Modul Purchasing (Pengadaan)

| Risiko Tersisa | Tingkat Risiko | Dampak Potensial | Mitigasi Yang Telah & Harus Diterapkan |
| :--- | :---: | :--- | :--- |
| **Fluktuasi Harga Beli Supplier terhadap HPP / COGS** | Medium | Perbedaan harga faktur supplier dengan estimasi di PO dapat mendistorsi margin kotor jika tidak dimutakhirkan. | Sistem secara otomatis mengkalkulasi ulang *moving average cost* saat penerimaan barang dicatat. Disarankan mereview laporan valuasi stok secara berkala. |
| **Over-Receiving (Penerimaan Melebihi PO)** | Low | Staf gudang menerima barang melebihi kuantitas yang dipesan dari pemasok. | Validasi pada `GoodsReceiptService` secara ketat membatasi total penerimaan kumulatif agar tidak melebihi sisa *unreceived quantity* PO tanpa persetujuan revisi PO. |
| **Keterlambatan Pengiriman Supplier (*Lead Time*)** | Low | Stok gudang menipis karena keterlambatan pengiriman pihak ketiga. | Indikator status PO menampilkan tanggal estimasi kedatangan barang (*expected arrival date*) pada antarmuka dashboard untuk peringatan dini. |
| **Human Error Input Faktur Tagihan** | Low | Kesalahan pengetikan nomor invoice eksternal supplier atau jatuh tempo. | Validasi nomor faktur unik per supplier dan konfirmasi modal sebelum pelunasan pembayaran dicatat. |
