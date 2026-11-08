# Laporan Survei Tata Letak Antarmuka (UI Layout Survey Report)
## Modul Feedback & Finance — COOCA Core Platform

**Tanggal Survei**: 2026-11-08  
**Surveior**: Explorer 1 (Feedback & Finance UI Layout Survey Subagent)  
**Tujuan**: Audit komprehensif 19 berkas Blade views pada modul Feedback dan Finance untuk kesiapan produksi (Production Readiness), penyederhanaan tata letak (UI simplification & zero overlapping), penataan hierarki tombol aksi, standardisasi Apple HIG v2.0 (touch target ≥ 44px, safe area padding), audit RBAC view guards, dan eliminasi hardcoded text.

---

## 1. Inventori Berkas Views (View File Inventory)

Pemeriksaan direktori `resources/views/app/feedback/` dan `resources/views/app/finance/` mencatat total **19 berkas Blade views** aktif:

| No | Modul | Path Berkas View | Ukuran (Bytes) | Controller Terkait | Route Utama |
|---|---|---|---|---|---|
| 1 | Feedback | `resources/views/app/feedback/index.blade.php` | 7.484 | `FeedbackWebController` | `feedback.bugs.index`, `feedback.features.index` |
| 2 | Feedback | `resources/views/app/feedback/create.blade.php` | 14.697 | `FeedbackWebController` | `feedback.bugs.create`, `feedback.features.create` |
| 3 | Feedback | `resources/views/app/feedback/show.blade.php` | 15.305 | `FeedbackWebController` | `feedback.bugs.show`, `feedback.features.show` |
| 4 | Finance | `resources/views/app/finance/expenses.blade.php` | 63.054 | `PosFinanceWebController` | `finance.expenses.index` |
| 5 | Finance | `resources/views/app/finance/journals.blade.php` | 19.566 | `PosFinanceWebController` | `finance.journals.index` |
| 6 | Finance | `resources/views/app/finance/payables.blade.php` | 52.915 | `CashLedgerWebController` | `finance.payables` |
| 7 | Finance | `resources/views/app/finance/receivables.blade.php` | 57.145 | `CashLedgerWebController` | `finance.receivables` |
| 8 | Finance | `resources/views/app/finance/cash-bank/index.blade.php` | 99.077 | `CashLedgerWebController` | `finance.cash-bank.index` |
| 9 | Finance | `resources/views/app/finance/cash-bank/ledger.blade.php` | 24.706 | `CashLedgerWebController` | `finance.cash-bank.ledger` |
| 10 | Finance | `resources/views/app/finance/edc-terminals/index.blade.php` | 23.022 | `StoreEdcTerminalWebController` | `finance.edc-terminals.index` |
| 11 | Finance | `resources/views/app/finance/external-reconciliation/index.blade.php` | 42.665 | `ExternalReconciliationWebController` | `finance.external-recon.index` |
| 12 | Finance | `resources/views/app/finance/payout-accounts/index.blade.php` | 18.105 | `MerchantPayoutAccountWebController` | `finance.payout-accounts.index` |
| 13 | Finance | `resources/views/app/finance/settlements/index.blade.php` | 41.614 | `PaymentSettlementWebController` | `finance.settlements.index` |
| 14 | Finance | `resources/views/app/finance/settlements/show.blade.php` | 22.975 | `PaymentSettlementWebController` | `finance.settlements.show` |
| 15 | Finance | `resources/views/app/finance/accounting/coa.blade.php` | 67.156 | `AccountingWebController` | `finance.coa.index` |
| 16 | Finance | `resources/views/app/finance/accounting/balance-sheet.blade.php` | 46.066 | `AccountingWebController` | `finance.balance-sheet` |
| 17 | Finance | `resources/views/app/finance/accounting/trial-balance.blade.php` | 15.985 | `AccountingWebController` | `finance.trial-balance` |
| 18 | Finance | `resources/views/app/finance/accounting/general-ledger.blade.php` | 17.079 | `AccountingWebController` | `finance.general-ledger` |
| 19 | Finance | `resources/views/app/finance/accounting/reconciliation.blade.php` | 25.748 | `AccountingWebController` | `finance.reconciliations.index` |

