# Handoff Report: UI/UX Architecture & Usability Survey — Core Transactional Modules

**Survey Focus**: Core Transactional & Operations Views in `resources/views/app` (POS, Sales, Sales Orders, Invoices, Quotations, Products, Inventory, Purchasing, Purchase Orders, Suppliers, Warehouse, Materials, Storefront, Marketplace, Customers).  
**Investigator**: Explorer 2 (`explorer_survey_2`)  
**Parent Agent**: `aa2ef6eb-6969-4758-9898-c916a6745d73`  
**Execution Timestamp**: 2026-10-31T11:05:00Z  
**Integrity Mode**: Strictly Read-Only (0 codebase modifications applied)

---

## 1. Observation

### 1.1 Complete Inventory of Transactional & Operations Modules

A full recursive scan of all transactional directories under `resources/views/app` reveals **87 Blade templates** comprising **52,755 lines of code** distributed across 15 submodules:

| Modul | File Count | Total Baris | Mega-Files (>1.000 baris) | Narrow Modals (`max-w-xs..3xl`) | Flawed Backdrops (Flex Wrapper) |
|---|---|---|---|---|---|
| `pos` | 29 | 15.935 | 3 (`terminal.blade.php`, `printers/index.blade.php`, `shifts.blade.php`) | 40 | 20 |
| `products` | 3 | 5.248 | 2 (`index.blade.php`, `modifiers.blade.php`) | 14 | 14 |
| `marketplace` | 6 | 5.171 | 1 (`products.blade.php`) | 5 | 0 |
| `storefront` | 12 | 4.894 | 1 (`orders/show.blade.php`) | 13 | 0 |
| `warehouse` | 2 | 3.711 | 2 (`index.blade.php`, `show.blade.php`) | 10 | 6 |
| `invoices` | 5 | 2.784 | 0 | 7 | 0 |
| `sales` (Returns) | 5 | 2.514 | 0 | 3 | 0 |
| `inventory` | 4 | 2.004 | 0 | 7 | 0 |
| `purchase-orders` | 4 | 1.926 | 0 | 5 | 0 |
| `sales-orders` | 4 | 1.821 | 0 | 2 | 0 |
| `customers` | 1 | 1.504 | 1 (`index.blade.php`) | 2 | 0 |
| `suppliers` | 1 | 1.351 | 1 (`index.blade.php`) | 9 | 0 |
| `quotations` | 4 | 1.319 | 0 | 2 | 0 |
| `purchasing` | 6 | 1.312 | 0 | 1 | 0 |
| `materials` | 1 | 1.261 | 1 (`index.blade.php`) | 7 | 7 |
| **TOTAL** | **87** | **52.755** | **12 mega-files** | **127** | **47** |

---

### 1.2 Detailed Observations by Submodule

