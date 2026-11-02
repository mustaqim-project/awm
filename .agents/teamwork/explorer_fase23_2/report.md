# Laporan Investigasi Arsitektur Mobile Flutter (cooca_my_own)
## Fase 2 (Logistik & Gudang Toko) & Fase 3 (Customer Piutang & Omnichannel Digital)

**Tanggal:** 2 November 2026  
**Peneliti:** Explorer 2 (Mobile Architecture Explorer)  
**Target Repositori:** `c:\laragon\www\cooca_my_own`  
**Backend Terkait:** `c:\laragon\www\cooca_core`  
**Standar Desain:** Apple Human Interface Guidelines (Apple HIG) & Zero Feature Leakage (20-25 Sektor Industri UMKM)

---

## 1. Executive Summary

Aplikasi mobile `cooca_my_own` adalah aplikasi POS & Backoffice Multi-Tenant berbasis **Flutter 3.47.2 / Dart 3.13.2** dengan arsitektur UI murni Apple HIG (`ios_tokens.dart`, `ios_adaptive.dart`, `Cupertino` widgets) dan manajemen state reaktif berbasis `MerchantState` (`ChangeNotifier`).

Berdasarkan investigasi menyeluruh:
1. **Kesehatan Baseline:** Sangat prima. Perintah `flutter analyze` menghasilkan **0 issues (No issues found)** dan `flutter test` mencatat **33 dari 33 unit & widget tests lulus 100%**.
2. **Kesiapan Dependensi:** Paket kamera barcode `mobile_scanner: ^7.4.2` dan `camera: ^0.12.1` sudah terpasang dan berfungsi baik pada POS. Paket `url_launcher` belum terdaftar di `pubspec.yaml` namun uji dry-run membuktikan `url_launcher: ^6.3.3` kompatibel tanpa konflik versi dengan seluruh dependensi eksisting.
3. **Kesiapan Fase 2:** Struktur modular di `lib/screens/inventory/` siap menerima `goods_receipt_screen.dart` dan `stock_transfer_screen.dart`, didukung backend service `InventoryService` yang telah menangani otentikasi Sanctum dan multi-warehouse.
4. **Kesiapan Fase 3:** Skema pembayaran kasbon telah memiliki fondasi awal di `pos_payment_sheet.dart` (`customer_credit`), struk cetak siap diperluas dengan WhatsApp intent fallback di `pos_receipt_screen.dart`, dan indikator pesanan online (`pendingOnlineOrdersCount`) sudah tercatat di `MerchantState` tinggal diaktifkan listener dan dialog aksinya.

---

## 2. Dependensi & Izin Sistem (`pubspec.yaml` & Android/iOS)

### 2.1. Audit Dependensi Eksisting
File `c:\laragon\www\cooca_my_own\pubspec.yaml` telah memiliki dependensi utama:
- `mobile_scanner: ^7.4.2`: Pemindai barcode live kamera berkecepatan tinggi, sudah digunakan di `pos_barcode_scanner_sheet.dart`.
- `camera: ^0.12.1`: Pengambilan foto bukti pengeluaran petty cash di `pos_expense_modal.dart`.
- `http: ^1.2.2`: Client HTTP inti untuk komunikasi API Sanctum di `ApiClient`.
- `shared_preferences: ^2.3.2`: Penyimpanan token auth, base URL, dan konfigurasi lokal.
- `cupertino_icons: ^1.0.8`: Ikonografi standar Apple iOS.
- `intl: ^0.20.3`: Pemformatan mata uang Rupiah dan tanggal.
- `print_bluetooth_thermal: ^1.2.5`, `esc_pos_utils_plus: ^2.0.4`, `printing: ^5.15.1`, `pdf: ^3.13.1`: Pencetakan struk kasir & KDS dapur.

### 2.2. Kebutuhan Paket Baru: `url_launcher`
- **Kebutuhan:** Membuka intent WhatsApp langsung (`whatsapp://send?phone=...&text=...` atau web fallback `https://api.whatsapp.com/send?...`) saat kasir menekan tombol kirim struk WA.
- **Hasil Verifikasi Dry-Run:**
  Perintah `dart pub add --dry-run url_launcher` berhasil menyelesaikan resolusi dependensi tanpa error:
  `+ url_launcher 6.3.3` serta sub-platform interfaces (`url_launcher_android`, `url_launcher_ios`, dsb).
- **Rekomendasi Tindakan:**
  Tambahkan `url_launcher: ^6.3.3` ke `dependencies` di `pubspec.yaml`.

