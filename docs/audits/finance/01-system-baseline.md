# System Baseline - finance

> Modul: Keuangan & Akuntansi Korporasi | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## 1. Inventaris Komponen Sistem

### A. Controller Web (HTTP Handlers)
1. `app/Http/Controllers/Web/Finance/AccountingWebController.php`:
   - Mengelola Bagan Akun (Chart of Accounts / COA), Jurnal Akuntansi, Buku Besar Umum, Neraca Saldo, Neraca Keuangan SAK EMKM, dan Rekonsiliasi Bank.
   - Dilindungi oleh Controller-Level RBAC: `accounting.view`, `accounting.manage`, serta `entitlement:export`.
2. `app/Http/Controllers/Web/Finance/CashLedgerWebController.php`:
   - Mengelola akun kas fisik/bank, pencatatan mutasi kas masuk/keluar/transfer, monitoring piutang usaha (AR), dan hutang usaha (AP).
   - Dilindungi oleh Controller-Level RBAC: `finance.cash_bank`, `finance.receivables`, `finance.payables`.
3. `app/Http/Controllers/Web/Finance/PosFinanceWebController.php`:
   - Mengelola pencatatan beban/pengeluaran operasional toko dan ringkasan keuangan harian terintegrasi dengan sistem kasir POS.
   - Dilindungi oleh Controller-Level RBAC: `accounting.view`, `expenses.view`, `expenses.manage`.
4. `app/Http/Controllers/Web/Finance/PaymentSettlementWebController.php`:
   - Mengelola settlement pembayaran dan rekonsiliasi gateway pencairan dana ke rekening toko.
   - Dilindungi oleh Controller-Level RBAC: `finance.cash_bank`.
5. `app/Http/Controllers/Web/Finance/StoreEdcTerminalWebController.php`:
   - Mengelola pendaftaran terminal EDC di toko, pengaturan MDR, dan pelacakan transaksi mesin EDC.
   - Dilindungi oleh Controller-Level RBAC: `finance.cash_bank`.
6. `app/Http/Controllers/Web/Finance/MerchantPayoutAccountWebController.php`:
   - Mengelola rekening bank tujuan penarikan dana pemilik usaha (*Payout Hub*).
   - Dilindungi oleh Controller-Level RBAC: `finance.cash_bank`.
7. `app/Http/Controllers/Web/Finance/ExternalReconciliationWebController.php`:
   - Mengelola dashboard likuiditas akun eksternal dan rekonsiliasi saldo penampung (*Where The Money Lives*).
   - Dilindungi oleh Controller-Level RBAC: `finance.cash_bank`.

### B. Service & Domain Layers
- `app/Domain/Import/DataExchangeService.php`: Layanan ekspor data COA, piutang, dan hutang ke spreadsheet dengan sanitasi formula injection.
- `app/Domain/Accounting/`: Layanan buku besar, perhitungan neraca saldo, dan struktur auto-journaling berpasangan.
- `app/Domain/Finance/`: Layanan mutasi kas ledger, transfer kas, dan rekonsiliasi eksternal.

### C. Model Eloquent & Skema Database
- `ChartOfAccount` (`chart_of_accounts`): Master akun akuntansi (kode akun, nama, klasifikasi: aset, kewajiban, modal, pendapatan, beban). Menerapkan `BelongsToBusiness`.
- `AccountingJournalEntry` (`accounting_journal_entries`): Header entri jurnal akuntansi (nomor jurnal, tanggal, tipe referensi, status).
- `AccountingJournalItem` (`accounting_journal_items`): Baris detail jurnal (akun COA, nominal debit, nominal kredit, memo).
- `CashAccount` (`cash_accounts`): Akun rekening kas fisik toko atau bank. Menerapkan `BelongsToBusiness`.
- `CashAccountTransaction` (`cash_account_transactions`): Mutasi transaksi masuk/keluar pada buku kas.
- `ExpenseRecord` (`expense_records`): Catatan beban operasional toko dengan foto nota bukti.

---

## 2. Inventaris Rute & Hak Akses

| URL Path | Nama Rute | Controller & Method | Permission / Middleware |
|---|---|---|---|
| `/finance/cash-bank` | `finance.cash-bank.index` | `CashLedgerWebController@cashBankHub` | `finance.cash_bank` |
| `/finance/cash-bank/ledger` | `finance.cash-bank.ledger` | `CashLedgerWebController@cashLedger` | `finance.cash_bank` |
| `/finance/expenses` | `finance.expenses.index` | `PosFinanceWebController@expenses` | `expenses.view` |
| `/finance/journals` | `finance.journals.index` | `AccountingWebController@journals` | `accounting.view` |
| `/finance/receivables` | `finance.receivables.index` | `CashLedgerWebController@receivables` | `finance.receivables` |
| `/finance/payables` | `finance.payables.index` | `CashLedgerWebController@payables` | `finance.payables` |
| `/finance/settlements` | `finance.settlements.index` | `PaymentSettlementWebController@index` | `finance.cash_bank` |
| `/finance/chart-of-accounts` | `finance.coa.index` | `AccountingWebController@chartOfAccounts` | `accounting.view` |
| `/finance/general-ledger` | `finance.general-ledger.index` | `AccountingWebController@generalLedger` | `accounting.view` |
| `/finance/trial-balance` | `finance.trial-balance` | `AccountingWebController@trialBalance` | `accounting.view` |
| `/finance/balance-sheet` | `finance.balance-sheet` | `AccountingWebController@balanceSheet` | `accounting.view` |
| `/finance/reconciliations` | `finance.reconciliations.index` | `AccountingWebController@reconciliations` | `accounting.view` |
| `/finance/payout-accounts` | `finance.payout-accounts.index` | `MerchantPayoutAccountWebController@index` | `finance.cash_bank` |
| `/finance/edc-terminals` | `finance.edc-terminals.index` | `StoreEdcTerminalWebController@index` | `finance.cash_bank` |
| `/finance/external-reconciliation` | `finance.external-recon.index` | `ExternalReconciliationWebController@index` | `finance.cash_bank` |

---

## 3. Baseline Pengujian & Metrik

Sebelum proses audit dan penguatan, test suite navigasi mengalami kegagalan pada label terjemahan lama (`Settlement Gateway` dan `Laporan Posisi Keuangan (Neraca)`). Setelah penyelarasan string dan penambahan test suite audit:
- Total file pengujian: 5 file.
- Total test cases: 42 tests.
- Total assertions: 339 assertions.
- Status: **100% Passed**.