#### A. Point of Sale (POS) & Cashier Interfaces (`resources/views/app/pos`)
1. **Monolithic Terminal Architecture (`pos/terminal.blade.php` — 7.158 baris, 532 KB)**:
   - File memuat **20 modal dialog berbeda** yang dideklarasikan secara inline dalam satu file Blade tanpa ekstraksi Blade component atau partial.
   - Mengandung ~2.500 baris script JavaScript murni di bagian bawah (baris 4.650–7.150) yang mengatur state machine kasir, audio Web Audio API synthesizer (`osc1.type = 'triangle'`), TriPay dynamic QRIS polling, offline queue, dan barcode scanner streams.
   - **Flawed Backdrop Architecture**: Seluruh 20 modal menaruh background overlay gelap langsung pada wrapper flex:
     - Baris 2.145 (Quick Customer): `class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60"`
     - Baris 2.195 (Payment Modal): `class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60"`
     - Baris 3.111 (Open Shift Modal): `class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60"`
     - Baris 3.141 (Close Shift & Reconciliation): `class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60"`
     - Baris 3.559 (Modifier & Variant Picker): `class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60"`
     - Baris 3.687 (Layanan Bengkel & Laundry): `class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60"`
   - **Narrow Modal Sizing**:
     - Quick Customer (baris 2.145): `max-w-sm` (384px)
     - Dynamic QRIS (baris 2.506): `max-w-sm sm:max-w-md`
     - Close Shift Reconciliation (baris 3.141): `max-w-md` (448px) — form rekonsiliasi kas riil vs sistem yang padat terdesak sempit.
     - Modifier Picker (baris 3.559): `max-w-md`
     - Layanan Bengkel & Laundry (baris 3.687): `max-w-lg`
     - Detail Item Apotek & Batch (baris 3.918): `max-w-md`
   - **Inkonsistensi Arsitektur Antar-Tampilan**:
     - Pada `pos/shifts.blade.php` (baris 405, 600) modal shift sudah menggunakan standar Apple Bento HIG `max-w-5xl xl:max-w-6xl rounded-[24px]`.
     - Namun pada `pos/terminal.blade.php` (baris 3.111, 3.141), modal shift kasir masih menggunakan legacy `max-w-md` yang sempit.
   - **Mobile/Tablet Operational View**:
     - Layout kasir menggunakan custom CSS bottom sheet pada `< 1024px` (`.pos-cart { position: fixed; bottom: 0; height: min(85dvh, 52rem); transform: translate3d(0, 102%, 0); }`).
     - Terdapat floating bar di bagian bawah (`.pos-cart-bar`). Namun tinggi 85dvh tidak adaptif saat keyboard virtual muncul untuk input catatan pesanan atau diskon.

2. **Kitchen Display System (`pos/kitchen.blade.php` — 476 baris)**:
   - Menggunakan 3 kolom vertikal pada desktop (`grid grid-cols-1 lg:grid-cols-3 gap-5 items-start`) untuk antrean New, In Progress, Ready.
   - Pada tablet portrait (< 1024px), antrean jatuh menjadi 1 kolom tumpuk ke bawah, sehingga staf dapur harus scroll vertikal panjang untuk melihat pesanan yang sedang dimasak vs yang baru masuk.

3. **POS Reports & Multi-Tab Filter (`pos/reports.blade.php` — 83 baris)**:
   - Menggunakan 15 segmented tab dalam horizontal scrolling nav.
   - Tombol Export Excel & Print PDF pada header (baris 17–27) menggunakan `min-h-[40px] sm:min-h-0 sm:h-9`, berada tepat di bawah batas target sentuh minimum 44px.

---

#### B. Product Catalog & Modifiers (`resources/views/app/products`)
1. **Monolithic Product Master (`products/index.blade.php` — 3.443 baris, 294 KB)**:
   - **State Bloat**: 778 baris pertama (baris 8–778) adalah deklarasi objek state Alpine.js di dalam tag div root sebelum markup HTML pertama dimulai.
   - **Duplikasi Kode Masif**:
     - Modal Tambah Produk Baru: baris 1.454–2.111 (657 baris markup).
     - Modal Edit Data Produk: baris 2.112–2.813 (701 baris markup).
     - Kedua modal ini 95% identik (field nama, barcode, kategori, satuan, harga pokok BOM, harga jual cabang, toggle marketplace, foto galeri, preorder lead days, paket bundle), menyebabkan duplikasi ~1.350 baris kode yang memperberat rendering Blade.
   - **Flawed Backdrop Overlay**: 8 modal dialog menggunakan wrapper flex `fixed inset-0 z-[200] flex items-center justify-center ... bg-black/60` (baris 1.460, 2.118, 2.819, 2.870, 2.938, 2.966, 3.007, 3.253).
   - **Sub-44px Touch Targets pada Mobile Card**:
     - Pada mobile grouped card view (baris 1.359, 1.366), tombol aksi Edit dan Delete menggunakan `h-9` (36px).
     - Pada quick toggle strip channel (baris 1.402, 1.408, 1.415), tombol toggle POS, Web, dan Sales Order menggunakan `h-7 px-2.5 rounded-[6px]` (hanya 28px tinggi), sangat rentan terhadap *miss-tap* bagi kasir/owner di lapangan.
   - **Table Density**: Tabel desktop (baris 949) memiliki lebar tetap `min-w-[1350px]`, sehingga pada tablet 768px–1024px memerlukan scroll horizontal ekstensif.

