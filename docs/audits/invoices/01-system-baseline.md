# System Baseline - Modul Faktur Penjualan & Piutang (Invoices)

> Modul: Invoices & Accounts Receivable | Tanggal Audit: 2026-10-26 | Target: `resources/views/app/invoices`

## 1. Inventaris Komponen & Rute
Modul Faktur Penjualan dikelola di bawah grup rute bisnis pemilik (`routes/owner.php`):

| Method | URI Path | Nama Rute | Controller Handler | Permission RBAC |
|---|---|---|---|---|
| GET | `/invoices` | `invoices.index` | `InvoiceWebController@index` | `invoices.view` |
| GET | `/invoices/create` | `invoices.create` | `InvoiceWebController@create` | `invoices.create` |
| POST | `/invoices` | `invoices.store` | `InvoiceWebController@store` | `invoices.create` |
| GET | `/invoices/export-excel` | `invoices.export-excel` | `InvoiceWebController@exportExcel` | `invoices.export` |
| GET | `/invoices/{invoice}` | `invoices.show` | `InvoiceWebController@show` | `invoices.view` |
| GET | `/invoices/{invoice}/edit` | `invoices.edit` | `InvoiceWebController@edit` | `invoices.edit` |
| PUT | `/invoices/{invoice}` | `invoices.update` | `InvoiceWebController@update` | `invoices.edit` |
| POST | `/invoices/{invoice}/payments` | `invoices.payments.store` | `InvoiceWebController@recordPayment` | `invoices.record_payment` |
| POST | `/invoices/{invoice}/confirm` | `invoices.confirm` | `InvoiceWebController@confirm` | `invoices.create` |
| POST | `/invoices/{invoice}/remind` | `invoices.remind` | `InvoiceWebController@sendTermReminder` | `invoices.view` |
| POST | `/invoices/{invoice}/void` | `invoices.void` | `InvoiceWebController@void` | `invoices.edit` |
| DELETE | `/invoices/{invoice}` | `invoices.destroy` | `InvoiceWebController@destroy` | `invoices.delete` |
| GET | `/invoices/{invoice}/print` | `invoices.print` | `InvoiceWebController@print` | `invoices.view` |
| GET | `/invoices/{invoice}/pdf` | `invoices.pdf` | `InvoiceWebController@pdf` | `invoices.view` |

## 2. Struktur Berkas Tampilan (Blade Views)
Direktori: `resources/views/app/invoices/`
1. `index.blade.php`: Halaman utama daftar faktur penjualan, kartu Bento ringkasan keuangan (Total Difakturkan, Total Diterima, Sisa Piutang, Estimasi Laba Kotor), filter status faktur, pencarian pelanggan/nomor, dan tabel interaktif dengan menu aksi.
2. `create.blade.php`: Formulir pembuatan faktur penjualan. Mendukung pemilihan pelanggan, pemilihan lokasi gudang asal, konversi dari Purchase Order pelanggan, atau integrasi kalkulator HPP, tabel item dinamis Alpine.js dengan kalkulasi pajak, diskon, dan ongkir otomatis.
3. `edit.blade.php`: Formulir penyuntingan draf faktur yang belum berstatus paid/void.
4. `show.blade.php`: Tampilan detail faktur lengkap dengan informasi pengirim, penerima, nomor PO, timeline status, modal pencatatan pelunasan, riwayat pembayaran, tombol rilis/konfirmasi stok, pembatalan (void), dan dialog hapus draf.
5. `print.blade.php`: Tampilan pratinjau cetak browser faktur penjualan komersial A4.

## 3. Entitas & Model Terkait
- `App\Models\Invoice`: Model utama faktur penjualan (menggunakan traits `Auditable`, `BelongsToBusiness`, `HasUuid`, `SoftDeletes`).
- `App\Models\InvoiceItem`: Rincian baris barang/jasa dalam faktur (produk, nama item, kuantitas, harga satuan, HPP, diskon).
- `App\Models\InvoicePayment`: Riwayat pembayaran dan pelunasan bertahap atas suatu faktur.
- `App\Models\Customer`: Pihak pembeli/pelanggan B2B dengan informasi limit kredit dan termin pembayaran standar.
- `App\Models\PurchaseOrder`: Pesanan pembelian dari pelanggan yang dapat dikonversi menjadi faktur penjualan.
- `App\Models\AuditLog`: Log audit permanen (*append-only*) untuk jejak kepatuhan dan forensik keamanan.

## 4. Dependensi Layanan Domain
- `App\Domain\Commerce\InvoiceService`: Orkestrator logika bisnis faktur, pembuatan dari produk/PO, kalkulasi pajak/diskon, konfirmasi rilis stok barang, dan prosedur pembatalan (*voiding*).
- `App\Domain\Commerce\InvoiceNumberGenerator`: Generator format nomor faktur resmi berurutan per bisnis.
- `App\Exports\InvoiceReportExport`: Generator laporan komersial Microsoft Excel (.xlsx) dengan proteksi Formula Injection.
- `App\Support\Security\CsvSanitizer`: Utilitas pengamanan injeksi formula spreadsheet (CWE-1236).
