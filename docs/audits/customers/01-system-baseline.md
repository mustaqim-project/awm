# System Baseline - customers

> Modul: Pelanggan (Customers Master Directory) | Commit: 38cdcdf | Tanggal: 2026-10-25

## 1. Lingkup Modul yang Diaudit
Modul Master Data Pelanggan di Cooca mencakup subsistem penyimpanan data profil klien, pencatatan limit kasbon, pengelolaan tempo pembayaran (payment terms), ekspor/impor Excel massal, serta deep-linking ke subsistem loyalitas CRM.

- **File Controller Utama:**
  - `app/Http/Controllers/Web/CustomerWebController.php`
- **Domain Service:**
  - `app/Domain/Import/DataExchangeService.php`
- **Model Eloquent Terlibat:**
  - `App\Models\Customer`
  - `App\Models\AuditLog`
  - `App\Models\Invoice`
  - `App\Models\PurchaseOrder`
  - `App\Models\PosOrder`
- **Tampilan Blade:**
  - `resources/views/app/customers/index.blade.php`
- **Rute Web (`routes/owner.php` baris 226-233):**
  - `GET /customers` (`customers.index`)
  - `GET /customers/export` (`customers.export`)
  - `GET /customers/template` (`customers.template`)
  - `POST /customers/import/preview` (`customers.import.preview`)
  - `POST /customers/import/execute` (`customers.import.execute`)
  - `POST /customers` (`customers.store`)
  - `PUT /customers/{customer}` (`customers.update`)
  - `DELETE /customers/{customer}` (`customers.destroy`)

## 2. Status Baseline Awal (Sebelum Perbaikan)
1. **RBAC Enforcing:** Middleware permission (`customers.view`, `customers.create`, `customers.edit`, `customers.delete`) hanya dipasang di rute `routes/owner.php`. Controller belum mengimplementasikan `implements HasMiddleware`.
2. **Deletion Guard Saldo Kasbon:** Aksi `destroy` langsung memanggil `$customer->delete()` tanpa memvalidasi apakah pelanggan masih memiliki piutang berjalan (`current_credit_balance > 0`).
3. **Formula Injection (CWE-1236):** Method `exportCustomers` pada `DataExchangeService` langsung menuliskan nama pelanggan dan perusahaan ke sel spreadsheet tanpa sanitasi karakter pemicu formula (`=`, `+`, `-`, `@`).
4. **Hasil Baseline Test Awal:**
   - Command: `php artisan test tests/Feature/CustomerWebFeatureTest.php`
   - Hasil: 5 passed, 25 assertions (100% Lulus).