2. **Product Modifiers & Variants (`products/modifiers.blade.php` — 1.366 baris)**:
   - Modal group modifier dan modal opsi pilihan menggunakan wrapper flex `bg-black/60` tanpa backdrop mandiri.

---

#### C. Sales Orders, Invoices & Quotations (`sales-orders`, `invoices`, `quotations`, `sales/returns`)
1. **Table Action Button Clutter & Sub-44px Targets**:
   - `sales-orders/index.blade.php` (baris 223–253): Kolom aksi tabel menjejaskan **4 tombol inline berukuran 32×32px** (`w-8 h-8 rounded-[8px]`, gap-1): PDF Printer, Edit, View/Eye, dan Delete. Tidak ada overflow menu (`⋯`).
   - `sales/returns/index.blade.php`: 3 tombol inline `w-8 h-8` berdampingan.
   - `quotations/index.blade.php`: 3 tombol inline `w-8 h-8` berdampingan.
   - `invoices/index.blade.php`: Tombol aksi row tabel berukuran 36×36px (`w-9 h-9`).
2. **Missing Sticky Bottom Submission Bar pada Mobile Form**:
   - `sales-orders/create.blade.php` (611 baris): Formulir pesanan penjualan memuat header pelanggan, tanggal, termin, tabel rincian item dinamis (`min-w-[700px]`), ringkasan diskon/pajak, dan catatan. Tombol submit utama berada di dasar halaman tanpa sticky footer. Pengguna HP harus menggulir jauh ke bawah setelah menambah item untuk mengeksekusi order.
   - `invoices/create.blade.php` (498 baris): Tidak ada sticky bar bawah pada mobile. Tombol submit utama pada baris 480 menggunakan ukuran `h-9` (36px) tanpa pembatas responsif.
   - `quotations/create.blade.php` (285 baris): Tombol submit (baris 275) berukuran `h-9 px-6 rounded-[10px]` (36px) tanpa `min-h-[44px]`.
3. **Form Density & Overflow pada Invoice Detail (`invoices/show.blade.php` — 917 baris)**:
   - Memuat 5 form, 2 tabel, 11 tombol warna primary (`#007AFF`), dan 12 tombol warna destructive (`#FF3B30`). Persaingan visual tinggi antara tombol "Kirim Tagihan", "Catat Pembayaran", "Cetak Faktur", "Batalkan Invoice", dan "Hapus".

---

#### D. Inventory, Warehouse, Materials & Suppliers (`inventory`, `warehouse`, `materials`, `suppliers`)
1. **Infinite Card Bloat pada Mobile (`inventory/stocks.blade.php` — 443 baris)**:
   - Baris 94–96: Baris KPI metrik menggunakan `grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4`.
   - Pada layar smartphone (< 640px), 3 kartu metrik besar (Nilai Aset HPP, Peringatan Stok Minimum, Total Item Terdata) tersusun vertikal satu per satu, menyita ~400px tinggi layar sebelum pengguna dapat melihat kolom pencarian dan tabel stok.
   - Pelanggaran prinsip: *Anti Infinite Card Bloat* — tidak menggunakan **Horizontal Snap Slider** (`snap-x snap-mandatory`) atau grid 2x2.
2. **Table Action Button Size**:
   - `inventory/stocks.blade.php` (baris 234): Tombol adjust stok berukuran `w-9 h-9 min-h-[36px] min-w-[36px]` (36px, di bawah standar 44px).
   - `materials/index.blade.php` (baris 443): Tombol update harga dan edit bahan berukuran `w-8 h-8 rounded-[8px]` (32px).
3. **Mega-Files & Backdrop Flaws pada Warehouse & Materials**:
   - `warehouse/index.blade.php` (1.812 baris) & `warehouse/show.blade.php` (1.899 baris): Memuat ribuan baris markup modal Biteship geocoding, jam operasional, dan stock matrix.
   - `materials/index.blade.php` (1.261 baris): Memiliki 7 modal dengan wrapper flex `bg-black/60` dan sub-modals (Supplier, Kategori, Satuan pada baris 1.035, 1.097, 1.147) terkurung pada `max-w-md` tanpa responsive layout.
