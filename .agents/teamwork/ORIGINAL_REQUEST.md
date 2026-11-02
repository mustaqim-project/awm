# Original User Request

## Approved Task Prompt — 2026-11-02T08:36:08Z

Implementasikan Fase 2 (Logistik & Gudang Toko) dan Fase 3 (Customer Piutang & Omnichannel Digital) dari rencana integrasi fitur di C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\acf5365f-47d8-4edf-9b08-e7eb3389da96\plan_core_to_mobile_features.md, secara terintegrasi antara backend cooca_core dan mobile cooca_my_own untuk penggunaan production.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

Dokumen acuan: C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\acf5365f-47d8-4edf-9b08-e7eb3389da96\plan_core_to_mobile_features.md
Repositori terkait:
- Backend: c:\laragon\www\cooca_core
- Mobile: c:\laragon\www\cooca_my_own

## Requirements

### R1. FASE 2: Logistik & Gudang Toko (Penerimaan Barang & Mutasi Cabang)
Implementasikan alur logistik dan gudang toko terintegrasi pada backend `c:\laragon\www\cooca_core` dan mobile `c:\laragon\www\cooca_my_own`:
- Penerimaan Barang Supplier (Goods Receipt / PO Receive via Kamera Scan):
  - Backend `cooca_core`: Buat endpoint API `GET /api/v1/inventory/purchase-orders/pending`, `GET /api/v1/inventory/purchase-orders/{id}`, dan `POST /api/v1/inventory/goods-receipts` (Sanctum auth, update PO received qty, mutasi stok masuk, pencatatan batch & exp date).
  - Mobile `cooca_my_own`: Buat layar `GoodsReceiptScreen` (`lib/screens/inventory/goods_receipt_screen.dart`) dengan pemindai barcode kamera HP, input qty aktual diterima vs rusak, input nomor batch/lot & expired date (khusus apotek/fnb), dan navigasi dari inventory screen.
- Mutasi / Transfer Stok Antar Cabang:
  - Backend `cooca_core`: Buat endpoint API `GET /api/v1/inventory/transfers`, `POST /api/v1/inventory/transfers/dispatch`, dan `POST /api/v1/inventory/transfers/{id}/receive` (Sanctum auth, mutasi in-transit stok dan penambahan stok cabang tujuan).
  - Mobile `cooca_my_own`: Buat layar `StockTransferScreen` (`lib/screens/inventory/stock_transfer_screen.dart`) dengan 2 tab: Tab Kirim Barang (Dispatch) dan Tab Terima Barang (Inbound Scan).

### R2. FASE 3: Customer Piutang & Omnichannel Digital
Implementasikan alur customer piutang dan omnichannel digital terintegrasi pada backend `c:\laragon\www\cooca_core` dan mobile `c:\laragon\www\cooca_my_own`:
- Buku Piutang & Kasbon Pelanggan (Accounts Receivable / Kasbon Tracking):
  - Backend `cooca_core`: Buat endpoint API `GET /api/v1/customers/{id}/debts` dan `POST /api/v1/customers/{id}/repay-debt` (Sanctum auth, pencatatan transaksi kas masuk cicilan piutang dan pembaruan sisa saldo hutang pelanggan).
  - Mobile `cooca_my_own`: Di `pos_payment_sheet.dart` tambahkan opsi metode bayar "Kasbon / Tempo" saat pelanggan terdaftar dipilih; modal `PosCustomerDebtSheet` (`lib/screens/pos/pos_customer_debt_sheet.dart`) untuk melihat total piutang, riwayat bon, dan input cicilan bayar hutang.
- Kirim Struk Digital & Bukti Bayar via WhatsApp Resmi:
  - Backend `cooca_core`: Buat endpoint API `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt`.
  - Mobile `cooca_my_own`: Tombol kirim struk ke WhatsApp pelanggan di `pos_receipt_screen.dart` dengan fallback direct WhatsApp intent (`url_launcher` `whatsapp://send?phone=...&text=...`).
- Notifikasi & Manajemen Pesanan Toko Online & Standee QR (Storefront Orders):
  - Backend `cooca_core`: Sediakan API pesanan online masuk dari web toko/storefront dan scan QR meja.
  - Mobile `cooca_my_own`: Listener pesanan online di `MerchantState`, dialog popup / badge notifikasi pesanan masuk di `dashboard_screen.dart` dan `pos_screen.dart`, serta tombol aksi: Terima Pesanan -> Cetak KDS -> Panggil Kurir.

### R3. Automated Testing and Verification
Implementasikan test suite otomatis pada masing-masing repositori menggunakan tool bawaan:
- Backend `cooca_core`: PHPUnit test suites yang mencakup Goods Receipt, Stock Transfer, Customer Debt Repay, WhatsApp Receipt, dan Storefront Orders.
- Mobile `cooca_my_own`: Widget & unit tests di `test/`, serta pastikan `flutter analyze` (0 issues) dan `flutter test` lolos 100%.

## Acceptance Criteria

### Logistik & Gudang Toko (Fase 2)
- [ ] Endpoint `GET /api/v1/inventory/purchase-orders/pending`, `GET /api/v1/inventory/purchase-orders/{id}`, dan `POST /api/v1/inventory/goods-receipts` di `cooca_core` berfungsi dengan otentikasi Sanctum, memperbarui status/qty PO, merekam mutasi stok masuk, dan mencatat batch/exp date
- [ ] Layar `GoodsReceiptScreen` di `cooca_my_own` memungkinkan pemindaian barcode kamera, input kuantitas aktual diterima vs rusak, pencatatan batch/exp date, serta navigasi dari inventory screen
- [ ] Endpoint transfer stok (`GET /api/v1/inventory/transfers`, `POST /api/v1/inventory/transfers/dispatch`, `POST /api/v1/inventory/transfers/{id}/receive`) di `cooca_core` mengelola mutasi stok in-transit dan penambahan stok cabang tujuan
- [ ] Layar `StockTransferScreen` di `cooca_my_own` menyediakan tab dispatch (kirim barang) dan inbound scan (terima barang) yang terhubung ke backend

### Customer Piutang & Omnichannel Digital (Fase 3)
- [ ] Endpoint `GET /api/v1/customers/{id}/debts` dan `POST /api/v1/customers/{id}/repay-debt` di `cooca_core` mengelola saldo piutang dan merekam transaksi kas masuk cicilan piutang
- [ ] Pilihan metode bayar "Kasbon / Tempo" aktif di `pos_payment_sheet.dart` saat pelanggan terdaftar dipilih, dan modal `PosCustomerDebtSheet` di `cooca_my_own` menampilkan riwayat bon serta memproses cicilan piutang
- [ ] Endpoint `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt` di `cooca_core` mengirimkan struk via WhatsApp, dan tombol kirim struk di `pos_receipt_screen.dart` mendukung pengiriman via API dengan fallback direct WhatsApp intent
- [ ] Backend menyediakan API pesanan masuk storefront/QR meja, dan aplikasi mobile mendengarkan pembaruan di `MerchantState`, memunculkan dialog popup / badge notifikasi di `dashboard_screen.dart` dan `pos_screen.dart`, serta memicu aksi terima pesanan, cetak KDS, dan panggil kurir

### Quality Gates & Automated Verification
- [ ] PHPUnit test suites di `cooca_core` untuk seluruh endpoint Fase 2 dan Fase 3 lolos 100%
- [ ] Widget dan unit tests di `cooca_my_own` lolos pengujian tanpa kegagalan
- [ ] Perintah `flutter analyze` pada `cooca_my_own` menghasilkan 0 issues/errors
