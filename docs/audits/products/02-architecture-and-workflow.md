# Architecture & Deep Business Workflow Audit: Modul Produk

| Aspek | Spesifikasi |
| :--- | :--- |
| **Fokus Audit** | Hubungan Hulu Rantai Pasok (Supply Chain Upstream), Arsitektur HPP (BOM), & Alur Ekosistem Transaksi |
| **Modul Terkait** | Products $\rightarrow$ Purchasing (Pengadaan) $\rightarrow$ Inventory (Gudang/Stok) $\rightarrow$ Point of Sale (POS) |
| **Status Verifikasi** | **Verified & Compliant** |

---

## 1. Arsitektur Rantai Pasok: Posisi Hulu Modul Produk
Modul Produk memegang peranan krusial sebagai **Master Single Source of Truth** untuk seluruh transaksi hilir. Diagram aliran data operasional di bawah ini mengilustrasikan keterkaitan produk dengan workflow bisnis:

```
+-----------------------------------------------------------------------------------+
|                            HULU (KATALOG & FORMULASI)                            |
|  [Modul Produk]                                                                  |
|   ├── Master SKU & Barcode                                                        |
|   ├── Unit Konversi (Base Unit -> Purchase/Sale Unit)                             |
|   ├── Resep BOM (Bill of Materials) & Costing HPP                                  |
|   └── Multi-Channel Matrix (POS Kasir, Online Store, Marketplace, Branch Pricing)  |
+-----------------------------------------------------------------------------------+
                                   │
                                   ▼
+-----------------------------------------------------------------------------------+
|                        PENGADAAN (PROCURE-TO-PAY FLOW)                            |
|  [Modul Purchasing]                                                              |
|   ├── Purchase Request (PR) -> Referensi Produk / Bahan Baku                      |
|   ├── Purchase Order (PO) -> Harga Beli Pemasok (Supplier Price)                  |
|   └── Penerimaan Barang (GRN) -> Validasi Satuan Beli & Update Moving Average Cost |
+-----------------------------------------------------------------------------------+
                                   │
                                   ▼
+-----------------------------------------------------------------------------------+
|                         GUDANG & STOK (INVENTORY FLOW)                            |
|  [Modul Inventory]                                                                |
|   ├── Stock In (Penerimaan PO / Penyesuaian Masuk)                                |
|   ├── Stock Out (Penjualan Kasir / Produksi Pemakaian Resep BOM)                  |
|   ├── Stock Transfer (Antar Cabang / Gudang Konsolidasi)                          |
|   └── Stock Opname (Penyesuaian Fisik vs Sistem berbasis SKU & Lokasi)            |
+-----------------------------------------------------------------------------------+
                                   │
                                   ▼
+-----------------------------------------------------------------------------------+
|                        PENJUALAN & OMSET (OMNICHANNEL POS)                        |
|  [Modul Penjualan & Kasir]                                                        |
|   ├── Kasir POS: Verifikasi `show_in_pos`, Varian, Modifiers, & Branch Pricing   |
|   └── E-Commerce / Marketplace: Sinkronisasi Stok Real-Time & Atribut Toko       |
+-----------------------------------------------------------------------------------+
```

---

## 2. Audit Workflow Mendalam (Deep Dive Workflows)

### Workflow 1: Procure-to-Pay (Katalog Hulu ke Pembelian)
1. **Definisi Satuan Beli vs Satuan Jual (UOM Conversion):**
   - Di modul produk, setiap item terikat pada `output_unit_id` (misalnya: Pcs, Botol, Porsi).
   - Saat item tersebut dibeli di modul Purchasing via Purchase Order (PO), pengadaan seringkali dalam satuan grosir (misal: Dus, Box, Jeriken).
   - *Verifikasi Integritas:* Modul produk menyediakan relasi ke tabel `units` dengan rasio konversi dasar (`is_base`, `conversion_rate`), menjamin tidak terjadi distorsi kuantitas saat penerimaan barang (GRN).
2. **Kalkulasi Estimasi HPP vs Actual Landed Cost:**
   - Modul produk menyimpan `base_cost` sebagai patokan estimasi modal.
   - Saat barang diterima dari supplier di Purchasing, sistem secara otomatis memperbarui HPP aktual berdasarkan *Moving Average Cost* atau *FIFO*, memperbarui baseline margin keuntungan produk.

### Workflow 2: Resep BOM (Bill of Materials) & Auto-Deduction Stok
1. **Formulasi Resep Manufaktur/F&B:**
   - Untuk bisnis Restoran, Kafe, Bakery, dan Manufaktur UMKM, produk jadi (misal: *Kopi Susu Gula Aren*) tidak memiliki stok fisik langsung, melainkan dirakit dari bahan baku (`materials`: Kopi Bubuk, Susu UHT, Gula Aren Cair, Cup, Sedotan).
   - `BomHeader` dan `BomItem` menyimpan takaran tepat per porsi beserta persentase *waste margin* (penyusutan/tumpah).
2. **Trigger Pengurangan Stok Otomatis:**
   - Saat kasir menjual 1 produk jadi di POS, trigger inventori membaca relasi BOM dan memotong kuantitas masing-masing bahan baku secara proporsional.
   - *Audit Keamanan Data:* Penghapusan atau pengubahan resep BOM dilindungi oleh middleware `require.permission:products.edit` dan entitlement fitur `entitlement:recipe`. IDOR dicegah melalui verifikasi kepemilikan tenant pada `costModel->business_id`.

### Workflow 3: Paket Bundel Dinamis (Product Bundles Explosion)
- Produk bertipe `is_bundle = true` (misal: *Paket Sarapan Hemat* = Nasi Goreng + Teh Manis).
- Alur transaksi:
  1. Penjualan paket bundel tidak memotong stok bundel abstrak, melainkan meledakkan item anak (*child items*) di tabel `product_bundle_items`.
  2. Sistem memeriksa ketersediaan stok masing-masing produk anak di cabang tempat transaksi berlangsung.
  3. Transaksi hanya diizinkan apabila seluruh item anak memiliki saldo stok mencukupi.

### Workflow 4: Pricing Matrix Multi-Cabang & Otorisasi
- **Kasus Bisnis:** Harga jual suatu produk di cabang Mall Senayan dapat berbeda dengan cabang Depok karena perbedaan sewa dan segmen pasar.
- **Implementasi:**
  - Tabel `branch_product_prices` menyimpan `[business_id, product_id, location_id, price, cost_price, is_available]`.
  - Jika tidak ada override harga di tabel cabang, POS secara otomatis melakukan fallback ke `products.selling_price`.
- **Otorisasi & Audit Trail:**
  - Perubahan harga multi-cabang dicatat dalam `AuditLog` dengan tingkat risiko `MEDIUM`, mencegah manipulasi harga sepihak oleh kasir cabang tanpa persetujuan kantor pusat.

### Workflow 5: Kesiapan Stock Opname & SKU Integrity
- Setiap produk wajib memiliki SKU (`code`) yang unik dalam lingkup bisnis (`business_id`).
- Keunikan SKU ini menjadi kunci indeks dalam modul **Inventory** saat staf gudang melakukan:
  1. Barcode scanning saat Stock Opname.
  2. Verifikasi selisih fisik vs sistem (*variance calculation*).
  3. Pembuatan berita acara penyesuaian stok (*Stock Adjustment Draft*).
