# Security Audit - crm

> Modul: CRM & Loyalty | Tanggal: 2026-10-25 | Auditor: Anti-Fraud & AppSec Engine

## 1. Evaluasi Otorisasi & Kontrol Akses (RBAC)
- **Controller-Level Enforcement:**
  - `CrmWebController` menerapkan interface `Illuminate\Routing\Controllers\HasMiddleware`.
  - Static method `middleware()` secara eksplisit membatasi:
    - `require.permission:crm.view` untuk method: `members`, `pointHistories`, `vouchers`.
    - `require.permission:crm.manage` untuk method: `recordCreditPayment`, `storeVoucher`, `toggleVoucher`.
  - Hasil Pengujian: Permintaan web tanpa permission dialihkan ke portal, sedangkan permintaan AJAX/JSON menerima status `403 Forbidden` (`MODULE_FORBIDDEN`).

## 2. Multi-Tenant Isolation & Perlindungan IDOR
- Seluruh model `Customer` dan `Voucher` mengimplementasikan trait `BelongsToBusiness` dengan `BusinessScope` global.
- Jika pengguna mencoba mengakses ID pelanggan atau voucher milik tenant lain melalui route parameter:
  - `BusinessScope` secara otomatis menyaring `WHERE business_id = Context::business()->id`.
  - Laravel melempar `ModelNotFoundException` (HTTP 404), mencegah *data reconnaissance*.
  - Sebagai lapisan *defense-in-depth*, controller menyematkan `abort_unless($model->business_id === Context::business()?->id, 403)` pada seluruh action mutasi.
- Terverifikasi 100% pada automated test `test_idor_protection_customer_points_and_credit_payment`.

## 3. Pencegahan Penipuan Kasir (Anti-Lapping Shield)
- Kasus penipuan umum pada bisnis ritel/F&B: Kasir menerima setoran pelunasan utang dari pelanggan A, namun tidak mencatatnya ke kas dan menggunakan setoran pelanggan B di hari berikutnya untuk menutupi saldo pelanggan A (*lapping scheme*).
- **Proteksi Cooca:**
  - Pembayaran kasbon tidak dapat dicatat tanpa mengaitkannya secara atomik ke `CashAccount` dan `CashTransaction`.
  - Perubahan saldo pelanggan dan kas masuk dieksekusi dalam satu transaksi database (`DB::transaction`).
  - Rekaman `AuditLog` mencatat ID kasir, waktu, nominal bayar, dan sisa saldo pelanggan.
  - Nilai pembayaran divalidasi tidak boleh melebihi sisa kasbon (`max:$customer->current_credit_balance`).

## 4. Keamanan Voucher Promosi
- Validasi keunikan kode voucher diperiksa secara ketat per tenant (`Rule::unique('vouchers', 'code')->where('business_id', ...)`).
- Penyerang tidak dapat menebak atau memakai voucher bisnis lain karena pemrosesan voucher pada transaksi kasir selalu disaring berdasarkan `business_id` aktif.
- Perekaman jejak audit `voucher.created` dan `voucher.status_toggled` memastikan setiap penerbitan atau penonaktifan voucher dapat ditelusuri siapa staf pembuatnya.