---

## 2. Struktur Tata Letak, Tumpang Tindih (Overlap), Double Header & Breadcrumb

### 2.1 Analisis Header Komponen & Double Title
- **Komponen Standar `<x-module-header>`**:
  Sebanyak 18 dari 19 file menggunakan komponen resmi `<x-module-header>`.
- **Anomali Header pada `settlements/show.blade.php`**:
  Berkas `resources/views/app/finance/settlements/show.blade.php` (baris 9–53) adalah **satu-satunya file yang tidak menggunakan `<x-module-header>`**. Berkas ini menulis markup `<header>` manual dengan styling ad-hoc, menyebabkan ketidakkonsistenan DOM dan hilangnya standar breadcrumb.
- **Masalah Judul Ganda (Double Title / Double Header)**:
  - `feedback/create.blade.php` (baris 23–27 & 54–60): `<x-module-header>` telah menampilkan judul "Laporkan Bug" / "Ajukan Fitur" dan subtitlenya. Tepat di bawahnya, kartu form utama menduplikasi judul yang identik (`<h2 class="text-lg font-bold...">`) beserta subtitle yang sama. Hal ini melanggar prinsip *Anti-Whitespace Atas & Larangan Duplikasi Header* pada `responsive-ui-ux` dan `ui-simplify-layout`.
  - `feedback/show.blade.php` (baris 23–27 & 79–81): Judul tiket `$item->title` ditampilkan pada `<x-module-header>`, kemudian diulang kembali secara persis sebagai `<h1 class="text-xl sm:text-2xl font-bold...">` di baris pertama Bento card utama.
- **Konsistensi Navigasi Tab `<x-module-tabs>`**:
  Seluruh views (kecuali `settlements/show.blade.php`) memanggil `<x-module-tabs module="feedback|finance|accounting" />`. Komponen ini menghasilkan kontainer `<nav>` tersembunyi (`display: none !important`) untuk mempertahankan DOM contract test suite sementara secondary navigation aktif disalurkan ke dual-sidebar desktop.

### 2.2 Konsistensi Breadcrumb
- Pada modul Feedback (`create.blade.php`, `index.blade.php`, `show.blade.php` baris 7–16), breadcrumb dioper ke `<x-module-header>`, namun variabel `$homeLabel = $canSeeDashboard ? 'Dashboard' : 'Portal';` didefinisikan secara hardcoded dalam bahasa Inggris, mengabaikan kamus terjemahan `__('navigation.dashboard')` dan `__('navigation.portal')`.
- Pada modul Finance, sebagian besar views tidak mengoper `:breadcrumbs` ke `<x-module-header>`, melainkan hanya `title` dan `subtitle`, kecuali `settlements/show.blade.php` yang menulis breadcrumb manual `<nav>` tanpa komponen `<x-breadcrumb>`.

---

## 3. Hierarki Tombol Aksi (Button Hierarchy & Touch Targets)

### 3.1 Pelanggaran Tombol Primer Bersaing (Multiple Solid Primary Actions)
Standar `ui-simplify-layout` dan Bento Apple HIG v2.0 menegaskan: **Maksimal 1 tombol solid primary per layar/konteks**. Keberadaan lebih dari satu tombol solid dengan warna mencolok membuat pengguna ragu (*choice paralysis*).

* **Pelanggaran Kritis — `cash-bank/index.blade.php` (Baris 20–48)**:
  Pada header halaman terdapat **3 tombol solid primer yang bersaing secara simultan**:
  1. `Kas Masuk` — Solid Hijau (`bg-[#34C759] text-white shadow-[0_1px_2px_rgba(52,199,89,0.25)]`)
  2. `Kas Keluar` — Solid Merah (`bg-[#FF3B30] text-white shadow-[0_1px_2px_rgba(255,59,48,0.25)]`)
  3. `Transfer Kas` — Solid Biru (`bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]`)
  Ditambah 2 tombol sekunder (`Tambah Akun` dan `Buku Kas`), menghasilkan 5 tombol berjajar di header.
  *Rekomendasi*: Tetapkan 1 aksi primer (misalnya Transfer Kas atau Kas Masuk), jadikan aksi mutasi lainnya sebagai segmented action bar atau satukan dalam satu tombol dropdown/split-action.

