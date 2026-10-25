# System Baseline — Modul Billing & Subscription
> Modul: Billing & Subscription | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Status: Final

---

## 1. Inventaris Rute Terdaftar

Berdasarkan `routes/owner.php` (baris 547–562):

| Verb | URI | Nama Rute | Controller & Action | Middleware Terpasang |
|---|---|---|---|---|
| GET | `/billing` | `billing` | `BillingAndLimitWebController@index` | `auth`, `verified`, `require.permission:billing.view` |
| GET | `/billing/limits` | `billing.limits` | `BillingAndLimitWebController@index` | `auth`, `verified`, `require.permission:billing.view` |
| GET | `/patungan` | `billing.patungan` | `SubscriptionCheckoutWebController@checkout` | `auth`, `verified`, `require.permission:billing.manage` |
| POST | `/billing/upgrade` | `billing.upgrade` | `BillingAndLimitWebController@upgrade` | `auth`, `verified`, `require.permission:billing.manage` |
| GET | `/billing/checkout` | `billing.checkout` | `SubscriptionCheckoutWebController@checkout` | `auth`, `verified`, `require.permission:billing.manage` |
| POST | `/billing/promo/validate` | `billing.promo.validate` | `SubscriptionCheckoutWebController@validatePromo` | `auth`, `verified`, `require.permission:billing.manage` |
| POST | `/billing/order` | `billing.order.store` | `SubscriptionCheckoutWebController@store` | `auth`, `verified`, `require.permission:billing.manage` |
| GET/POST | `/billing/payments/{payment}` | `billing.payment.show` | `SubscriptionCheckoutWebController@payment` | `auth`, `verified`, `require.permission:billing.view` |
| GET | `/billing/payments/{payment}/status` | `billing.payment.status` | `SubscriptionCheckoutWebController@checkStatus` | `auth`, `verified`, `require.permission:billing.view` |
| GET | `/billing/payments/{payment}/invoice` | `billing.payment.invoice` | `SubscriptionCheckoutWebController@invoice` | `auth`, `verified`, `require.permission:billing.view` |
| POST | `/billing/payments/{payment}/upload-proof` | `billing.payment.upload` | `SubscriptionCheckoutWebController@uploadProof` | `auth`, `verified`, `require.permission:billing.manage` |
| GET | `/billing/payments/{payment}/proof` | `billing.payment.proof` | `SubscriptionCheckoutWebController@viewProof` | `auth`, `verified`, `require.permission:billing.view` |
| GET | `/billing/history` | `billing.history` | `SubscriptionCheckoutWebController@history` | `auth`, `verified`, `require.permission:billing.view` |
| POST | `/billing/storage/recalculate` | `billing.storage.recalculate` | `BillingAndLimitWebController@recalculateStorage` | `auth`, `verified`, `require.permission:billing.manage` |
| GET | `/billing/storage/files` | `billing.storage.files` | `BillingAndLimitWebController@listFiles` | `auth`, `verified`, `require.permission:billing.view` |
| DELETE | `/billing/storage/files/{storageFile}` | `billing.storage.files.destroy` | `BillingAndLimitWebController@destroyStorageFile` | `auth`, `verified`, `require.permission:billing.manage` |

---

## 2. Inventaris Controller & Model

### Controller
1. [`BillingAndLimitWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Billing/BillingAndLimitWebController.php):
   - Menangani kalkulasi kuota (`EntitlementService`), manajemen kapasitas disk (`StorageTrackingService`), kalkulasi ulang penyimpanan, dan penghapusan file per tenant.
   - Mengimplementasikan `HasMiddleware` dengan Controller-Level RBAC.
2. [`SubscriptionCheckoutWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Billing/SubscriptionCheckoutWebController.php):
   - Menangani alur checkout, pemilihan paket langganan (Solo/Standard/Premium/Prestige), validasi voucher promo, pembuatan order transaksi TriPay, halaman instruksi bayar interaktif, verifikasi status realtime polling, dan ekspor invoice PDF.
   - Mengimplementasikan `HasMiddleware` dengan Controller-Level RBAC.

### Model Terlibat
1. `BillingPackage`: Master paket langganan dan topup kapasitas.
2. `BusinessSubscription`: Catatan langganan aktif suatu tenant (tier, status, masa berlaku, kuota khusus).
3. `SubscriptionPayment`: Entitas transaksi pembayaran langganan / topup (nomor order, nominal, gateway, status, referensi TriPay).
4. `SubscriptionPromo`: Kupon voucher diskon dengan batas pemakaian dan periode aktif.
5. `StorageFile`: Tracking file fisik yang diunggah oleh tenant ke cloud disk.
6. `AuditLog`: Log forensik tindakan sensitif dan finansial.

---

## 3. Inventaris Tampilan Antarmuka (Blade Views)

Direktori: `resources/views/app/billing/`
1. [`limits.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/billing/limits.blade.php): Halaman utama dasbor batas pemakaian, progress bar konsumsi kuota 10 pilar, perbandingan paket langganan, dan modal manajemen file storage.
2. [`checkout.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/billing/checkout.blade.php): Halaman pemilihan paket, kalkulasi diskon kupon promo realtime, dan opsi kanal pembayaran otomatis TriPay.
3. [`payment.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/billing/payment.blade.php): Halaman instruksi pembayaran dengan countdown timer 15 menit, tampilan QRIS dinamis, nomor Virtual Account, dan polling status otomatis.
4. [`invoice.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/billing/invoice.blade.php): Tanda terima / faktur pembayaran resmi yang mendukung pratinjau browser dan unduh PDF.
5. [`history.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/billing/history.blade.php): Riwayat seluruh transaksi tagihan langganan bisnis lengkap dengan filter status dan tautan ke faktur.

---

## 4. Lingkungan Eksekusi & Dependensi

- **Framework**: Laravel 11 / 12
- **PHP Version**: 8.3.28
- **Database Driver**: MySQL (Production), SQLite Memory (Testing)
- **Payment Gateway**: TriPay API v2 (Integrasi signature HMAC-SHA256)
- **Penyimpanan Berkas**: Local & Public Disk via Flysystem
- **Status Baseline**: Siap diaudit komprehensif.
