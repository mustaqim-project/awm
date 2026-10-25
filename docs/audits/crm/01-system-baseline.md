# System Baseline - crm

> Modul: CRM & Loyalty | Commit: 38cdcdf | Tanggal: 2026-10-25

## 1. Lingkup Modul yang Diaudit
Modul CRM & Loyalty di Cooca mencakup subsistem keanggotaan pelanggan, program loyalitas poin belanja, pencatatan pelunasan piutang/kasbon pelanggan, serta pengelolaan voucher promosi diskon.

- **File Controller Utama:**
  - `app/Http/Controllers/Web/Crm/CrmWebController.php`
- **Domain Service:**
  - `app/Domain/Crm/LoyaltyService.php`
- **Model Eloquent Terlibat:**
  - `App\Models\Customer`
  - `App\Models\CustomerPointHistory`
  - `App\Models\CustomerCreditTransaction`
  - `App\Models\Voucher`
  - `App\Models\CashAccount`
  - `App\Models\CashTransaction`
  - `App\Models\AuditLog`
- **Tampilan Blade:**
  - `resources/views/app/crm/members.blade.php`
  - `resources/views/app/crm/vouchers.blade.php`
- **Rute Web (`routes/owner.php` baris 758-767):**
  - `GET /crm/members` (`crm.members.index`)
  - `GET /crm/customers/{customer}/points` (`crm.customers.points`)
  - `GET /crm/vouchers` (`crm.vouchers.index`)
  - `POST /crm/customers/{customer}/credit-payment` (`crm.customers.credit-payment`)
  - `POST /crm/vouchers` (`crm.vouchers.store`)
  - `POST /crm/vouchers/{voucher}/toggle` (`crm.vouchers.toggle`)

## 2. Status Baseline Awal (Sebelum Perbaikan)
1. **RBAC Enforcing:** Izin akses (`crm.view` dan `crm.manage`) hanya terpasang di level rute web (`routes/owner.php`), belum memiliki deklarasi pertahanan di level controller (`implements HasMiddleware`).
2. **Audit Logging:** Pencatatan pelunasan kasbon pelanggan (`recordCreditPayment`) telah merekam `AuditLog` via `LoyaltyService`. Namun, penerbitan voucher baru (`storeVoucher`) dan perubahan status keaktifan voucher (`toggleVoucher`) belum memiliki rekam jejak audit trail.
3. **UI / Ergonomics:** Tombol-tombol pada antarmuka voucher belum semuanya memenuhi standar Apple HIG minimum touch target 44px untuk pengguna perangkat bergerak (ponsel kasir).
4. **Hasil Baseline Test Awal:**
   - Command: `php artisan test tests/Feature/CrmWebFeatureTest.php`
   - Hasil: 6 passed, 25 assertions (100% Lulus).