### 3.2 Duplikasi Aksi (Redundant Buttons)
- **`cash-bank/index.blade.php`**: Tombol "Tambah Akun" (`btn_add_account`) muncul di header (baris 21–24), dan **diulang kembali** di atas deretan kartu akun (baris 88–91). Aksi di header sudah cukup atau gunakan floating quick-add.
- **`feedback/index.blade.php`**: Tombol create muncul di header dan di empty-state (ini pola yang wajar untuk empty state).

### 3.3 Audit Touch Target Apple HIG (Standar Minimal 44×44px)
Berdasarkan prinsip Apple Human Interface Guidelines dan `responsive-ui-ux`, setiap elemen sentuh (tombol dan tautan aksi) wajib berukuran minimal 44×44px dengan gap minimal 8px untuk mencegah *mis-tap* pada perangkat layar sentuh.

| File View | Elemen / Tombol | Ukuran Eksisting | Status Apple HIG | Keterangan & Rujukan Baris |
|---|---|---|---|---|
| `payables.blade.php` | Tombol Bayar (Aksi Baris Tabel) | `min-w-[36px] min-h-[36px]` (sm: `40×40px`) | ❌ Pelanggaran | Baris 222: Ukuran di bawah 44px |
| `payables.blade.php` | Tombol Modal Reset / Commit | `h-10` (40px) | ❌ Pelanggaran | Baris 543 & 558: Tombol footer modal import |
| `receivables.blade.php` | Tombol Detail (Aksi Baris Tabel) | `min-w-[36px] min-h-[36px]` (sm: `40×40px`) | ❌ Pelanggaran | Baris 228: Ukuran di bawah 44px |
| `receivables.blade.php` | Tombol Modal Reset / Commit | `h-10` (40px) | ❌ Pelanggaran | Baris 597 & 612: Tombol footer modal import |
| `accounting/coa.blade.php` | Tombol Edit & Hapus (Baris Tabel) | `w-8 h-8` (32×32px) | ❌ Pelanggaran Kritis | Baris 227 & 235: Target sentuh terlalu sempit (32px) |
| `accounting/reconciliation.blade.php` | Tombol Match & Unmatch | `min-w-[36px] min-h-[36px]` (sm: `40×40px`) | ❌ Pelanggaran | Baris 164 & 171: Tombol aksi mutasi bank |
| `edc-terminals/index.blade.php` | Tombol Toggle Aktif/Nonaktif | `min-h-[36px]` (sm: `38px`) | ❌ Pelanggaran | Baris 134: Tombol status terminal |
| `edc-terminals/index.blade.php` | Tombol Hapus Terminal | `min-w-[36px] min-h-[36px]` (sm: `40×40px`) | ❌ Pelanggaran | Baris 143: Tombol hapus terminal EDC |
| `edc-terminals/index.blade.php` | Tombol Empty State | `h-10` (40px) | ❌ Pelanggaran | Baris 160: Tombol tambah di empty-state |
| `payout-accounts/index.blade.php` | Tombol Jadikan Utama | `min-h-[36px]` (sm: `38px`) | ❌ Pelanggaran | Baris 89: Tombol primary account |
| `payout-accounts/index.blade.php` | Tombol Hapus Rekening | `min-w-[36px] min-h-[36px]` (sm: `40×40px`) | ❌ Pelanggaran | Baris 99: Tombol delete rekening |
| `payout-accounts/index.blade.php` | Tombol Empty State | `h-10` (40px) | ❌ Pelanggaran | Baris 117: Tombol tambah di empty-state |
| `settlements/show.blade.php` | Tombol Lihat Bukti & Unduh Resi | `h-10 sm:h-9` (36px di desktop) | ⚠️ Peringatan | Baris 74 & 79: Kurang lapang di layar sentuh laptop |
| `feedback/show.blade.php` | Tombol Buka Berkas Lampiran | `h-9` (36px) | ❌ Pelanggaran | Baris 156: Tombol lampiran tiket kendala |
| Seluruh Modal Utama | Tombol Close (X) Header Modal | `min-w-[44px] min-h-[44px]` | ✅ Memenuhi Standar | Menggunakan tombol lingkaran 44px HIG |