### 2.3. Android Manifest & iOS Info.plist
- Di `android/app/src/main/AndroidManifest.xml`:
  Pastikan `<queries>` menyertakan package WhatsApp agar `canLaunchUrl` mengenali aplikasi WhatsApp:
  ```xml
  <queries>
      <package android:name="com.whatsapp" />
      <package android:name="com.whatsapp.w4b" />
      <intent>
          <action android:name="android.intent.action.VIEW" />
          <data android:scheme="whatsapp" />
      </intent>
  </queries>
  ```
- Di `ios/Runner/Info.plist`:
  Pastikan `LSApplicationQueriesSchemes` menyertakan `whatsapp`:
  ```xml
  <key>LSApplicationQueriesSchemes</key>
  <array>
      <string>whatsapp</string>
  </array>
  ```

---

## 3. Arsitektur State Management & Komunikasi API

### 3.1. Klien API SSOT (`ApiClient`)
- **Lokasi:** `lib/core/services/api_client.dart`
- **Pola:** Singleton (`factory ApiClient() => _instance;`)
- **Base URL Dinamis:** Otomatis memilih `http://cooca_core.test` pada mode debug desktop/web lokal, atau `https://cooca.id` pada peranti fisik/rilis, dan dapat diubah lewat `ApiClient().setBaseUrl(...)`.
- **Header Standar:**
  ```dart
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': 'Bearer $customerToken',
  'X-Business-Id': '$activeBusinessId',
  ```
- **Penanganan Error:** Menangkap `SocketException` dan timeout secara terpusat, mengembalikan Map terstruktur `{'success': false, 'message': ...}` tanpa membuat aplikasi crash.

### 3.2. Single State Management (`MerchantState`)
- **Lokasi:** `lib/state/merchant_state.dart`
- **Pola:** `ChangeNotifier` Singleton, dipantau antarmuka via `ListenableBuilder(listenable: MerchantState(), builder: ...)`.
- **Atribut Terkait Fase 2 & 3:**
  - `locations`: Daftar gudang / outlet cabang aktif.
  - `selectedLocationId`: ID gudang/cabang yang sedang aktif.
  - `userAssignedLocationId` & `canAccessAllLocations`: Pembatasan akses jika kasir/staf terikat pada cabang tertentu.
  - `customers`: Daftar pelanggan terdaftar untuk kasbon piutang.
  - `pendingOnlineOrdersCount`: Penghitung pesanan online / storefront masuk (sudah ada di respons `refreshPulse()`).
  - `isPharmacy`, `isFoodIndustry`: Flag industri untuk memunculkan input Nomor Batch & Expired Date.

### 3.3. Offline Resilience & Sinkronisasi (`PosOfflineSyncManager`)
- **Lokasi:** `lib/core/services/pos_offline_sync_manager.dart`
- Menggunakan `LocalStorageService` untuk menyimpan antrean transaksi kasir dengan idempotensi berbasis UUID v4 (`client_uuid`). Saat koneksi terputus, transaksi tetap tersimpan dan otomatis disinkronkan saat terhubung kembali ke backend.

---

## 4. Desain & Arsitektur Fase 2: Logistik & Gudang Toko

### 4.1. Layar Penerimaan Barang Supplier: `GoodsReceiptScreen`
* **File Target:** `lib/screens/inventory/goods_receipt_screen.dart`
* **Titik Akses / Navigasi:**
  1. Dari `inventory_screen.dart` via `_showQuickActionSheet` -> Opsi menu baru: *"Penerimaan PO (Goods Receipt)"*.
  2. Dari tombol tindakan cepat pada header atau tab Opname/Mutasi.

