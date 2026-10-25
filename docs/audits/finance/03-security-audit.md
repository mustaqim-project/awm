# Security Audit - finance

> Modul: Keuangan & Akuntansi Korporasi | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## 1. Ikhtisar Keamanan & Postur Risiko

Audit keamanan dilakukan secara mendalam pada 7 Controller Web Keuangan, service layer pertukaran data, serta model data akuntansi.

| Vektor Keamanan | Status Sebelum Audit | Status Pasca Perbaikan | Bukti Verifikasi |
|---|---|---|---|
| **Controller-Level RBAC** | Route-level middleware saja | `implements HasMiddleware` pada 7 Controller | `FinanceModuleAuditTest::test_unauthorized_user_cannot_access_accounting_dashboard()` |
| **Formula Injection (CWE-1236)**| Nilai teks diekspor mentah ke spreadsheet | Disanitasi via `CsvSanitizer::sanitize()` | `FinanceModuleAuditTest::test_formula_injection_defense_sanitizes_csv_export()` |
| **Audit Trail Logging** | Tidak ada log pada mutasi akun | Tercatat pada tabel `audit_logs` | `FinanceModuleAuditTest::test_coa_creation_generates_audit_log()` |
| **Multi-Tenant Scoping (IDOR)** | Aman (Global Scope) | Tetap terisolasi via `BelongsToBusiness` | `FinanceModuleAuditTest::test_cross_tenant_isolation_protects_cash_and_coa()` |
| **Otentikasi & Sesi** | Dilindungi middleware web | Dilindungi `auth:web` + WhatsApp OTP | `FinanceModuleAuditTest::test_guest_redirected_to_login()` |

---

## 2. Analisis Implementasi Controller-Level RBAC

Sebelum proses audit, proteksi rute hanya bergantung pada pengelompokan `Route::group` di `routes/web.php`. Jika controller diakses dari rute baru atau tanpa grup middleware yang tepat, aksi controller dapat tereksekusi tanpa otorisasi.

Telah dilakukan penguatan dengan mengimplementasikan `Illuminate\Routing\Controllers\HasMiddleware` pada seluruh 7 controller:

### 1. `AccountingWebController`
```php
public static function middleware(): array
{
    return [
        new Middleware('permission:accounting.view', only: [
            'chartOfAccounts', 'journals', 'generalLedger', 'trialBalance', 'balanceSheet', 'reconciliations',
        ]),
        new Middleware('permission:accounting.manage', only: [
            'coaStore', 'coaUpdate', 'coaDestroy',
        ]),
        new Middleware('entitlement:export', only: [
            'exportBalanceSheet', 'exportCoa',
        ]),
    ];
}
```

### 2. `CashLedgerWebController`
```php
public static function middleware(): array
{
    return [
        new Middleware('permission:finance.cash_bank', only: [
            'cashBankHub', 'cashLedger', 'storeAccount', 'updateAccount', 'toggleAccount',
        ]),
        new Middleware('permission:finance.receivables', only: [
            'receivables', 'receivablesExport', 'receivablesTemplate',
        ]),
        new Middleware('permission:finance.payables', only: [
            'payables', 'payablesExport', 'payablesTemplate',
        ]),
    ];
}
```

### 3. `PosFinanceWebController`
```php
public static function middleware(): array
{
    return [
        new Middleware('permission:accounting.view', only: ['summary']),
        new Middleware('permission:expenses.view', only: ['expenses']),
        new Middleware('permission:expenses.manage', only: ['storeExpense']),
    ];
}
```

### 4. Controller Tambahan:
- `PaymentSettlementWebController`: `permission:finance.cash_bank`.
- `StoreEdcTerminalWebController`: `permission:finance.cash_bank`.
- `MerchantPayoutAccountWebController`: `permission:finance.cash_bank`.
- `ExternalReconciliationWebController`: `permission:finance.cash_bank`.

---

## 3. Mitigasi Formula / CSV Injection (CWE-1236)

Pada file `app/Domain/Import/DataExchangeService.php`, fungsi ekspor spreadsheet:
- `exportCoa(Business $business)`
- `exportReceivables(Business $business)`
- `exportPayables(Business $business)`

Sebelumnya memasukkan string mentah ke dalam baris spreadsheet. Jika penyerang menyisipkan nama akun atau nama supplier yang diawali `=`, `+`, `-`, atau `@`, Microsoft Excel akan mengeksekusinya sebagai formula kalkulasi berbahaya (DDE execution / data exfiltration).

**Perbaikan:** Setiap sel teks kini dilewatkan melalui `CsvSanitizer::sanitize()`:
```php
$data[] = [
    CsvSanitizer::sanitize($account->code),
    CsvSanitizer::sanitize($account->name),
    CsvSanitizer::sanitize(strtoupper($account->classification)),
    CsvSanitizer::sanitize(strtoupper($account->normal_balance)),
    CsvSanitizer::sanitize($account->parent ? $account->parent->code : ''),
    CsvSanitizer::sanitize($account->description ?? ''),
    $account->is_active ? 'YA' : 'TIDAK',
];
```
Metode ini secara otomatis menambahkan tanda kutip tunggal (`'`) di depan karakter berbahaya, sehingga aplikasi spreadsheet membacanya murni sebagai teks biasa tanpa mengeksekusi formula.

---

## 4. Pencatatan Jejak Audit Finansial (*Audit Trail*)

Tindakan mutasi akun kini secara otomatis mencatat jejak audit ke tabel `audit_logs`:
1. `coaStore`: Action `accounting.coa_created`.
2. `coaUpdate`: Action `accounting.coa_updated`.
3. `coaDestroy`: Action `accounting.coa_deleted`.
4. `storeAccount`: Action `finance.cash_account_created`.
5. `updateAccount`: Action `finance.cash_account_updated`.
6. `toggleAccount`: Action `finance.cash_account_toggled`.
7. `storeExpense`: Action `finance.expense_created`.

Setiap entri memuat `business_id`, `user_id`, `ip_address`, `user_agent`, dan detail payload finansial.