---

## 4. Responsivitas Mobile (Viewport 375px) & Kebersihan Tata Letak

### 4.1 Safe-Area Bottom Clearance (Padding Bawah)
Aplikasi COOCA menggunakan bilah navigasi bawah tetap (Fixed Bottom Bar / Dock) pada viewport mobile setinggi ~64–80px.
- **Kondisi Baik**:
  - `layouts/app.blade.php` membungkus `<main>` dengan `pb-36 lg:pb-10`.
  - Berkas modul seperti `expenses.blade.php`, `journals.blade.php`, `payables.blade.php`, `receivables.blade.php`, `cash-bank/index.blade.php`, dan `accounting/coa.blade.php` memiliki `pb-28 sm:pb-32 lg:pb-10` atau `pb-28 lg:pb-12`.
- **Kondisi Rentan**:
  - `settlements/show.blade.php` (baris 4): hanya menggunakan `pb-16` (64px). Ini berisiko terpotong atau tertutup navigasi mobile pada layar ponsel kecil atau perangkat dengan *home indicator*. Wajib ditingkatkan minimal ke `pb-28 sm:pb-32 lg:pb-10`.

### 4.2 Perilaku Tabel vs Kartu Inset Mobile
- **Pola Unggulan (Adaptive Card Inset)**:
  Modul `expenses.blade.php`, `journals.blade.php`, `payables.blade.php`, dan `receivables.blade.php` telah mengimplementasikan pola ganda yang sangat baik: tabel lengkap dengan sorting untuk desktop (`hidden sm:block`), dan Grouped Inset List vertikal bergaya iOS untuk ponsel (`sm:hidden divide-y divide-black/[0.04]`). Tidak terjadi horizontal overflow di 375px pada modul-modul ini.
- **Pola Rentan (Horizontally Scrolled Table)**:
  - `accounting/trial-balance.blade.php` (baris 100–120): Menggunakan tabel 7-kolom multi-header dengan `overflow-x-auto`. Pada layar 375px, pengguna terpaksa melakukan scroll horizontal lebar.
  - `accounting/general-ledger.blade.php` (baris 105–120): Menggunakan tabel 7-kolom dengan `overflow-x-auto`.
  - `accounting/reconciliation.blade.php` (baris 111–120): Menggunakan tabel mutasi 5-kolom dengan sticky actions.

### 4.3 Kepadatan Kartu Bento KPI di Layar 375px
Pada tampilan mobile (`grid-cols-2 sm:grid-cols-4`):
- Nominal nominal besar seperti `Rp 125.000.000` di dalam 2-kolom pada layar lebar 375px memiliki ruang horizontal hanya ~165px per kolom.
- Beberapa angka mengalami wrapping atau teks label terpotong. Mengacu pada `responsive-ui-ux` dan `ui-layout-hierarchy-reorganizer`, kartu KPI mobile disarankan menggunakan **Horizontal Snap Slider** (`snap-x snap-mandatory flex overflow-x-auto`) atau ukuran tipografi fluida tabular (`text-[17px] sm:text-[22px]`).

---

## 5. Audit Otorisasi RBAC pada Tombol Tindakan & Tautan Modifikasi

Kriteria R3 dan standar keamanan mengharuskan seluruh aksi sensitif (Tambah, Ubah, Hapus, Ekspor, Unggah, Rekonsiliasi) terlindungi oleh otorisasi role & permission.

### 5.1 Temuan Paradigma Otorisasi Blade
- **Zero `@can` Usage**: Tidak ada satu pun dari 19 berkas view yang menggunakan direktif native Blade `@can(...)`.
- Seluruh pemeriksaan izin menggunakan facade `\App\Support\Context::hasPermission('...')`.

### 5.2 Celah Keamanan View: Aksi Sensitif Tanpa Guard Otorisasi (Unguarded Actions)
Hasil audit menemukan beberapa tombol tindakan manipulasi data penting yang **TIDAK DIBUNGKUS** oleh kondisi otorisasi apa pun di tingkat view:

1. **`payables.blade.php` (Baris 21–26)**:
   Tombol `Import Saldo Awal` (`<button @click="showImportModal = true">`) ditampilkan secara bebas tanpa pengecekan permission, padahal endpoint mutasi `finance.payables.import-execute` di controller dikawal ketat oleh `require.permission:finance.payables`.
2. **`receivables.blade.php` (Baris 21–26)**:
   Tombol `Import Saldo Awal` (`<button @click="showImportModal = true">`) ditampilkan bebas tanpa guard `Context::hasPermission('finance.receivables')`.
3. **`edc-terminals/index.blade.php` (Baris 10–14)**:
   Tombol utama `Tambah Mesin EDC` (`<button @click="openAddModal()">`) di header tidak dibungkus oleh `@if (\App\Support\Context::hasPermission('finance.cash_bank'))`, meskipun aksi toggle dan delete di dalam card dibungkus.
4. **`payout-accounts/index.blade.php` (Baris 10–14)**:
   Tombol `Tambah Rekening Bank` di header tidak dibungkus guard permission, meskipun aksi baris dibungkus.
5. **`settlements/index.blade.php` (Baris 114–240)**:
   Seluruh formulir dan tombol aksi `Cairkan Dana` (Reconcile / Payout) **sama sekali tidak memiliki pengecekan permission** di Blade view (`Context::hasPermission('finance.cash_bank')`).
6. **Ekspor Laporan Keuangan SAK EMKM Tanpa Guard Entitlement di View**:
   - `accounting/balance-sheet.blade.php` (baris 28–33): Tombol `Export Excel` ditampilkan tanpa mengecek `entitlement:export` atau `accounting.view`.
   - `accounting/trial-balance.blade.php` (baris 23–28): Tombol `Export Excel` ditampilkan tanpa guard.
   - `accounting/general-ledger.blade.php` (baris 17–22): Tombol `Export Excel` ditampilkan tanpa guard.

---

## 6. Audit Multi-Bahasa (i18n) & Hardcoded Strings

Mengacu pada direktif `multi-language-and-i18n`, sistem COOCA mewajibkan **Zero Hardcoded Text (100% ID & EN)**.

### 6.1 Temuan Hardcoded Text pada Modul Feedback
1. **`create.blade.php`**:
   - Baris 9: `$homeLabel = $canSeeDashboard ? 'Dashboard' : 'Portal';` (Teks bahasa Inggris statis).
   - Baris 90: `{{ ucfirst(str_replace('_', ' ', $cat)) }}` (Menampilkan slug mentah enum kategori tanpa kamus).
   - Baris 103: `{{ ucfirst($level) }}` (Menampilkan teks mentah "Low", "Normal", "High", "Critical" padahal kunci terjemahan `__('feedback.severity_' . $level)` sudah tersedia lengkap di `lang/id/feedback.php`).
2. **`index.blade.php`**:
   - Baris 9: `$homeLabel = $canSeeDashboard ? 'Dashboard' : 'Portal';`.
   - Baris 56: Fallback status `str_replace('_', ' ', ucfirst($item->status))` menghasilkan teks bahasa Inggris jika kunci tidak cocok.
3. **`show.blade.php`**:
   - Baris 9: `$homeLabel = $canSeeDashboard ? 'Dashboard' : 'Portal';`.
   - Baris 83: `ucfirst(str_replace('_', ' ', $item->category))`.
   - Baris 203: `{{ $update->status ? str_replace('_', ' ', ucfirst($update->status)) : __('feedback.update_work') }}`.