#### Komponen & Alur Layar:
```
┌─────────────────────────────────────────────────────────────┐
│ ‹ Kembali         Penerimaan Barang PO           [ Simpan ] │
├─────────────────────────────────────────────────────────────┤
│ 1. PILIH PURCHASE ORDER (PO PENDING)                        │
│    [ PO #PO-2026-004 • PT Sumber Makmur Mandiri        ▼ ] │
│    Status: Disetujui • Tgl PO: 01 Nov 2026 • 5 Item         │
├─────────────────────────────────────────────────────────────┤
│ 2. INPUT SURAT JALAN / DOKUMEN PENGIRIMAN                   │
│    [ No. Surat Jalan / Delivery Order (DO-9921)           ] │
├─────────────────────────────────────────────────────────────┤
│ 3. PEMINDAI BARCODE KAMERA LIVE (mobile_scanner)            │
│    ┌───────────────────────────────────────────────────┐    │
│    │ [   Camera Viewfinder with Red Laser Animation  ] │    │
│    └───────────────────────────────────────────────────┘    │
│    [ Quick Scan Mode ON ]   [ Input Manual Barcode ]        │
├─────────────────────────────────────────────────────────────┤
│ 4. DAFTAR ITEM PO (Match & Actual vs Damaged Qty)           │
│    ┌───────────────────────────────────────────────────┐    │
│    │ Paracetamol 500mg Strip (SKU: MED-PCT-01)         │    │
│    │ Dipesan: 100 box • Belum Diterima: 100 box        │    │
│    │ [ Qty Diterima: 95 ]   [ Qty Rusak / Reject: 5 ]   │    │
│    │ --- Khusus Apotek / F&B ---                       │    │
│    │ [ Batch / Lot: LOT-202611A ]                      │    │
│    │ [ Exp Date: 12/2028 (CupertinoDatePicker) ]       │    │
│    └───────────────────────────────────────────────────┘    │
├─────────────────────────────────────────────────────────────┤
│ 5. CATATAN PENERIMAAN GUDANG                                │
│    [ 5 box kemasan penyok dari kurir ekspedisi...         ] │
└─────────────────────────────────────────────────────────────┘
```

#### Kontrak API yang Dipanggil:
- `GET /api/v1/inventory/purchase-orders/pending?location_id={id}`
- `GET /api/v1/inventory/purchase-orders/{id}`
- `POST /api/v1/inventory/goods-receipts`
  - Payload:
    ```json
    {
      "purchase_order_id": "po_id",
      "location_id": "loc_id",
      "delivery_order_number": "DO-1029",
      "received_date": "2026-11-02",
      "notes": "Barang diterima di gudang utama",
      "items": [
        {
          "purchase_order_item_id": "poi_1",
          "product_id": "prod_1",
          "quantity_received": 95,
          "quantity_damaged": 5,
          "batch_number": "LOT-9901",
          "expired_date": "2028-12-31"
        }
      ]
    }
    ```

### 4.2. Layar Mutasi / Transfer Stok Antar Cabang: `StockTransferScreen`
* **File Target:** `lib/screens/inventory/stock_transfer_screen.dart`
* **Titik Akses / Navigasi:**
  1. Dari `inventory_screen.dart` via `_showQuickActionSheet` -> Opsi menu baru: *"Transfer Stok Cabang"*.

#### Komponen & Alur Layar (2 Tab Apple HIG Segmented):
- **Tab 0: Tab Kirim Barang (Dispatch)**
  - Asal Gudang: Mengunci pada lokasi aktif staf (`MerchantState.selectedLocationId`).
  - Tujuan Gudang: Dropdown memilih cabang tujuan (mengeliminasi cabang asal).
  - Selector Item & Qty: Memilih produk dari katalog dengan indikator sisa stok fisik di cabang asal.
  - No Referensi Transfer & Catatan Supir/Ekspedisi.
  - Riwayat Transfer Keluar (Status: *In-Transit / Menunggu Diterima*).
  - Aksi: Tombol *"Kirim Mutasi (Dispatch Transfer)"* memanggil `POST /api/v1/inventory/transfers/dispatch`.
- **Tab 1: Tab Terima Barang (Inbound Scan)**
  - Daftar transfer masuk yang berstatus `in_transit` menuju cabang aktif.
  - Mengklik transfer membuka detail pengiriman:
    - Mode Scan Barcode Kamera untuk verifikasi fisik per item yang datang.
    - Mencocokkan kuantitas yang dikirim vs kuantitas aktual tiba di rak.
    - Input catatan jika ada barang hilang di perjalanan atau kemasan rusak.
  - Aksi: Tombol *"Konfirmasi Terima Barang"* memanggil `POST /api/v1/inventory/transfers/{id}/receive`.

#### Kontrak API yang Dipanggil:
- `GET /api/v1/inventory/transfers?location_id={id}&type=all|inbound|outbound`
- `POST /api/v1/inventory/transfers/dispatch`
- `POST /api/v1/inventory/transfers/{id}/receive`

---

## 5. Desain & Arsitektur Fase 3: Customer Piutang & Omnichannel Digital

### 5.1. Buku Piutang & Kasbon Pelanggan
* **File Target 1:** Update `lib/screens/pos/pos_payment_sheet.dart`
* **File Target 2 (Baru):** `lib/screens/pos/pos_customer_debt_sheet.dart`

