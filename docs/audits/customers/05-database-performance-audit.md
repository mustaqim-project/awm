# Database and Performance Audit - customers

> Modul: Pelanggan (Customers Master Directory) | Tanggal: 2026-10-25

## 1. Analisis Skema Database & Relasi Model

| Tabel | Primary Key | Foreign Keys / Scopes | Keterangan Indeks & Optimasi |
| :--- | :--- | :--- | :--- |
| `customers` | UUID (`id`) | `business_id` | Menggunakan compound index `(business_id, phone)` dan unique index `(business_id, code)` untuk validasi instan. Dilengkapi `deleted_at` untuk SoftDeletes. |
| `invoices` | UUID (`id`) | `customer_id`, `business_id` | Terindeks pada `(business_id, customer_id, status)` untuk perhitungan riwayat pesanan. |
| `pos_orders` | UUID (`id`) | `customer_id`, `business_id` | Menghubungkan transaksi kasir POS dengan pelanggan. |
| `audit_logs` | UUID (`id`) | `business_id`, `(auditable_type, auditable_id)` | Polimorfik index untuk penelusuran audit trail pembuatan, pembaruan, dan penghapusan pelanggan. |

## 2. Analisis Query & Penanganan N+1
- Pada `CustomerWebController::index`:
  - Menggunakan eager count `withCount(['invoices', 'purchaseOrders', 'posOrders'])` dalam satu query terpadu untuk mencegah query N+1 pada tabel baris pelanggan.
  - Paginasi dibatasi `paginate(15)` dengan query string terpelihara (`withQueryString()`).
  - Agregasi metrik hero bento (count, avg, sum) dieksekusi langsung di level SQL engine dengan filter `business_id`.
- Pada `CustomerWebController::export`:
  - Menghindari pemuatan data yang berlebihan ke memori dengan streaming langsung berkas spreadsheet melalui `StreamedResponse`.

## 3. Integritas Transaksional & Soft Deletes
- Model `Customer` menggunakan trait `SoftDeletes`. Saat dihapus, rekaman tidak langsung hilang dari database fisik melainkan kolom `deleted_at` terisi tanggal penghapusan, menjaga integritas historis faktur penjualan lama.