### 6.2 Temuan Hardcoded Text pada Modul Finance
1. **`expenses.blade.php` (Modal Catat Biaya)**:
   Terdapat **banyak teks bahasa Indonesia hardcoded statis** pada tombol-tombol kategori visual di dalam modal (baris 399–488), antara lain:
   - Baris 399–400: `Operasional Toko` / `ATK, pembersih, umum`
   - Baris 407–408: `Listrik, Air & Gas` / `PLN, PAM, tabung LPG`
   - Baris 423–424: `Kemasan & Plastik` / `Plastik, dus, cup, lakban`
   - Baris 447–448: `Transport & Bensin` / `BBM, kurir instan, tol`
   - Baris 455–456: `Sewa Tempat` / `Sewa ruko, booth, IPL`
   - Baris 463–464: `Perawatan & Servis` / `Servis alat, reparasi AC`
   - Baris 471–472: `Pemasaran & Iklan` / `Meta ads, cetak spanduk`
   - Baris 487–488: `Admin Bank & MDR` / `Admin rekening, MDR EDC`
   *(Catatan: Kategori lain seperti `cat_internet_phone` dan `cat_salaries` sudah menggunakan `__()`, terjadi inkonsistensi internal)*.
   - Baris 661: Teks label statis `Double-Entry`.
   - Baris 665 & 669: Teks statis `(D) Beban:` dan `(K) Kas/Bank`.
2. **`cash-bank/index.blade.php`**:
   - Baris 899: Hardcoded string `Saldo:` dan flag `[Minus]` pada tag `<option>`.
3. **`journals.blade.php`**:
   - Baris 131: String fallback `Jurnal` pada badge tipe referensi.
   - Baris 147 & 149: Hardcoded `(Rp)` pada header kolom tabel Debit dan Kredit (seharusnya mengikuti simbol mata uang tenant `$business->currency_symbol`).
4. **`accounting/general-ledger.blade.php`**:
   - Baris 114 & 115: Hardcoded `(Rp)` pada header kolom Debit dan Kredit.
5. **`accounting/coa.blade.php`**:
   - Baris 389: URL action formulir menggunakan string statis hardcoded `'/finance/chart-of-accounts/' + editAccount.id` alih-alih named route atau helper route.
6. **`edc-terminals/index.blade.php`**:
   - Baris 229: String statis `"Bank / Vendor Lainnya"` di dalam opsi dropdown bank.
7. **`settlements/show.blade.php`**:
   - Baris 17: Teks statis `Settlement #`.
   - Baris 110: Teks fallback statis `'Admin COOCA Platform'`.

---

## 7. Matriks Rekapitulasi Masalah per Berkas View

| File View | Overlap / Layout Bloat | Double Header / Breadcrumb | Pelanggaran Tombol / HIG | Unguarded RBAC | Hardcoded Text |
|---|:---:|:---:|:---:|:---:|:---:|
| `feedback/index.blade.php` | - | Breadcrumb hardcoded ID/EN | - | - | Ada |
| `feedback/create.blade.php` | - | Double Title (Header vs Card) | - | - | Ada |
| `feedback/show.blade.php` | - | Double Title (Header vs Card) | Target sentuh lampiran 36px | - | Ada |
| `finance/expenses.blade.php` | - | - | - | - | Ada (Modal Kategori) |
| `finance/journals.blade.php` | - | - | - | - | Ada (`(Rp)`) |
| `finance/payables.blade.php` | - | - | Aksi tabel 36px, Modal 40px | Tombol Import tidak dijaga | - |
| `finance/receivables.blade.php` | - | - | Aksi tabel 36px, Modal 40px | Tombol Import tidak dijaga | - |
| `finance/cash-bank/index.blade.php` | - | - | **3 Tombol Primer Bersaing**, Duplikasi Tambah Akun | - | Ada (`Saldo:`, `[Minus]`) |
| `finance/cash-bank/ledger.blade.php` | - | - | - | - | - |
| `finance/edc-terminals/index.blade.php` | - | - | Toggle/Delete/Empty 36–40px | Tombol Header Tambah EDC | Ada (`Bank Lainnya`) |
| `finance/external-recon/index.blade.php` | - | - | - | - | - |
| `finance/payout-accounts/index.blade.php` | - | - | Aksi baris & empty 36–40px | Tombol Header Tambah Akun | - |
| `finance/settlements/index.blade.php` | - | - | - | Aksi Cairkan Dana tanpa guard | - |
| `finance/settlements/show.blade.php` | Padding `pb-16` kurang aman | Non-standard Header & Nav manual | Tombol bukti 36px di desktop | - | Ada (`Settlement #`) |
| `finance/accounting/coa.blade.php` | - | - | **Aksi baris tabel 32px (Kritis)** | - | Route path hardcoded |
| `finance/accounting/balance-sheet.blade.php` | - | - | - | Tombol Export Excel | - |
| `finance/accounting/trial-balance.blade.php` | Tabel 7 kolom lebar di mobile | - | - | Tombol Export Excel | - |
| `finance/accounting/general-ledger.blade.php` | Tabel 7 kolom lebar di mobile | - | - | Tombol Export Excel | Ada (`(Rp)`) |
| `finance/accounting/reconciliation.blade.php` | - | - | Aksi Match/Unmatch 36px | - | - |