4. **Golden Reference Pattern (`suppliers/index.blade.php` — baris 656–674)**:
   - Mengimplementasikan arsitektur backdrop yang benar sesuai standar Apple HIG:
     ```html
     <div x-show="showAddModal" class="fixed inset-0 z-[200] overflow-y-auto" role="dialog">
         <!-- Standalone Backdrop Div -->
         <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>
         <!-- Centered Dialog Wrapper -->
         <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6">
             <div class="relative w-full max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl max-h-[88vh] rounded-[24px] ...">
     ```

---

#### E. Storefront & Marketplace Operations (`storefront`, `marketplace`)
1. **Storefront Order Detail (`storefront/orders/show.blade.php` — 1.295 baris)**:
   - Halaman tunggal memuat **10 formulir**, **8 modal dialog**, 23 primary buttons, dan 19 destructive buttons.
   - Modal Verifikasi Pembayaran Manual (baris 1.122) dan Modal Tolak Pesanan (baris 1.062) terkurung pada `max-w-md` (448px), menyebabkan bukti transfer perbankan dan form rekonsiliasi berdesakan secara vertikal.
2. **Marketplace Product Sync Hub (`marketplace/products.blade.php` — 2.296 baris)**:
   - Memuat 45 tombol destructive merah (`#FF3B30`) untuk aksi unbind/delete mapping marketplace (Shopee, Tokopedia, TikTok Shop, Lazada).
   - Penggunaan tombol penuh tanpa pengelompokan menu overflow menyebabkan tabel produk marketplace sangat padat.

---

### 1.3 Audit Khusus: Deteksi 6 Anomali Tombol & Layout (Berdasarkan `ui-simplify-layout`)