#### Penyesuaian di `pos_payment_sheet.dart`:
- Metode pembayaran `customer_credit` (*Kasbon / Piutang*) sudah ada di kanal pembayaran (`_buildSingleChannelGrid` baris 868).
- Penegakan alur:
  - Jika kasir memilih "Kasbon / Piutang" tetapi belum ada pelanggan terpilih: Tampilkan dialog/picker pelanggan terdaftar.
  - Saat pelanggan terdaftar terpilih: Munculkan banner informasi Plafon Kredit, Sisa Limit, dan Tombol Apple HIG: **"Lihat Riwayat Piutang & Bayar Cicilan"** yang membuka modal `PosCustomerDebtSheet`.
  - Jika transaksi melebihi sisa limit kredit pelanggan: Blokir proses checkout dengan notifikasi peringatan limit terlampaui.

#### Desain Modal `PosCustomerDebtSheet`:
```
┌─────────────────────────────────────────────────────────────┐
│                       Buku Piutang Pelanggan                │
│ Haji Sulaiman (0812-3456-7890)                      [Tutup] │
├─────────────────────────────────────────────────────────────┤
│ 1. RINGKASAN SALDO PIUTANG                                  │
│    ┌─────────────────────────┬─────────────────────────┐    │
│    │ TOTAL HUTANG AKTIF      │ SISA PLAFON KREDIT      │    │
│    │ Rp 750.000              │ Rp 1.250.000 / 2.000.000│    │
│    └─────────────────────────┴─────────────────────────┘    │
├─────────────────────────────────────────────────────────────┤
│ 2. RIWAYAT BON & FAKTUR BELUM LUNAS                         │
│    • 24 Okt 2026 - INV/2026/1024/001                        │
│      Total: Rp 500.000 | Sisa: Rp 250.000 (Tempo: 07 Nov)   │
│    • 30 Okt 2026 - INV/2026/1030/015                        │
│      Total: Rp 500.000 | Sisa: Rp 500.000 (Tempo: 14 Nov)   │
├─────────────────────────────────────────────────────────────┤
│ 3. FORM CATAT PEMBAYARAN CICILAN PIUTANG                    │
│    [ Nominal Cicilan: Rp 250.000                         ]  │
│    [ Quick: Rp 100k ] [ Rp 250k ] [ Lunasi Semua (750k) ]   │
│    Metode Bayar Cicilan: (•) Tunai  ( ) Transfer  ( ) QRIS  │
│    Catatan: [ Pembayaran titip sopir / kasbon lunas...   ]  │
│    [ Simpan Pembayaran & Perbarui Sisa Hutang ]             │
└─────────────────────────────────────────────────────────────┘
```

#### Kontrak API yang Dipanggil:
- `GET /api/v1/customers/{id}/debts`
- `POST /api/v1/customers/{id}/repay-debt`
  - Payload:
    ```json
    {
      "amount": 250000,
      "payment_method": "cash",
      "notes": "Cicilan kasbon nota 1024",
      "pos_shift_id": 12
    }
    ```

### 5.2. Kirim Struk Digital via WhatsApp Resmi dengan Fallback
* **File Target:** `lib/screens/pos/pos_receipt_screen.dart`
* **Alur Eksekusi:**
  1. Kasir menekan tombol *"WhatsApp"* di bagian bawah struk.
  2. Dialog / Sheet konfirmasi meminta atau mengonfirmasi nomor WhatsApp pelanggan (auto-fill dari profil pelanggan/tamu).
  3. Aplikasi memanggil endpoint API resmi: `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt`.
  4. **Fallback Handling Pintar:**
     - Jika backend berhasil (gateway WhatsApp online): Tampilkan snackbar sukses *"Struk digital resmi berhasil dikirim via WhatsApp Cooca"*.
     - Jika backend mengembalikan respons offline/error (atau nomor WA gateway belum tersambung): Langsung fallback ke intent WhatsApp native menggunakan `url_launcher`:
       ```dart
       final waUri = Uri.parse('whatsapp://send?phone=$cleanPhone&text=$encodedReceiptText');
       if (await canLaunchUrl(waUri)) {
         await launchUrl(waUri, mode: LaunchMode.externalApplication);
       } else {
         // Fallback ke browser web WhatsApp
         final webUri = Uri.parse('https://api.whatsapp.com/send?phone=$cleanPhone&text=$encodedReceiptText');
         await launchUrl(webUri, mode: LaunchMode.externalApplication);
       }
       ```
     - Jika perangkat sama sekali tidak memiliki aplikasi WhatsApp dan offline: Teks format struk otomatis disalin ke clipboard kasir seperti perilaku saat ini.