---

## 8. Rekomendasi Solusi & Rencana Perbaikan (Actionable Roadmap)

### Prioritas 1: Penyederhanaan Tombol & Eliminasi Kompetisi Visual (R2 Acceptance Criteria)
1. **Rasionalisasi Header `cash-bank/index.blade.php`**:
   - Satukan aksi mutasi kas (`Kas Masuk`, `Kas Keluar`, `Transfer Kas`) menjadi satu kelompok tombol yang rapi (misal: 1 tombol primer brand `#007AFF` "Mutasi Kas" dengan pilihan dropdown, atau pisahkan menjadi 1 primer solid dan 2 sekunder tonal/outline).
   - Hapus duplikasi tombol "Tambah Akun" di atas deretan kartu akun.
2. **Eliminasi Double Title**:
   - Hapus heading duplikat di dalam kartu form `feedback/create.blade.php` dan `feedback/show.blade.php`.
3. **Standardisasi `<x-module-header>` pada `settlements/show.blade.php`**:
   - Ganti elemen `<header>` kustom dan `<nav>` manual dengan `<x-module-header>` standar yang mendukung `:breadcrumbs` terstruktur.

### Prioritas 2: Kepatuhan Touch Target 44px (Apple HIG & Mobile Usability)
1. Tingkatkan ukuran tombol aksi pada baris tabel:
   - `accounting/coa.blade.php`: ubah `w-8 h-8` menjadi `w-11 h-11 min-w-[44px] min-h-[44px]` (atau `min-h-[40px] sm:min-h-0`).
   - `payables.blade.php`, `receivables.blade.php`, `accounting/reconciliation.blade.php`, `edc-terminals/index.blade.php`, `payout-accounts/index.blade.php`: pastikan container sentuh memiliki `min-w-[44px] min-h-[44px]` dengan `gap-2`.
2. Tingkatkan tombol aksi di footer modal import `payables` dan `receivables` dari `h-10` menjadi `min-h-[44px] sm:min-h-[48px]`.
3. Naikkan padding bawah `settlements/show.blade.php` dari `pb-16` ke `pb-28 sm:pb-32 lg:pb-10`.

### Prioritas 3: Penegakan View Guards RBAC (R3 Acceptance Criteria)
1. Bungkus tombol `Import Saldo Awal` di `payables.blade.php` dan `receivables.blade.php` dengan `@if (\App\Support\Context::hasPermission('finance.payables'))` dan `@if (\App\Support\Context::hasPermission('finance.receivables'))`.
2. Bungkus tombol header `Tambah Mesin EDC` di `edc-terminals/index.blade.php` dan `Tambah Rekening Bank` di `payout-accounts/index.blade.php` dengan `@if (\App\Support\Context::hasPermission('finance.cash_bank'))`.
3. Lindungi formulir pencairan di `settlements/index.blade.php` dengan guard permission.
4. Tambahkan verifikasi hak akses ekspor pada tautan `Export Excel` di modul laporan akuntansi.

### Prioritas 4: Pembersihan Multi-Bahasa & Kamus i18n
1. Daftarkan dan gantikan seluruh label hardcoded kategori biaya di `expenses.blade.php` ke kamus `lang/id/finance.php` dan `lang/en/finance.php`.
2. Terapkan pemetaan `__('feedback.severity_' . $level)` dan `__('feedback.priority_' . $level)` pada `feedback/create.blade.php`.
3. Lokalisasikan breadcrumb `$homeLabel` di modul Feedback menggunakan `__('navigation.dashboard')` / `__('navigation.portal')`.
4. Ganti mata uang hardcoded `(Rp)` dengan dynamic variable `({{ $business->currency_symbol ?? 'Rp' }})`.
