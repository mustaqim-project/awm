# Security Audit   Modul Billing & Subscription
> Modul: Billing & Subscription | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Status: Final

---

## 1. Analisis Otorisasi & Controller-Level RBAC

Sebelum proses remediasi, kontrol akses modul billing hanya dideklarasikan pada lapisan rute (`routes/owner.php`). Pola ini rentan terhadap regresi apabila deklarasi rute diubah atau dibuka tanpa middleware pengaman.

### Remediasi yang Diterapkan:
Kedua controller utama telah dimutakhirkan dengan menerapkan interface `Illuminate\Routing\Controllers\HasMiddleware`:
1. **`BillingAndLimitWebController`**:
   - `billing.view`: Diwajibkan untuk `index` dan `listFiles`.
   - `billing.manage`: Diwajibkan untuk `upgrade`, `recalculateStorage`, dan `destroyStorageFile`.
2. **`SubscriptionCheckoutWebController`**:
   - `billing.view`: Diwajibkan untuk `payment`, `checkStatus`, `invoice`, `viewProof`, dan `history`.
   - `billing.manage`: Diwajibkan untuk `checkout`, `validatePromo`, `store`, dan `uploadProof`.

Pengguna tanpa izin yang sah dialihkan secara elegan ke rute aman (`portal`) pada antarmuka web atau menerima respons `403 Forbidden` pada request API/JSON.

---

## 2. Proteksi IDOR (Insecure Direct Object Reference) Lintas Tenant

Seluruh rute yang menerima parameter entitas model (`SubscriptionPayment` dan `StorageFile`) memiliki perlindungan ketat:

1. **Tagihan & Faktur (`SubscriptionPayment`)**:
   ```php
   $business = Context::requireBusiness();
   abort_unless($payment->business_id === $business->id, 403);
   ```
   Percobaan pengguna mengakses ID tagihan milik tenant lain langsung memicu status HTTP 403 Forbidden. Telah diverifikasi oleh test `test_cross_tenant_idor_protection_on_invoice_and_payment`.

2. **Penghapusan File Storage (`StorageFile`)**:
   ```php
   $business = Context::requireBusiness();
   abort_unless($isOwner || $hasBillingPerm, 403, __('billing.only_owner_delete_storage'));
   abort_unless($storageFile->business_id === $business->id, 403, __('billing.storage_file_not_owned'));
   ```
   Percobaan menghapus berkas penyimpanan tenant lain langsung ditolak dengan status HTTP 403. Telah diverifikasi oleh test `test_cross_tenant_idor_protection_on_storage_file_deletion`.

---

## 3. Forensik & Anti-Fraud Audit Trail (`AuditLog`)

Setiap tindakan yang berpotensi memengaruhi integritas kuota, kapasitas disk, atau transaksi finansial dicatat secara terstruktur:

| Aksi | Level Risiko | Model Terkait | Metadata yang Dicatat |
|---|---|---|---|
| `billing.order.created` | `RISK_MEDIUM` | `SubscriptionPayment` | Nomor order, tier langganan, nominal, kanal pembayaran, kode promo voucher, IP address, user agent. |
| `billing.storage.recalculated` | `RISK_LOW` | `Business` | Total berkas terpindai, ukuran disk yang diperbarui, IP address, user agent. |
| `billing.storage.file_deleted` | `RISK_MEDIUM` | `StorageFile` | Nama berkas, ukuran (MB), path disk, user yang menghapus, IP address, user agent. |

---

## 4. Keamanan Integrasi Payment Gateway (TriPay API)

1. **HMAC-SHA256 Signature Verification**: Webhook callback diverifikasi menggunakan private key TriPay sehingga data status transaksi tidak dapat dipalsukan.
2. **Window Kedaluwarsa 15 Menit**: Mencegah pemanfaatan nomor rekening bayar virtual yang sudah lama atau order gantung.
3. **Double-Submit Prevention**: Frontend memproteksi tombol bayar dari klik ganda saat memproses inisialisasi tagihan.
