# Panduan Pengguna: Modul Keuangan & Akuntansi (Finance & Accounting)

Selamat datang di Panduan Pengguna Modul Keuangan & Akuntansi COOCA. Modul ini dirancang khusus untuk pemilik usaha (**Business Owner**) dan manajer keuangan UMKM di Indonesia guna mengelola arus kas harian, buku besar akuntansi ganda (*double-entry bookkeeping*), pencatatan piutang dan hutang usaha, rekonsiliasi bank, hingga penerbitan laporan keuangan standar SAK EMKM secara otomatis dan akurat.

---

## 1. Ikhtisar Arsitektur Modul

Modul Keuangan terbagi menjadi dua sub-hub utama yang saling terintegrasi secara otomatis:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        COOCA FINANCE & ACCOUNTING                      │
├───────────────────────────────────┬────────────────────────────────────┤
│ 1. Hub Keuangan & Kas (Operasional)│ 2. Hub Akuntansi Korporasi (SAK)   │
├───────────────────────────────────┼────────────────────────────────────┤
│ • Buku Kas & Bank                 │ • Bagan Akun (Chart of Accounts)   │
│ • Buku Kas & Riwayat Mutasi       │ • Buku Besar Umum (General Ledger) │
│ • Pengeluaran / Beban Operasional │ • Neraca Saldo (Trial Balance)     │
│ • Piutang Usaha (AR Aging)        │ • Neraca Keuangan (Balance Sheet)  │
│ • Hutang Usaha (AP Aging)         │ • Rekonsiliasi Rekening Koran Bank │
│ • Payout Hub & Gateway Settlement │ • Rekonsiliasi Eksternal & Likuid  │
└───────────────────────────────────┴────────────────────────────────────┘
```

---

## 2. Navigasi & Tautan Menu Resmi

Sebagai pemilik bisnis, Anda dapat mengakses seluruh fitur keuangan melalui tautan langsung berikut:

| Nama Fitur / Layanan | Tautan Langsung | Izin Akses Minimal | Deskripsi Fungsional |
|---|---|---|---|
| **Buku Kas & Bank** | [/finance/cash-bank](/finance/cash-bank) | `finance.cash_bank` | Ringkasan saldo kas fisik laci, rekening bank, dan ringkasan pendapatan POS per metode bayar. |
| **Buku Kas & Mutasi** | [/finance/cash-bank/ledger](/finance/cash-bank/ledger) | `finance.cash_bank` | Buku besar kas harian, pencatatan mutasi masuk (*inflow*), keluar (*outflow*), dan transfer antar rekening. |
| **Beban Operasional** | [/finance/expenses](/finance/expenses) | `expenses.view` | Pencatatan pengeluaran operasional toko lengkap dengan kategori, metode bayar, dan bukti nota foto. |
| **Jurnal Akuntansi** | [/finance/journals](/finance/journals) | `accounting.view` | Rekaman entri jurnal otomatis (*double-entry*) dari seluruh transaksi penjualan POS, beban, dan mutasi. |
| **Piutang Usaha (AR)** | [/finance/receivables](/finance/receivables) | `finance.receivables` | Pemantauan umur piutang faktur penjualan B2B, ekspor data, dan template migrasi saldo awal. |
| **Hutang Usaha (AP)** | [/finance/payables](/finance/payables) | `finance.payables` | Jadwal jatuh tempo tagihan pembelian ke supplier, pemantauan status lunas/sebagian, dan ekspor Excel. |
| **Payout Hub & Settlement**| [/finance/settlements](/finance/settlements) | `finance.cash_bank` | Rekonsiliasi pencairan dana otomatis dari payment gateway (QRIS/Transfer) ke rekening operasional toko. |
| **Bagan Akun (COA)** | [/finance/chart-of-accounts](/finance/chart-of-accounts) | `accounting.view` | Pengelola bagan akun standar SAK EMKM dan penambahan sub-akun kustom bertingkat (*tree builder*). |
| **Buku Besar Umum** | [/finance/general-ledger](/finance/general-ledger) | `accounting.view` | Filter mutasi debit/kredit per akun COA untuk audit pembukuan terperinci. |
| **Neraca Saldo** | [/finance/trial-balance](/finance/trial-balance) | `accounting.view` | Verifikasi keseimbangan (*balance check*) total debit dan kredit seluruh akun pada periode tertentu. |
| **Neraca Keuangan SAK**| [/finance/balance-sheet](/finance/balance-sheet) | `accounting.view` | Laporan posisi keuangan resmi (Aset = Kewajiban + Modal) standar SAK EMKM yang siap diekspor ke Excel. |
| **Rekonsiliasi Bank** | [/finance/reconciliations](/finance/reconciliations) | `accounting.view` | Unggah rekening koran CSV (BCA, Mandiri, BRI) dan pencocokan otomatis (*auto-matching*) dengan pembukuan kas. |
| **Rekening Payout** | [/finance/payout-accounts](/finance/payout-accounts) | `finance.cash_bank` | Pendaftaran dan verifikasi rekening bank pemilik usaha untuk penerimaan pencairan dana gateway. |
| **Mesin EDC Toko** | [/finance/edc-terminals](/finance/edc-terminals) | `finance.cash_bank` | Manajemen terminal EDC per outlet dan pengaturan persentase potongan MDR debit/kredit. |
| **Rekonsiliasi Eksternal**| [/finance/external-reconciliation](/finance/external-reconciliation) | `finance.cash_bank` | Dashboard likuiditas dan rekonsiliasi saldo akun eksternal toko (*Where The Money Lives*). |

---

## 3. Alur Kerja Harian Pemilik Usaha

### A. Memantau Saldo Kas Toko & Rekening Bank
1. Buka menu [Buku Kas & Bank](/finance/cash-bank).
2. Tinjau kartu saldo masing-masing kas fisik (laci kasir) dan rekening bank operasional.
3. Klik tombol **Catat Pemasukan** untuk setoran modal awal atau pendapatan non-POS.
4. Klik tombol **Catat Pengeluaran** untuk pengeluaran kas kecil (*petty cash*).
5. Klik tombol **Transfer Saldo** jika Anda memindahkan uang tunai laci ke rekening bank toko. Seluruh mutasi otomatis mencatat entri jurnal akuntansi berpasangan.

### B. Pencatatan Beban & Pengeluaran Toko
1. Buka menu [Beban Operasional](/finance/expenses).
2. Klik tombol **Catat Pengeluaran Baru**.
3. Pilih kategori biaya (misal: *Bahan Habis Pakai*, *Listrik & Air*, *Gaji Karyawan*, *Sewa*, atau *Pemasaran*).
4. Masukkan nominal rupiah, tanggal pengeluaran, akun kas pembayar, dan unggah foto nota bukti transaksi.
5. Sistem otomatis memotong saldo akun kas yang dipilih dan menerbitkan jurnal debit-kredit secara real-time.

### C. Meninjau Neraca Keuangan & Keseimbangan Pembukuan
1. Akses menu [Neraca Keuangan SAK](/finance/balance-sheet).
2. Tentukan tanggal cut-off posisi keuangan (*As of Date*).
3. Evaluasi komposisi Aset Lancar, Aset Tetap, Kewajiban Jangka Pendek, dan Ekuitas Modal.
4. Klik tombol **Export Neraca ke Excel** untuk mengunduh laporan keuangan formal berformat multi-sheet lengkap dengan analisis rasio likuiditas.

---

## 4. Panduan Terkait

- [Panduan Troubleshooting Keuangan](troubleshooting.md) — Solusi atas kendala rekonsiliasi bank, selisih jurnal, dan impor saldo awal.
- [FAQ Modul Keuangan & Akuntansi](faq.md) — Tanya jawab seputar standar SAK EMKM, izin staf, dan keamanan formula Excel.
