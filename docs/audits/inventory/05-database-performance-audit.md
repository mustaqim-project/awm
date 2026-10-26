# Database & Performance Audit: Modul Inventory (Stok, Mutasi, Opname, & Transfer)

## 1. Skema Database & Integritas Data

Tabel-tabel dalam modul Inventory dirancang untuk menjaga integritas data fisik dan akuntansi secara presisi:
1. **`inventory_stocks`**:
   - Master kuantitas stok per lokasi.
   - Dilindungi indeks komposit unik: `unique(['business_id', 'location_id', 'product_id', 'material_id'])`.
   - Mengeliminasi duplikasi data stok untuk produk yang sama pada satu gudang.
2. **`stock_movements` (Immutable Ledger):**
   - Buku besar mutasi dirancang bersifat *append-only* (tidak boleh di-update/dihapus sembarangan).
   - Indeks terpasang pada `business_id`, `location_id`, `product_id`, `material_id`, dan `created_at` untuk mempercepat query filtering dan ekspor laporan mutasi.
3. **`stock_transfers` & `stock_opnames`**:
   - Terhubung dengan relasi *foreign key* berindeks ke `locations`, `users`, dan `business`.

---

## 2. Mitigasi Race Condition & Pessimistic Locking

Dalam operasional bisnis nyata, kasir POS dapat menjual barang bersamaan dengan staf gudang yang mencatat penerimaan atau rekonsiliasi opname:
- **`StockService::getOrCreateStock(..., lock: true)`**:
  - Mengaktifkan `lockForUpdate()` pada level database (SELECT ... FOR UPDATE).
  - Menjamin pembacaan dan pemotongan stok bersifat atomik, mencegah saldo stok minus akibat eksekusi paralel.
- **`DB::transaction`**:
  - Seluruh alur multi-tabel (seperti transfer stok yang memotong gudang asal dan menambah gudang tujuan) dibungkus transaksi database atomik. Jika salah satu operasi gagal, seluruh mutasi dibatalkan otomatis (*rollback*).

---

## 3. Pencegahan N+1 Query & Optimasi Beban Server

Seluruh controller listing menerapkan *eager loading* relasi:
- **`InventoryWebController@stocks`**:
  `->with(['product.outputUnit', 'location'])`
- **`InventoryWebController@movements`**:
  `->with(['product', 'material', 'location', 'user'])`
- **`InventoryWebController@transfers`**:
  `->with(['sourceLocation', 'destinationLocation', 'creator', 'receiver', 'items.product'])`
- **Paginasi:** Seluruh tabel menggunakan `paginate(15)` / `paginate(20)` dengan `withQueryString()`, menjaga *memory footprint* tetap rendah dan waktu respons $\le 100\text{ms}$.
