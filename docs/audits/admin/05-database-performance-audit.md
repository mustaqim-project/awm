# Database & Performance Audit - Modul Platform Backoffice & Superadmin (`admin`)

> Modul: `admin` | Commit: `a17c5ae` | Basis Data: MySQL 8.x / MariaDB | Driver Cache: Redis / Array

---

## 1. Analisis Kueri & Pencegahan Masalah N+1

Pemeriksaan kode secara menyeluruh pada 30 kontroler admin memastikan bahwa seluruh relasi Eloquent dimuat secara agregat (*Eager Loading*) sebelum dikirimkan ke layer tampilan (*View*).

### Bukti Kode Implementasi Eager Loading:
1. **`AdminAccountRecoveryController`:**
   ```php
   $query = AccountRecoveryRequest::with(['user', 'business', 'approver', 'rejector'])
       ->orderByDesc('created_at');
   ```
   *Dampak:* Mengeliminasi potensi 60+ kueri berulang pada tabel `users`, `businesses`, dan `admins` saat menampilkan 15 baris antrean permohonan.
2. **`AdminBusinessController`:**
   ```php
   $query = Business::with(['owner', 'users', 'activeSubscription.billingPackage'])
       ->withCount(['locations', 'products', 'orders']);
   ```
   *Dampak:* Perhitungan jumlah cabang (*locations*) dan produk menggunakan sub-kueri SQL terindeks (`withCount`) tanpa perlu me-load seluruh koleksi model ke dalam memori PHP.
3. **`AdminSubscriptionController`:**
   ```php
   $payments = SubscriptionPayment::with(['business', 'billingPackage', 'approverAdmin'])
       ->latest('created_at')->paginate(20);
   ```

---

## 2. Standar Paginasi & Manajemen Memori

Seluruh tabel data di modul admin menerapkan paginasi berbasis limit:
- Standar paginasi menggunakan batas aman `15` hingga `25` baris per halaman.
- Seluruh paginasi memanggil `->withQueryString()`, mempertahankan filter parameter pencarian, status dropdown, dan urutan tanggal saat berpindah halaman tanpa kehilangan konteks.
- Dilarang keras memanggil `->get()` atau `->all()` tanpa batas limit pada tabel transaksi berskala besar (`subscription_payments`, `error_logs`, `leads`).

---

## 3. Strategi Caching & Reduksi Beban Kueri

Pengaturan platform yang sering diakses di setiap *lifecycle* request dilindungi dengan mekanisme caching cerdas pada model [`app/Models/SystemSetting.php`](file:///c:/laragon/www/cooca_core/app/Models/SystemSetting.php):

```php
public static function get(string $key, ?string $default = null): ?string
{
    try {
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting?->value ?? $default;
        });
    } catch (\Throwable) {
        return $default;
    }
}

public static function set(string $key, ?string $value, string $group = 'general', bool $isSecret = false): self
{
    Cache::forget("system_setting_{$key}");
    return static::updateOrCreate(
        ['key' => $key],
        ['value' => $value, 'group' => $group, 'is_secret' => $isSecret]
    );
}
```

### Keuntungan Kinerja:
- Kueri pengaturan platform seperti `subscription_price_monthly`, konfigurasi gateway TriPay, dan identitas platform di-cache selama 1 jam di memori.
- Saat admin memperbarui nilai di menu `/admin/settings`, cache otomatis dibersihkan via `Cache::forget()`, menjamin kueri berikutnya langsung mengambil data terkini tanpa *stale cache*.

---

## 4. Evaluasi Pengindeksan Basis Data (*Database Indexing*)

Tabel-tabel kunci modul admin dilengkapi indeks yang tepat pada kolom filter utama:

| Tabel | Kolom Terindeks | Tipe Indeks | Tujuan Kinerja |
| :--- | :--- | :---: | :--- |
| `admins` | `email` | `UNIQUE` | Pencarian instan akun admin saat proses login |
| `admins` | `is_active` | `INDEX` | Evaluasi cepat middleware `EnsureAdminIsActive` |
| `account_recovery_requests` | `status` | `INDEX` | Filter cepat tab antrean: pending, approved, rejected |
| `account_recovery_requests` | `ticket_number` | `UNIQUE` | Lookup langsung nomor tiket pemulihan |
| `account_recovery_requests` | `created_at` | `INDEX` | Pengurutan kronologis antrean berkas masuk |
| `subscription_payments` | `business_id` | `INDEX` | Relasi riwayat pembayaran per tenant |
| `subscription_payments` | `status` | `INDEX` | Filter transaksi lunas vs menunggu pembayaran |
| `system_settings` | `key` | `UNIQUE` | Pencarian kunci konfigurasi sistem |

---

## 5. Perlindungan Integritas Data & Transaksi Atomik

Aksi persetujuan pemulihan akun melibatkan pembaruan beberapa tabel sekaligus (mengganti kontak di tabel `users`, mengupdate status di `account_recovery_requests`, dan mencatat riwayat log). Operasi ini dibungkus secara utuh dalam transaksi basis data:

```php
DB::transaction(function () use ($recovery, $admin) {
    $user = $recovery->user;
    if ($recovery->new_email) {
        $user->email = $recovery->new_email;
    }
    if ($recovery->new_phone) {
        $user->phone = $recovery->new_phone;
    }
    $user->save();

    $recovery->status = AccountRecoveryRequest::STATUS_APPROVED;
    $recovery->approved_by = $admin->id;
    $recovery->approved_at = now();
    $recovery->save();
});
```

Jika terjadi kendala teknis di tengah jalan, seluruh perubahan akan di-*rollback* seketika, mencegah terjadinya *orphaned state* atau ketidakkonsistenan data kontak pengguna.
