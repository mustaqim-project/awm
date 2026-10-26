# Residual Risks & Monitoring: Modul Produk

| Risiko Sisa | Level | Rencana Mitigasi Berkelanjutan |
| :--- | :--- | :--- |
| **Penyimpanan Gambar Produk Lokal/S3** | Low | Kapasitas disk tenant dipantau secara otomatis oleh `StorageTrackingService`. Pembersihan gambar fisik dilakukan saat foto dihapus dari galeri. |
| **Kuantitas Bahan Baku Resep BOM Berubah Negatif** | Low | Modul Inventory telah dilengkapi validasi penolakan stok minus jika pengaturan bisnis melarang saldo minus (*Strict Inventory Mode*). |
| **Sinkronisasi Taksonomi Marketplace Eksternal** | Low | Format pemetaan kategori Shopee/Tokopedia disediakan melalui registry lokal `MarketplaceCategoryRegistry` yang dapat diperbarui secara berkala tanpa mengganggu database tenant. |
