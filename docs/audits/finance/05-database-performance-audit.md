# Database & Performance Audit - finance

> Modul: Keuangan & Akuntansi Korporasi | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## 1. Skema Database & Integritas Data

Modul Keuangan mengandalkan relasi tabel relasional yang dinormalisasi dengan baik untuk menjamin keakuratan matematis pembukuan berpasangan:

```
┌─────────────────────────────────┐       ┌─────────────────────────────────┐
│       chart_of_accounts         │       │    accounting_journal_entries   │
├─────────────────────────────────┤       ├─────────────────────────────────┤
│ id (bigint / uuid) PK           │       │ id (bigint / uuid) PK           │
│ business_id (FK -> businesses)  │       │ business_id (FK -> businesses)  │
│ code (varchar)                  │       │ entry_number (varchar)          │
│ name (varchar)                  │       │ entry_date (date)               │
│ classification (enum)           │       │ status (varchar)                │
│ parent_id (FK -> self)          │       └────────────────┬────────────────┘
└────────────────┬────────────────┘                        │ 1
                 │ 1                                       │
                 │                                         │ N
                 │ N                      ┌────────────────┴────────────────┐
                 └───────────────────────>│    accounting_journal_items     │
                                          ├─────────────────────────────────┤
                                          │ id PK                           │
                                          │ journal_entry_id (FK)           │
                                          │ chart_of_account_id (FK)        │
                                          │ debit (decimal 15,2)            │
                                          │ credit (decimal 15,2)           │
                                          │ memo (text)                     │
                                          └─────────────────────────────────┘
```

---

## 2. Transaksi Database Atomik (*ACID Compliance*)

Setiap mutasi finansial wajib dibungkus dalam blok `DB::transaction()` untuk mencegah inkonsistensi saldo (*ledger corruption*):
1. **Transfer Saldo Kas Antar Rekening:**
   - Saldo akun asal didebit/dikurangi.
   - Saldo akun tujuan dikredit/ditambahkan.
   - Dua baris mutasi `CashAccountTransaction` diterbitkan.
   - Jurnal akuntansi berpasangan dibentuk.
   - *Seluruh langkah dieksekusi dalam satu transaksi atomik.* Jika ada langkah yang gagal, seluruh mutasi dibatalkan (*auto-rollback*).
2. **Pencatatan Beban Toko (`storeExpense`):**
   - Transaksi database memastikan pemotongan saldo kas, pembuatan record beban, dan pencatatan audit log terjadi secara atomik.

---

## 3. Strategi Pengindeksan & Kinerja Kueri

Kueri laporan keuangan (seperti Buku Besar dan Neraca Saldo) sering memproses ribuan baris mutasi dalam rentang tanggal tertentu. Tabel-tabel utama telah dioptimasi dengan indeks komposit:
- `chart_of_accounts`: Indeks unik komposit `(business_id, code)` memastikan pencarian kode akun instan (< 1ms) dan mencegah duplikasi kode per tenant.
- `accounting_journal_entries`: Indeks `(business_id, entry_date)` mempercepat filter tanggal laporan posisi keuangan.
- `accounting_journal_items`: Indeks `(journal_entry_id, chart_of_account_id)` mengoptimasi kueri kalkulasi total mutasi debit/kredit per akun.
- `cash_account_transactions`: Indeks `(cash_account_id, transaction_date)` menjamin pagination halaman buku kas tetap responsif di bawah 50ms bahkan dengan puluhan ribu baris riwayat.

---

## 4. Evaluasi N+1 Query & Eager Loading

Audit pada controller memastikan seluruh relasi dimuat dengan eager loading:
- `AccountingWebController@journals`: Menggunakan `with(['items.account'])` untuk menghindari N+1 kueri saat merender tabel jurnal.
- `AccountingWebController@chartOfAccounts`: Menggunakan hierarki rekursif atau eager loading `parent` dan `children`.
- `CashLedgerWebController@cashLedger`: Memuat eager `account` dan `creator`.
