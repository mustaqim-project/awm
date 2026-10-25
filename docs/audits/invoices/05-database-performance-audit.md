# Database & Performance Audit - Modul Faktur Penjualan & Piutang

> Modul: Invoices & Accounts Receivable | Tanggal Audit: 2026-10-26 | Target: `resources/views/app/invoices`

## 1. Skema Database & Relasi Tabel
Tabel utama yang menyokong modul faktur penjualan adalah:
1. `invoices`:
   - Primary Key: `id` (UUID v4)
   - Foreign Keys: `business_id`, `customer_id`, `location_id`, `purchase_order_id`, `sales_order_id`
   - Numeric Data: `subtotal`, `discount_amount`, `tax_amount`, `shipping_cost`, `total_amount`, `paid_amount`, `balance_due`, `total_hpp_cost`, `total_gross_profit` (tipe `decimal(15,2)`)
   - Indeks: `business_id`, `customer_id`, `invoice_number`, `status`, `invoice_date`, `due_date`
   - Soft Deletes: Kolom `deleted_at` untuk preservasi audit data historis.
2. `invoice_items`:
   - Primary Key: `id` (UUID v4)
   - Foreign Keys: `invoice_id`, `product_id`, `unit_id`
   - Data Baris: `item_name`, `sku`, `quantity`, `unit_price`, `unit_hpp`, `discount_amount`, `subtotal`
3. `invoice_payments`:
   - Primary Key: `id` (UUID v4)
   - Foreign Keys: `invoice_id`, `business_id`, `created_by` (User)
   - Data Pembayaran: `amount`, `payment_method`, `payment_date`, `reference_number`, `notes`
4. `audit_logs`:
   - Primary Key: `id` (UUID v4)
   - Foreign Keys: `business_id`, `user_id`
   - Data Jejak: `auditable_type`, `auditable_id`, `action`, `risk_level`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`

---

## 2. Analisis Query & Penanganan N+1 Query
- **Eager Loading pada Daftar Faktur (`index`):**
  ```php
  $query = Invoice::with(['customer', 'items', 'payments'])
      ->latest('invoice_date');
  ```
  Menghilangkan N+1 query saat menampilkan nama pelanggan, jumlah item, dan progres pembayaran pada tabel daftar faktur.
- **Eager Loading pada Detail Faktur (`show`):**
  ```php
  $invoice->load(['customer', 'purchaseOrder', 'items.product', 'items.unit', 'payments.creator']);
  ```
  Memuat seluruh relasi secara optimal dalam satu siklus query batch, mencegah query terulang untuk setiap baris barang atau riwayat pembayaran.
- **Paginasi Data:** Query daftar faktur dibatasi dengan paginasi standar 15 record per halaman (`paginate(15)->withQueryString()`) untuk menjamin kecepatan render pada bisnis dengan ribuan faktur.

---

## 3. Transaksi Atomik Database (ACID Compliance)
Operasi mutasi krusial yang melibatkan lebih dari satu tabel dibungkus dalam `DB::transaction()`:
1. **Konfirmasi & Rilis Faktur (`confirmAndRelease`):**
   - Perubahan status faktur ke `sent`/`unpaid`.
   - Pemotongan kuantitas stok di tabel `inventory_items` dan pencatatan riwayat di `stock_movements`.
   - Pencatatan jurnal piutang di `journal_entries` dan `journal_entry_items`.
   - Jika salah satu langkah gagal, seluruh mutasi dibatalkan (*rollback*) secara otomatis.
2. **Pembatalan Faktur (`voidInvoice`):**
   - Pengembalian kuantitas stok gudang.
   - Pembuatan jurnal pembalik (*reversing entries*).
   - Pengubahan status faktur menjadi `void`.
3. **Pencatatan Pembayaran (`recordPayment`):**
   - Penyimpanan baris pembayaran di `invoice_payments`.
   - Rekalkulasi `paid_amount` dan `balance_due` faktur.
   - Pembaruan otomatis status faktur ke `partially_paid` atau `paid`.
   - Pencatatan mutasi kas/bank di buku besar keuangan.