### 5.3. Notifikasi & Manajemen Pesanan Toko Online & Standee QR
* **File Target:**
  - `lib/state/merchant_state.dart` (listener & polling data pesanan)
  - `lib/screens/dashboard_screen.dart` (badge notifikasi & widget ringkasan)
  - `lib/screens/pos_screen.dart` (top-bar badge & popup pesanan masuk)
  - `lib/screens/pos/pos_online_orders_modal.dart` (modal kelola pesanan online)

#### Mekanisme Reaktif:
1. `MerchantState` menambahkan method `fetchPendingOnlineOrders()` dan periodik check di `refreshPulse()`.
2. Saat terdapat pesanan online baru:
   - Di `dashboard_screen.dart`: Badge merah menyala pada ikon lonceng header dan kartu bento analitik *"Pesanan Toko Online"*.
   - Di `pos_screen.dart`: Badge warna oranye menyala di sebelah tombol *Pesanan Tertahan* pada top-bar.
3. Mengklik badge membuka dialog interaktif:
   - Rincian Pesanan (No Order, Asal Kanal: Web Storefront / QR Meja, Nama Tamu, Alamat / Meja, Waktu Pemesanan).
   - Daftar item dan catatan khusus.
   - 3 Tombol Aksi Langsung:
     1. **Terima Pesanan:** Mengubah status pesanan menjadi `accepted` / `processing`.
     2. **Cetak Tiket KDS Dapur:** Meneruskan item ke printer dapur atau layar KDS via `PosService().sendToKitchen(...)`.
     3. **Panggil Kurir Instant:** Mengirimkan webhook request kurir instant (Grab/Gojek/Borzo) ke backend.

---

## 6. Rencana Pengujian & Baseline Verifikasi

### 6.1. Baseline Saat Ini
- `flutter analyze`: **0 issues (Bersih 100%)**
- `flutter test`: **33 passed (100% lulus)**

### 6.2. Test Suite Baru yang Wajib Diimplementasikan
Untuk memastikan kepatuhan kriteria penerimaan R3, test suite berikut harus ditambahkan di `test/`:
1. `test/goods_receipt_screen_test.dart`:
   - Widget test rendering `GoodsReceiptScreen`.
   - Validasi kalkulasi selisih kuantitas: `actual_received + damaged == ordered`.
   - Validasi gating form batch & expired date (muncul untuk Apotek & F&B, tersembunyi untuk Bengkel/Jasa).
2. `test/stock_transfer_screen_test.dart`:
   - Widget test navigasi 2 tab (Dispatch vs Inbound Scan).
   - Validasi validasi cabang asal != cabang tujuan.
3. `test/pos_customer_debt_test.dart`:
   - Verifikasi pilihan metode bayar "Kasbon / Tempo" saat pelanggan terdaftar dipilih di `pos_payment_sheet.dart`.
   - Widget test `PosCustomerDebtSheet` menampilkan saldo piutang dan memproses cicilan.
4. `test/pos_whatsapp_receipt_test.dart`:
   - Unit test URL encoding format struk WhatsApp.
   - Verifikasi mekanisme fallback saat API gagal.
5. `test/storefront_orders_test.dart`:
   - Verifikasi listener `MerchantState` saat mendeteksi pesanan online baru.
   - Verifikasi kemunculan badge di `dashboard_screen.dart` dan `pos_screen.dart`.

---

## 7. Kesimpulan & Rekomendasi Eksekusi

Codebase Flutter `cooca_my_own` memiliki arsitektur modular yang sangat rapi, standar tipografi dan warna token Apple HIG yang konsisten, serta pemisahan concern yang jelas antara Service, State, dan Presentation. 

Rencana implementasi dapat dijalankan secara terstruktur tanpa risiko regresi:
1. Menambahkan `url_launcher: ^6.3.3` di `pubspec.yaml`.
2. Menambahkan method API di `InventoryService` dan `PosService`.
3. Membangun layar `GoodsReceiptScreen` dan `StockTransferScreen` di `lib/screens/inventory/`.
4. Membangun komponen kasbon `PosCustomerDebtSheet` dan integrasi WhatsApp fallback di `pos_receipt_screen.dart`.
5. Membangun modul pesanan online storefront di `MerchantState`, `dashboard_screen.dart`, dan `pos_screen.dart`.
6. Menjalankan verifikasi akhir `flutter analyze` (target 0 issues) dan `flutter test` (seluruh suite lulus).