| # | Anomali Visual / Layout | Gejala & Lokasi Nyata | Akar Masalah | Rekomendasi Solusi |
|---|---|---|---|---|
| 1 | **Sub-44px Tap Targets (Fitts's Law Failure)** | `sales-orders/index` (baris 225), `materials/index` (baris 445): Tombol aksi tabel `w-8 h-8` (32px); `products/index` (baris 1.402): Toggle switch `h-7` (28px). | Pengembang mengecilkan tombol demi memuat banyak elemen dalam satu baris. | Wajibkan minimum `min-h-[44px] min-w-[44px]` pada mobile/tablet, atau kelompokkan ke overflow dropdown `⋯`. |
| 2 | **Table Action Clutter (> 2 Aksi Inline)** | `sales-orders/index` (4 tombol berdampingan: Print, Edit, Detail, Delete); `invoices/show` (11 primary + 12 destructive). | Tidak ada menu overflow (`⋯`). Semua opsi CRUD ditaruh di level baris pertama. | Pertahankan 1 aksi primer (Lihat/Detail), masukkan sisa aksi ke dropdown menu overflow (`⋯`). |
| 3 | **Flawed Backdrop Wrapper ($y=0$ Header Leaks)** | Ditemukan pada **47 lokasi** (`pos/terminal`, `products/index`, `materials/index`, `warehouse/index`): `class="fixed inset-0 z-[200] flex ... bg-black/60"`. | Background gelap disatukan pada flex wrapper, memicu celah putih bilah header dan clipping saat scroll. | Ekstraksi ke elemen backdrop mandiri: `<div class="fixed inset-0 bg-black/60 backdrop-blur-md"></div>` dengan kontainer dialog `relative z-10`. |
| 4 | **Narrow Form Modal Squeeze (Non-Bento)** | Ditemukan pada **127 lokasi**: modal formulir memakai `max-w-md`, `max-w-sm`, `max-w-lg` tanpa override `lg:max-w-5xl/6xl`. | Menggunakan template modal peringatan (alert) untuk formulir input multi-field. | Terapkan canvas XXL Bento 2-kolom pada Desktop (`lg:max-w-5xl max-h-[92vh]`) dan Adaptive Bottom Sheet pada Mobile (`rounded-t-[28px] max-h-[96vh] pb-28`). |
| 5 | **Mobile Infinite Card Bloat** | `inventory/stocks` (baris 94), `pos/orders` (baris 45): Deretan 3–4 kartu metrik KPI disusun `grid-cols-1` vertikal. | Breakpoint mobile tidak diatur sebagai horizontal snap slider. | Ubah ke **Horizontal Snap Slider** (`flex gap-3 overflow-x-auto snap-x snap-mandatory sm:grid sm:grid-cols-3`). |
| 6 | **Missing Mobile Sticky Submit Bar** | `sales-orders/create` (611 baris), `invoices/create` (498 baris), `quotations/create` (285 baris): Tombol submit terkubur di paling bawah. | Alur formulir mengikuti linear flow desktop tanpa kompensasi layar sentuh jempol. | Tambahkan sticky bottom action bar pada mobile (`fixed inset-x-0 bottom-0 p-3 bg-white/95 border-t z-30 sm:static`) dengan safe-area padding `pb-28`. |

---

## 2. Logic Chain

1. **Premis 1 (Fitts's Law & Human Thumb Physics)**: Target sentuh di bawah 44×44px dengan jarak di bawah 8px secara matematis meningkatkan kesalahan ketukan (*error rate*) sebesar >30% pada layar sentuh, khususnya pada kasir POS dan operasional gudang UMKM yang serba cepat.
   - *Bukti Observasi*: 58 dari 87 file memiliki tombol berukuran 28px (`h-7`), 32px (`w-8 h-8`), atau 36px (`w-9 h-9`, `h-9`) tanpa `min-h-[44px]`.
   - *Inferensi*: Petugas kasir atau staf gudang berisiko salah menekan tombol (misalnya menekan Hapus padahal ingin Edit atau Detail).

2. **Premis 2 (Cognitive Scannability & Action Hierarchy)**: Menampilkan lebih dari 2 aksi inline per baris tabel menyebabkan *visual noise* yang memperlambat pemindaian mata kasir/owner (pola F-Pattern terpecah).
   - *Bukti Observasi*: `sales-orders/index.blade.php` memiliki 4 tombol inline berdampingan di setiap baris data; `invoices/show.blade.php` memiliki 23 tombol aksi yang saling bersaing bobot visualnya.
   - *Inferensi*: Alur keputusan pengguna terhambat karena tidak adanya primary visual cue yang dominan.

3. **Premis 3 (Bento Apple HIG vs Modal Narrow Squeeze)**: Formulir transaksi (rekonsiliasi kas, input produk BOM, harga cabang, verifikasi bukti bayar) membutuhkan bidang visual yang lapang agar data master, kalkulasi, dan field input dapat dilihat tanpa scroll vertikal berlebih.
   - *Bukti Observasi*: 127 kemunculan kelas `max-w-xs`, `max-w-sm`, `max-w-md`, dan `max-w-lg` pada modal formulir input data, termasuk form rekonsiliasi kas `pos/terminal.blade.php` line 3141 (`max-w-md`).
   - *Inferensi*: Pengguna dipaksa menggulir di dalam dialog sempit dengan bilah scroll berlapis (*nested scrollbars*), memicu klaustrofobia visual dan kelelahan operasional.

4. **Premis 4 (Backdrop Architecture & Viewport Edge-to-Edge)**: Menaruh background overlay hitam transparan pada flex wrapper (`class="fixed inset-0 flex ... bg-black/60"`) menyebabkan lapisan backdrop gagal menutupi area di luar flex bounding box saat header sticky atau viewport safe-area aktif.
   - *Bukti Observasi*: 47 modal di `pos/terminal.blade.php`, `products/index.blade.php`, dan `materials/index.blade.php` menggunakan pola flex wrapper tersebut. Sebaliknya, `suppliers/index.blade.php` telah memisahkan backdrop mandiri secara benar.
   - *Inferensi*: Terjadi kebocoran visual bilah atas (*topbar bleed*) dan modal terpotong pada browser mobile.

5. **Premis 5 (Maintainability & Monolithic Danger)**: Menggabungkan 7.158 baris kode, 20 modal, dan 2.500 baris inline JavaScript ke dalam satu file `pos/terminal.blade.php` meningkatkan risiko regresi kode, menyulitkan caching Blade, dan memicu memory spike pada parser.
   - *Bukti Observasi*: `pos/terminal.blade.php` berukuran 532 KB dengan 7.158 baris. `products/index.blade.php` menduplikasi 1.350 baris kode modal Create dan Edit secara verbatim.
   - *Inferensi*: Refactoring modular berbasis Blade partials (`resources/views/app/pos/terminal/partials/*` dan `resources/views/app/products/partials/*`) mutlak diperlukan demi stabilitas produksi.

---

## 3. Caveats

1. **Read-Only Scope**: Seluruh pengamatan dilakukan secara statis melalui inspeksi kode, parsing AST, dan regex audit tanpa menjalankan browser runtime rendering atau headless Puppeteer/Playwright. Perilaku visual di layar nyata (seperti animasi transisi Alpine.js `x-transition`) didasarkan pada analisis kode Tailwind & Alpine.
2. **Dynamic Alpine JS State**: Beberapa modal di `pos/terminal.blade.php` dan `products/index.blade.php` dibuka berdasarkan event dinamis JavaScript (`$dispatch`, inline methods). Kondisi reaktifitas dinamis saat runtime bergantung pada kelengkapan payload data dari backend controller.
3. **Hardware / Thermal Printer Constraints**: Antarmuka printer di `pos/printers/index.blade.php` melibatkan interaksi WebUSB, Bluetooth ESC/POS, dan Network socket printing yang hanya dapat diuji sepenuhnya dengan perangkat keras printer termal nyata.

---

## 4. Conclusion

Secara keseluruhan, arsitektur antarmuka modul transaksional COOCA (`resources/views/app`) telah memiliki pondasi visual yang modern berbasis Tailwind CSS dan Alpine.js, dengan beberapa tampilan (seperti `pos/shifts.blade.php`, `pos/printers/index.blade.php`, dan `suppliers/index.blade.php`) yang telah mengadopsi standar Apple Bento HIG secara konsisten.

Namun, terdapat **5 kelemahan sistemik** yang memerlukan perbaikan terstruktur:
1. **Monolitik Terminal Kasir & Mega-Files**: `pos/terminal.blade.php` (7.158 baris), `products/index.blade.php` (3.443 baris), dan `warehouse/index.blade.php` (1.812 baris) perlu didekomposisi menjadi sub-partial modular.
2. **Cacat Arsitektur Backdrop (47 lokasi)**: Wrapper flex dengan background langsung harus dimigrasikan ke arsitektur backdrop mandiri seperti pada `suppliers/index.blade.php`.
3. **Penyempitan Modal Formulir (127 lokasi)**: Seluruh pop-up formulir harus dinaikkan ke canvas Bento XXL (`max-w-5xl`/`6xl` pada desktop) dan Adaptive Bottom Sheet (`rounded-t-[28px] max-h-[96vh]` dengan safe-area `pb-28` pada mobile).
4. **Target Sentuh < 44px & Clutter Aksi Tabel**: Baris tabel dengan 3–4 tombol kecil (`w-8 h-8`) harus dirampingkan menjadi 1 aksi primer + 1 menu overflow (`⋯`), serta memastikan target sentuh minimum 44×44px.
5. **Mobile Viewport Optimization**: Mengganti tumpukan kartu metrik vertikal (Infinite Card Bloat) pada `inventory/stocks` dan `pos/orders` dengan **Horizontal Snap Slider** (`snap-x snap-mandatory`), serta menambahkan sticky bottom action bar pada formulir transaksi mobile (`sales-orders/create`, `invoices/create`, `purchase-orders/create`).

### Rekomendasi Roadmap Remediasi (4 Fase)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        ROADMAP REMEDIASI UI/UX TRANSAKSIONAL COOCA                     │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 🚀 FASE 1: Standarisasi Backdrop Mandiri & Penegakan Touch Target (Quick Wins)         │
│ • Perbaiki 47 flawed backdrop flex wrappers menjadi standalone backdrop div            │
│ • Naikkan target sentuh tombol aksi tabel dan toggle mobile ke min-h-[44px]            │
│ • Terapkan menu overflow (⋯) pada tabel sales-orders, quotations, dan materials        │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 📐 FASE 2: Dekomposisi Mega-File Produk & Eliminasi Duplikasi Modal                    │
│ • Ekstraksi products/index.blade.php: pisahkan state Alpine ke assets/js               │
│ • Satukan modal Create & Edit Produk (~1.350 baris duplikat) menjadi shared partial    │
│ • Naikkan sub-modal kategori, satuan, dan barcode ke Bento XXL canvas                  │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 📱 FASE 3: Optimalisasi Operasional Mobile & Ergonomi Layar Sentuh                     │
│ • Terapkan Horizontal Snap Slider pada metrik inventory/stocks dan pos/orders          │
│ • Tambahkan Sticky Bottom Submit Bar pada formulir transaksi mobile                    │
│ • Eliminasi text-xs pada 45 input form untuk mencegah iOS Safari auto-zoom             │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 💎 FASE 4: Modularisasi POS Terminal Kasir (pos/terminal.blade.php)                    │
│ • Pecah 20 modal inline pos/terminal.blade.php ke folder pos/terminal/modals/*        │
│ • Ekstraksi 2.500 baris script kasir ke modul JS terisolasi (pos-engine.js)           │
│ • Selaraskan modal shift kasir pos/terminal dengan standar Bento XXL pos/shifts        │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 5. Verification Method

Untuk memverifikasi temuan audit secara independen, jalankan serangkaian script inspeksi di bawah ini:

1. **Verifikasi Total File & Baris Transaksional**:
   ```powershell
   python -c "
   import os, glob
   base = r'c:\laragon\www\cooca_core\resources\views\app'
   mods = ['pos', 'sales', 'sales-orders', 'products', 'inventory', 'purchasing', 'purchase-orders', 'suppliers', 'invoices', 'quotations', 'warehouse', 'materials', 'storefront', 'marketplace', 'customers']
   tot_f, tot_l = 0, 0
   for m in mods:
       fl = glob.glob(os.path.join(base, m, '**', '*.blade.php'), recursive=True)
       ln = sum(sum(1 for _ in open(f, 'r', encoding='utf-8', errors='ignore')) for f in fl)
       print(f'{m:20}: {len(fl):2} files | {ln:,} lines')
       tot_f += len(fl); tot_l += ln
   print(f'TOTAL: {tot_f} files | {tot_l:,} lines')
   "
   ```

2. **Verifikasi Cacat Backdrop Wrapper (47 Lokasi)**:
   ```powershell
   python -c "
   import os, glob, re
   base = r'c:\laragon\www\cooca_core\resources\views\app'
   pattern = re.compile(r'class=\"[^\"]*\bfixed\s+inset-0\b[^\"]*\bflex\b[^\"]*\bbg-black/(?:[0-9]+)\b')
   for root, _, files in os.walk(base):
       for f in files:
           if f.endswith('.blade.php'):
               p = os.path.join(root, f)
               cnt = len(pattern.findall(open(p, 'r', encoding='utf-8', errors='ignore').read()))
               if cnt > 0:
                   print(f'{os.path.relpath(p, base)}: {cnt} flawed backdrops')
   "
   ```

3. **Verifikasi Penyempitan Modal Sempit (127 Lokasi)**:
   Periksa `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2\scan_results.json` untuk daftar lengkap modal dengan kelas `max-w-xs`, `max-w-sm`, `max-w-md`, dan `max-w-lg`.

4. **Verifikasi Kondisi Ketiadaan Modifikasi (Read-Only Guarantee)**:
   Jalankan git status untuk membuktikan tidak ada perubahan pada file repositori di luar direktori teamwork:
   ```powershell
   git status resources/views/app
   ```
   *(Harus menghasilkan working tree clean, tanpa modified files).*
